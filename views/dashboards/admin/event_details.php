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
    $query = "SELECT e.*, u.first_name, u.last_name 
              FROM events e 
              JOIN users u ON e.organizer_id = u.user_id 
              WHERE e.event_id = ?";
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Event Details</title>
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
            <li><a href="../../../index.php">
                <i class="bi bi-house-door"></i> Home
            </a></li>
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
                    <h2>Event Details</h2>
                    <p class="text-muted">View and manage event information</p>
                </div>
            </div>

            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0"><?php echo htmlspecialchars($event['title']); ?></h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <p><strong>Organizer:</strong> <?php echo htmlspecialchars($event['first_name'] . ' ' . $event['last_name']); ?></p>
                                    <p><strong>Category:</strong> <?php echo htmlspecialchars($event['category']); ?></p>
                                    <p><strong>Location:</strong> <?php echo htmlspecialchars($event['location']); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Start Date & Time:</strong> <?php echo date('M d, Y H:i', strtotime($event['start_datetime'])); ?></p>
                                    <p><strong>End Date & Time:</strong> <?php echo date('M d, Y H:i', strtotime($event['end_datetime'])); ?></p>
                                    <p><strong>Maximum Capacity:</strong> <?php echo $event['max_capacity']; ?></p>
                                </div>
                            </div>
                            <div class="mb-3">
                                <h6>Description</h6>
                                <p><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="event_management.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Back to Events
                            </a>
                            <a href="edit_event.php?id=<?php echo $event['event_id']; ?>" class="btn btn-primary">
                                <i class="bi bi-pencil"></i> Edit Event
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Event Status</h5>
                        </div>
                        <div class="card-body">
                            <?php
                            $statusClass = '';
                            switch ($event['status']) {
                                case 'approved':
                                    $statusClass = 'success';
                                    break;
                                case 'pending':
                                    $statusClass = 'warning';
                                    break;
                                case 'rejected':
                                    $statusClass = 'danger';
                                    break;
                                default:
                                    $statusClass = 'secondary';
                            }
                            ?>
                            <p class="mb-3">
                                <span class="badge bg-<?php echo $statusClass; ?>">
                                    <?php echo ucfirst($event['status']); ?>
                                </span>
                            </p>
                            <p><strong>Created:</strong> <?php echo date('M d, Y', strtotime($event['created_at'])); ?></p>
                            <p><strong>Last Updated:</strong> <?php echo date('M d, Y', strtotime($event['updated_at'])); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 