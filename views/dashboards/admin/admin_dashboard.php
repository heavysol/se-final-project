<?php
session_start();
include('../../../db/config.php');

// Get count of approved events for the dashboard

$event_query = "SELECT COUNT(*) as active_events FROM events WHERE status = 'approved'";
$event_result = $conn->query($event_query);
$active_events = $event_result->fetch_assoc()['active_events'];

// Hardcoded value for total registrations
$total_registrations = 150; // Hardcoded value

// Get total number of users
$users_query = "SELECT COUNT(*) as total_users FROM users";
$users_result = $conn->query($users_query);
$total_users = $users_result->fetch_assoc()['total_users'];

// Get count of organizers
$organizers_query = "SELECT COUNT(*) as total_organizers FROM users WHERE role = 'organizer'";
$organizers_result = $conn->query($organizers_query);
$total_organizers = $organizers_result->fetch_assoc()['total_organizers'];
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
            <li><a href="admin_dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_dashboard.php' ? 'active' : ''; ?>">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a></li>
            <li><a href="event_management.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'event_management.php' ? 'active' : ''; ?>">
                <i class="bi bi-calendar-event"></i> Events Management
            </a></li>
            <li><a href="user_management.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'user_management.php' ? 'active' : ''; ?>">
                <i class="bi bi-people"></i> User Management
            </a></li>
            <li><a href="event_organization.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'event_organization.php' ? 'active' : ''; ?>">
                <i class="bi bi-building"></i> Organizations
            </a></li>
            <li><a href="analytics.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'analytics.php' ? 'active' : ''; ?>">
                <i class="bi bi-graph-up"></i> Analytics
            </a></li>
            <li><a href="../notifications.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'notifications.php' ? 'active' : ''; ?>">
                <i class="bi bi-bell"></i> Notifications
            </a></li>
            <li><a href="../settings.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">
                <i class="bi bi-gear"></i> Settings
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

            <!-- Recent Activity Section -->
            <div class="row">
                <div class="col-md-12">
                    <div class="admin-card">
                        <h4 class="admin-card-title">Recent Activity</h4>
                        <div class="table-responsive">
                            <table class="table admin-table">
                                <thead>
                                    <tr>
                                        <th>Event</th>
                                        <th>Organizer</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Activity rows will be populated here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>