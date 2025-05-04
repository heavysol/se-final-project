<?php
session_start();
include('../../../db/config.php');

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$query = "SELECT role FROM users WHERE user_id = ?";
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
    // Prepare the data
    $postData = [
        'action' => 'edit',
        'event_id' => $event_id,
        'title' => $_POST['title'],
        'description' => $_POST['description'],
        'start_datetime' => $_POST['start_datetime'],
        'end_datetime' => $_POST['end_datetime'],
        'location' => $_POST['location'],
        'category' => $_POST['category'],
        'max_capacity' => $_POST['max_capacity']
    ];

    // Initialize cURL session
    $ch = curl_init('/se-final-project/actions/event_management_action.php');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200) {
        $result = json_decode($response, true);
        if ($result['success']) {
            if (isset($result['redirect'])) {
                header('Location: ' . $result['redirect']);
                exit;
            } else {
                header('Location: event_management.php');
                exit;
            }
        } else {
            $error = $result['message'];
        }
    } else {
        $error = "Error connecting to server. HTTP Code: " . $httpCode;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Edit Event</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../../assets/css/general-styles.css">
    <link rel="stylesheet" href="../../../assets/css/admin-styles.css">
    <style>
        /* Fix compatibility issues */
        html {
            -webkit-text-size-adjust: 100%;
            text-size-adjust: 100%;
        }
        .form-label {
            text-align: match-parent;
            text-align: inherit;
        }
        @media print {
            * {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
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

                            <form method="POST" action="../../../actions/event_management_action.php" id="editEventForm">
                                <input type="hidden" name="action" value="edit">
                                <input type="hidden" name="event_id" value="<?php echo $event_id; ?>">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Title</label>
                                    <input type="text" class="form-control" id="title" name="title" 
                                           value="<?php echo htmlspecialchars($event['title']); ?>" 
                                           required aria-required="true"
                                           placeholder="Enter event title">
                                </div>
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" id="description" name="description" 
                                              required aria-required="true"
                                              placeholder="Enter event description"><?php echo htmlspecialchars($event['description']); ?></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="start_datetime" class="form-label">Start Date & Time</label>
                                    <input type="datetime-local" class="form-control" id="start_datetime" name="start_datetime" 
                                           value="<?php echo date('Y-m-d\TH:i', strtotime($event['start_datetime'])); ?>" 
                                           required aria-required="true">
                                </div>
                                <div class="mb-3">
                                    <label for="end_datetime" class="form-label">End Date & Time</label>
                                    <input type="datetime-local" class="form-control" id="end_datetime" name="end_datetime" 
                                           value="<?php echo date('Y-m-d\TH:i', strtotime($event['end_datetime'])); ?>" 
                                           required aria-required="true">
                                </div>
                                <div class="mb-3">
                                    <label for="location" class="form-label">Location</label>
                                    <input type="text" class="form-control" id="location" name="location" 
                                           value="<?php echo htmlspecialchars($event['location']); ?>" 
                                           required aria-required="true"
                                           placeholder="Enter event location">
                                </div>
                                <div class="mb-3">
                                    <label for="category" class="form-label">Category</label>
                                    <select class="form-select" id="category" name="category" 
                                            required aria-required="true"
                                            aria-label="Select event category">
                                        <option value="">Select Category</option>
                                        <option value="academic" <?php echo $event['category'] === 'academic' ? 'selected' : ''; ?>>Academic</option>
                                        <option value="social" <?php echo $event['category'] === 'social' ? 'selected' : ''; ?>>Social</option>
                                        <option value="cultural" <?php echo $event['category'] === 'cultural' ? 'selected' : ''; ?>>Cultural</option>
                                        <option value="sports" <?php echo $event['category'] === 'sports' ? 'selected' : ''; ?>>Sports</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="max_capacity" class="form-label">Maximum Capacity</label>
                                    <input type="number" class="form-control" id="max_capacity" name="max_capacity" 
                                           value="<?php echo $event['max_capacity']; ?>" 
                                           required aria-required="true"
                                           placeholder="Enter maximum capacity">
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
    <script>
        document.getElementById('editEventForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('../../../actions/event_management_action.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    } else {
                        window.location.href = 'event_management.php';
                    }
                } else {
                    alert(data.message || 'An error occurred');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while processing your request');
            });
        });
    </script>
</body>
</html> 