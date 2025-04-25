<?php
session_start();
include('../../../db/config.php');

// Get pending events that need approval
$pending_events_query = "SELECT e.*, CONCAT(u.first_name, ' ', u.last_name) as organizer_name 
                        FROM events e 
                        JOIN users u ON e.organizer_id = u.user_id 
                        WHERE e.status = 'pending' 
                        ORDER BY e.created_at DESC";
$pending_events_result = $conn->query($pending_events_query);

// Get recent registrations
$recent_registrations_query = "SELECT r.*, e.title as event_title, CONCAT(u.first_name, ' ', u.last_name) as student_name 
                             FROM registrations r 
                             JOIN events e ON r.event_id = e.event_id 
                             JOIN users u ON r.user_id = u.user_id 
                             ORDER BY r.registration_date DESC 
                             LIMIT 10";
$recent_registrations_result = $conn->query($recent_registrations_query);

// Get system alerts (events starting soon, capacity issues, etc.)
$system_alerts_query = "SELECT 
    e.event_id,
    e.title,
    e.start_datetime,
    e.max_capacity,
    COUNT(r.registration_id) as current_registrations,
    CASE 
        WHEN e.start_datetime <= DATE_ADD(NOW(), INTERVAL 24 HOUR) THEN 'starting_soon'
        WHEN COUNT(r.registration_id) >= e.max_capacity THEN 'full_capacity'
        WHEN COUNT(r.registration_id) >= e.max_capacity * 0.8 THEN 'near_capacity'
        ELSE NULL
    END as alert_type
    FROM events e
    LEFT JOIN registrations r ON e.event_id = r.event_id
    WHERE e.status = 'approved'
    GROUP BY e.event_id
    HAVING alert_type IS NOT NULL
    ORDER BY e.start_datetime ASC";
$system_alerts_result = $conn->query($system_alerts_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Notifications - Admin Dashboard</title>
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
            <li><a href="admin_dashboard.php">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a></li>
            <li><a href="event_management.php">
                <i class="bi bi-calendar-event"></i> Events Management
            </a></li>
            <li><a href="user_management.php">
                <i class="bi bi-people"></i> User Management
            </a></li>
            <li><a href="analytics.php">
                <i class="bi bi-graph-up"></i> Analytics
            </a></li>
            <li><a href="notifications.php" class="active">
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
                    <h2>Notifications</h2>
                    <p class="text-muted">Important updates and alerts</p>
                </div>
            </div>

            <!-- Pending Events -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="admin-card">
                        <h4 class="admin-card-title">
                            <i class="bi bi-clock-history text-warning"></i> Pending Events
                            <?php if ($pending_events_result && $pending_events_result->num_rows > 0): ?>
                                <span class="badge bg-warning"><?php echo $pending_events_result->num_rows; ?></span>
                            <?php endif; ?>
                        </h4>
                        <div class="table-responsive">
                            <table class="table admin-table">
                                <thead>
                                    <tr>
                                        <th>Event Title</th>
                                        <th>Organizer</th>
                                        <th>Category</th>
                                        <th>Date Created</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($pending_events_result && $pending_events_result->num_rows > 0): ?>
                                        <?php while($event = $pending_events_result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($event['title']); ?></td>
                                                <td><?php echo htmlspecialchars($event['organizer_name']); ?></td>
                                                <td><?php echo htmlspecialchars($event['category']); ?></td>
                                                <td><?php echo date('M d, Y h:i A', strtotime($event['created_at'])); ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center">No pending events</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- System Alerts -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="admin-card">
                        <h4 class="admin-card-title">
                            <i class="bi bi-exclamation-triangle text-danger"></i> System Alerts
                        </h4>
                        <div class="table-responsive">
                            <table class="table admin-table">
                                <thead>
                                    <tr>
                                        <th>Event</th>
                                        <th>Alert Type</th>
                                        <th>Details</th>
                                        <th>Start Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($system_alerts_result && $system_alerts_result->num_rows > 0): ?>
                                        <?php while($alert = $system_alerts_result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($alert['title']); ?></td>
                                                <td>
                                                    <?php if ($alert['alert_type'] == 'starting_soon'): ?>
                                                        <span class="badge bg-warning">Starting Soon</span>
                                                    <?php elseif ($alert['alert_type'] == 'full_capacity'): ?>
                                                        <span class="badge bg-danger">Full Capacity</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-info">Near Capacity</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($alert['alert_type'] == 'starting_soon'): ?>
                                                        Event starts in less than 24 hours
                                                    <?php elseif ($alert['alert_type'] == 'full_capacity'): ?>
                                                        Event is at full capacity (<?php echo $alert['current_registrations']; ?>/<?php echo $alert['max_capacity']; ?>)
                                                    <?php else: ?>
                                                        Event is near capacity (<?php echo $alert['current_registrations']; ?>/<?php echo $alert['max_capacity']; ?>)
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo date('M d, Y h:i A', strtotime($alert['start_datetime'])); ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center">No system alerts</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Registrations -->
            <div class="row">
                <div class="col-md-12">
                    <div class="admin-card">
                        <h4 class="admin-card-title">
                            <i class="bi bi-person-plus text-success"></i> Recent Registrations
                        </h4>
                        <div class="table-responsive">
                            <table class="table admin-table">
                                <thead>
                                    <tr>
                                        <th>Event</th>
                                        <th>Student</th>
                                        <th>Registration Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($recent_registrations_result && $recent_registrations_result->num_rows > 0): ?>
                                        <?php while($registration = $recent_registrations_result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($registration['event_title']); ?></td>
                                                <td><?php echo htmlspecialchars($registration['student_name']); ?></td>
                                                <td><?php echo date('M d, Y h:i A', strtotime($registration['registration_date'])); ?></td>
                                                <td>
                                                    <span class="badge bg-success">Completed</span>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center">No recent registrations</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 