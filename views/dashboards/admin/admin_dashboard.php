<?php
session_start();
include('../../../db/config.php');

// Get count of approved events for the dashboard

$event_query = "SELECT COUNT(*) as active_events FROM events WHERE status = 'approved'";
$event_result = $conn->query($event_query);
$active_events = $event_result->fetch_assoc()['active_events'];

// Get total number of registrations from the database
$registrations_query = "SELECT COUNT(*) as total_registrations FROM registrations";
$registrations_result = $conn->query($registrations_query);
$total_registrations = $registrations_result->fetch_assoc()['total_registrations'];

// Get total number of users
$users_query = "SELECT COUNT(*) as total_users FROM users";
$users_result = $conn->query($users_query);
$total_users = $users_result->fetch_assoc()['total_users'];

// Get count of organizers
$organizers_query = "SELECT COUNT(*) as total_organizers FROM users WHERE role = 'organizer'";
$organizers_result = $conn->query($organizers_query);
$total_organizers = $organizers_result->fetch_assoc()['total_organizers'];

// Get recent upcoming approved events
$recent_events_query = "SELECT e.*, u.first_name as organizer_name 
                       FROM events e 
                       JOIN users u ON e.organizer_id = u.user_id 
                       WHERE e.status = 'approved' 
                       ORDER BY e.updated_at DESC 
                       LIMIT 5";
$recent_events_result = $conn->query($recent_events_query);

// Debug query to check all upcoming events
$debug_query = "SELECT e.*, u.first_name as organizer_name 
                FROM events e 
                JOIN users u ON e.organizer_id = u.user_id 
                WHERE e.status = 'approved' 
                ORDER BY e.updated_at DESC";
$debug_result = $conn->query($debug_query);

// Log the results for debugging
error_log("Number of approved events found: " . ($debug_result ? $debug_result->num_rows : 0));
if ($debug_result && $debug_result->num_rows > 0) {
    while($event = $debug_result->fetch_assoc()) {
        error_log("Event found: " . $event['title'] . " - Updated: " . $event['updated_at']);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrator Dashboard - Campus Events</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
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
            <li><a href="admin_dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_dashboard.php' ? 'active' : ''; ?>">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a></li>
            <li><a href="event_management.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'event_management.php' ? 'active' : ''; ?>">
                <i class="bi bi-calendar-event"></i> Events Management
            </a></li>
            <li><a href="user_management.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'user_management.php' ? 'active' : ''; ?>">
                <i class="bi bi-people"></i> User Management
            </a></li>
            <li><a href="analytics.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'analytics.php' ? 'active' : ''; ?>">
                <i class="bi bi-graph-up"></i> Analytics
            </a></li>
            <li><a href="notifications.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'notifications.php' ? 'active' : ''; ?>">
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
                    <h2>Dashboard Overview</h2>
                    <p class="text-muted">Welcome back, Admin!</p>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="admin-card">
                        <div class="admin-card-title">Active Events</div>
                        <div class="admin-card-value"><?php echo $active_events; ?></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="admin-card">
                        <div class="admin-card-title">Total Users</div>
                        <div class="admin-card-value"><?php echo $total_users; ?></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="admin-card">
                        <div class="admin-card-title">Organizers</div>
                        <div class="admin-card-value"><?php echo $total_organizers; ?></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="admin-card">
                        <div class="admin-card-title">Total Registrations</div>
                        <div class="admin-card-value"><?php echo $total_registrations; ?></div>
                    </div>
                </div>
            </div>

            <!-- Recent Events -->
            <div class="col-md-8">
                <div class="dashboard-card">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4><i class="bi bi-calendar2-week"></i> Recent Events</h4>
                        <a href="./event_management.php" class="btn btn-sm btn-primary">
                            <i class="bi bi-list"></i> View All Events
                        </a>
                    </div>
                    <div id="recent-events-list">
                        <?php
                        // Get recent events ordered by start date
                        $query = "SELECT e.*, 
                                (SELECT COUNT(*) FROM Registrations WHERE event_id = e.event_id) as current_registrations,
                                u.first_name as organizer_name
                                FROM Events e 
                                LEFT JOIN Users u ON e.organizer_id = u.user_id
                                ORDER BY e.start_datetime ASC 
                                LIMIT 5";
                        
                        $stmt = $conn->prepare($query);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        
                        if ($result->num_rows > 0) {
                            while ($event = $result->fetch_assoc()) {
                                $startDate = new DateTime($event['start_datetime']);
                                $endDate = new DateTime($event['end_datetime']);
                                ?>
                                <div class="event-card mb-3 p-3 border rounded">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h5 class="event-title mb-2">
                                                <?php echo htmlspecialchars($event['title']); ?>
                                            </h5>
                                            <div class="event-meta">
                                                <span class="event-category"><i class="bi bi-tag"></i> <?php echo htmlspecialchars($event['category']); ?></span>
                                                <span class="event-capacity"><i class="bi bi-people"></i> <?php echo $event['current_registrations']; ?>/<?php echo $event['max_capacity']; ?> registered</span>
                                            </div>
                                            <div class="event-details">
                                                <p class="mb-1"><i class="bi bi-calendar"></i> <?php echo $startDate->format('F j, Y g:i A'); ?> - <?php echo $endDate->format('F j, Y g:i A'); ?></p>
                                                <p class="mb-1"><i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($event['location']); ?></p>
                                                <p class="mb-1"><i class="bi bi-person"></i> Organized by: <?php echo htmlspecialchars($event['organizer_name']); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="event-actions mt-3">
                                        <a href="./event_management.php?action=edit&id=<?php echo $event['event_id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger delete-event" data-event-id="<?php echo $event['event_id']; ?>">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </div>
                                </div>
                                <?php
                            }
                        } else {
                            echo '<p class="text-muted">No events found.</p>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>