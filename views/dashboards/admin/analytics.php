<?php
require_once '../../../includes/session_handler.php';
require_once '../../../db/config.php';

// Check if user is logged in and is an admin
requireRole('admin');

// Get user data
$userData = getUserData();
if (!$userData) {
    setFlashMessage('error', 'User data not found');
    header("Location: ../../../views/login.php");
    exit();
}

// Get event statistics
$event_stats_query = "SELECT 
    COUNT(*) as total_events,
    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_events,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_events,
    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected_events,
    SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_events
    FROM events";
$event_stats = $conn->query($event_stats_query)->fetch_assoc();

// Get registration statistics
$registration_stats_query = "SELECT 
    COUNT(*) as total_registrations,
    SUM(CASE WHEN attendance_status = 'attended' THEN 1 ELSE 0 END) as attended_registrations,
    SUM(CASE WHEN attendance_status = 'missed' THEN 1 ELSE 0 END) as missed_registrations
    FROM registrations";
$registration_stats = $conn->query($registration_stats_query)->fetch_assoc();

// Get user statistics
$user_stats_query = "SELECT 
    COUNT(*) as total_users,
    SUM(CASE WHEN role = 'student' THEN 1 ELSE 0 END) as total_students,
    SUM(CASE WHEN role = 'organizer' THEN 1 ELSE 0 END) as total_organizers,
    SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as total_admins
    FROM users";
$user_stats = $conn->query($user_stats_query)->fetch_assoc();

// Get top events by registration
$top_events_query = "SELECT 
    e.title,
    e.category,
    COUNT(r.registration_id) as registration_count
    FROM events e
    LEFT JOIN registrations r ON e.event_id = r.event_id
    GROUP BY e.event_id
    ORDER BY registration_count DESC
    LIMIT 5";
$top_events = $conn->query($top_events_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../../assets/css/general-styles.css">
    <link rel="stylesheet" href="../../../assets/css/admin-styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .admin-sidebar {
            background: var(--primary-color);
            color: var(--text-light);
            width: 250px;
            position: fixed;
            height: 100vh;
            padding-top: 0;
            box-shadow: 4px 0 10px var(--shadow-color);
        }
        
        .admin-sidebar-header {
            padding: 20px;
            background: var(--primary-color);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
        }
        
        .admin-sidebar-header h3 {
            color: var(--text-light);
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        
        .admin-sidebar-header .small {
            color: var(--text-light);
            opacity: 0.8;
            font-size: 14px;
            margin: 5px 0 0 0;
        }
        
        .admin-sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .admin-sidebar-menu li a {
            color: var(--text-light);
            text-decoration: none;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            transition: background-color 0.3s ease;
        }
        
        .admin-sidebar-menu li a:hover {
            background-color: var(--hover-color);
        }
        
        .admin-sidebar-menu li a.active {
            background-color: var(--active-color);
        }
        
        .admin-sidebar-menu li a i {
            margin-right: 10px;
            font-size: 1.2rem;
        }
        
        .admin-sidebar-menu li:last-child {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            margin-top: 20px;
        }

        .admin-main-content {
            margin-left: 250px;
            padding: 2rem;
            background-color: var(--background-color);
            min-height: 100vh;
        }

        .analytics-card {
            background-color: var(--background-color);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .analytics-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px var(--shadow-color);
        }

        .analytics-title {
            color: var(--text-primary);
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .analytics-value {
            color: var(--primary-color);
            font-size: 2rem;
            font-weight: 700;
        }

        .analytics-label {
            color: var(--text-secondary);
            font-size: 0.875rem;
        }

        .chart-container {
            background-color: var(--background-color);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: var(--text-light);
        }

        .btn-primary:hover {
            background-color: var(--hover-color);
            border-color: var(--hover-color);
        }

        .text-muted {
            color: var(--text-secondary) !important;
        }

        h2 {
            color: var(--text-primary);
        }

        .table {
            background-color: var(--background-color);
            color: var(--text-primary);
        }

        .table thead th {
            background-color: var(--secondary-color);
            color: var(--text-primary);
            border-bottom: 2px solid var(--border-color);
        }

        .table tbody tr {
            border-bottom: 1px solid var(--border-color);
        }

        .table tbody tr:hover {
            background-color: var(--hover-color);
        }

        .activity-status {
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .status-completed {
            background-color: var(--success-color);
            color: var(--text-light);
        }

        .status-pending {
            background-color: var(--warning-color);
            color: var(--text-light);
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
            <li><a href="event_management.php">
                <i class="bi bi-calendar-event"></i> Events Management
            </a></li>
            <li><a href="user_management.php">
                <i class="bi bi-people"></i> User Management
            </a></li>
            <li><a href="analytics.php" class="active">
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
                    <h2>Analytics Dashboard</h2>
                    <p class="text-muted">View detailed statistics and insights</p>
                </div>
            </div>

            <!-- Event Statistics -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Event Statistics</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="admin-card">
                                        <div class="admin-card-title">Total Events</div>
                                        <div class="admin-card-value"><?php echo $event_stats['total_events']; ?></div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="admin-card">
                                        <div class="admin-card-title">Approved Events</div>
                                        <div class="admin-card-value"><?php echo $event_stats['approved_events']; ?></div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="admin-card">
                                        <div class="admin-card-title">Pending Events</div>
                                        <div class="admin-card-value"><?php echo $event_stats['pending_events']; ?></div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="admin-card">
                                        <div class="admin-card-title">Rejected Events</div>
                                        <div class="admin-card-value"><?php echo $event_stats['rejected_events']; ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Registration Statistics -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Registration Statistics</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="admin-card">
                                        <div class="admin-card-title">Total Registrations</div>
                                        <div class="admin-card-value"><?php echo $registration_stats['total_registrations']; ?></div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="admin-card">
                                        <div class="admin-card-title">Attended</div>
                                        <div class="admin-card-value"><?php echo $registration_stats['attended_registrations']; ?></div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="admin-card">
                                        <div class="admin-card-title">Missed</div>
                                        <div class="admin-card-value"><?php echo $registration_stats['missed_registrations']; ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- User Statistics -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">User Statistics</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="admin-card">
                                        <div class="admin-card-title">Total Users</div>
                                        <div class="admin-card-value"><?php echo $user_stats['total_users']; ?></div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="admin-card">
                                        <div class="admin-card-title">Students</div>
                                        <div class="admin-card-value"><?php echo $user_stats['total_students']; ?></div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="admin-card">
                                        <div class="admin-card-title">Organizers</div>
                                        <div class="admin-card-value"><?php echo $user_stats['total_organizers']; ?></div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="admin-card">
                                        <div class="admin-card-title">Admins</div>
                                        <div class="admin-card-value"><?php echo $user_stats['total_admins']; ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Events -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Top Events by Registration</h5>
                        </div>
                        <div class="card-body">
                            <?php if ($top_events->num_rows > 0): ?>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Event Title</th>
                                                <th>Category</th>
                                                <th>Registrations</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($event = $top_events->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($event['title']); ?></td>
                                                    <td><?php echo htmlspecialchars($event['category']); ?></td>
                                                    <td><?php echo $event['registration_count']; ?></td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-muted">No events found.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 