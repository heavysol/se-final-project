<?php
session_start();
include('../../../db/config.php');

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$query = "SELECT role FROM Users WHERE user_id = ?";
$stmt = $conn->prepare($query);

if ($stmt) {
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user || $user['role'] !== 'admin') {
        header('Location: ../../index.php');
        exit;
    }
    $stmt->close();
}

// Get event details
if (isset($_GET['id'])) {
    $event_id = $_GET['id'];
    $query = "SELECT * FROM events WHERE event_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $event = $result->fetch_assoc();
    $stmt->close();
} else {
    header('Location: event_management.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $start_datetime = $_POST['start_datetime'];
    $end_datetime = $_POST['end_datetime'];
    $location = $_POST['location'];
    $category = $_POST['category'];
    $max_capacity = $_POST['max_capacity'];
    $status = $_POST['status'];

    $query = "UPDATE events SET 
              title = ?, 
              description = ?, 
              start_datetime = ?, 
              end_datetime = ?, 
              location = ?, 
              category = ?, 
              max_capacity = ?, 
              status = ?,
              updated_at = NOW()
              WHERE event_id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssssisi", $title, $description, $start_datetime, $end_datetime, $location, $category, $max_capacity, $status, $event_id);
    
    if ($stmt->execute()) {
        header('Location: event_management.php');
        exit;
    } else {
        $error = "Error updating event: " . $stmt->error;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Event</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../../assets/css/general-styles.css">
    <link rel="stylesheet" href="../../../assets/css/admin-styles.css">
</head>
<body>
    <!-- Admin Sidebar -->
    <div class="admin-sidebar">
        <div class="admin-sidebar-header">
            <h3>Campus Events</h3>
            <div class="small">Admin Dashboard</div>
        </div>
        <ul class="admin-sidebar-menu">
            <li><a href="admin_dashboard.php">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a></li>
            <li><a href="event_management.php" class="active">
                <i class="bi bi-calendar-event"></i> Events Management
            </a></li>
            <li><a href="user_management.php">
                <i class="bi bi-people"></i> User Management
            </a></li>
            <li><a href="analytics.php">
                <i class="bi bi-graph-up"></i> Analytics
            </a></li>
            <li><a href="notifications.php">
                <i class="bi bi-bell"></i> Notifications
            </a></li>
            <li><a href="../../logout.php">
                <i class="bi bi-box-arrow-right"></i> Logout
            </a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="admin-main-content">
        <div class="container-fluid">
            <div class="row mb-4">
                <div class="col-md-12">
                    <h2>Edit Event</h2>
                    <p class="text-muted">Update event information</p>
                </div>
            </div>

            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-body">
                            <?php if (isset($error)): ?>
                                <div class="alert alert-danger"><?php echo $error; ?></div>
                            <?php endif; ?>

                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label class="form-label">Title</label>
                                    <input type="text" class="form-control" name="title" value="<?php echo htmlspecialchars($event['title']); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control" name="description" required><?php echo htmlspecialchars($event['description']); ?></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Start Date & Time</label>
                                    <input type="datetime-local" class="form-control" name="start_datetime" value="<?php echo date('Y-m-d\TH:i', strtotime($event['start_datetime'])); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">End Date & Time</label>
                                    <input type="datetime-local" class="form-control" name="end_datetime" value="<?php echo date('Y-m-d\TH:i', strtotime($event['end_datetime'])); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Location</label>
                                    <input type="text" class="form-control" name="location" value="<?php echo htmlspecialchars($event['location']); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Category</label>
                                    <select class="form-select" name="category" required>
                                        <option value="">Select Category</option>
                                        <option value="academic" <?php echo $event['category'] === 'academic' ? 'selected' : ''; ?>>Academic</option>
                                        <option value="social" <?php echo $event['category'] === 'social' ? 'selected' : ''; ?>>Social</option>
                                        <option value="cultural" <?php echo $event['category'] === 'cultural' ? 'selected' : ''; ?>>Cultural</option>
                                        <option value="sports" <?php echo $event['category'] === 'sports' ? 'selected' : ''; ?>>Sports</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Maximum Capacity</label>
                                    <input type="number" class="form-control" name="max_capacity" value="<?php echo $event['max_capacity']; ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <select class="form-select" name="status" required>
                                        <option value="pending" <?php echo $event['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="approved" <?php echo $event['status'] === 'approved' ? 'selected' : ''; ?>>Approved</option>
                                        <option value="rejected" <?php echo $event['status'] === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                    </select>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <a href="event_management.php" class="btn btn-secondary">
                                        <i class="bi bi-arrow-left"></i> Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save"></i> Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 