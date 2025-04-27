<?php
session_start();
include('../../../db/config.php');

// Get total number of users by role
$users_query = "SELECT role, COUNT(*) as count FROM users GROUP BY role";
$users_result = $conn->query($users_query);
$user_counts = [];
while($row = $users_result->fetch_assoc()) {
    $user_counts[$row['role']] = $row['count'];
}

// Get event statistics
$events_query = "SELECT 
    COUNT(*) as total_events,
    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_events,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_events,
    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected_events,
    SUM(CASE WHEN start_datetime > NOW() THEN 1 ELSE 0 END) as upcoming_events,
    SUM(CASE WHEN start_datetime < NOW() THEN 1 ELSE 0 END) as past_events
    FROM events";
$events_result = $conn->query($events_query);
$event_stats = $events_result->fetch_assoc();

// Get registration statistics
$registrations_query = "SELECT 
    COUNT(*) as total_registrations,
    COUNT(DISTINCT user_id) as unique_registrants,
    COUNT(DISTINCT event_id) as events_with_registrations
    FROM registrations";
$registrations_result = $conn->query($registrations_query);
$registration_stats = $registrations_result->fetch_assoc();

// Get recent activity (last 7 days)
$recent_activity_query = "SELECT 
    'event' as type, 
    title as name, 
    created_at as date,
    status
    FROM events 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    UNION ALL
    SELECT 
    'registration' as type,
    CONCAT('Registration for Event #', event_id) as name,
    registration_date as date,
    'completed' as status
    FROM registrations 
    WHERE registration_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    ORDER BY date DESC
    LIMIT 10";
$recent_activity_result = $conn->query($recent_activity_query);

// Get data for charts
$user_distribution_query = "SELECT role, COUNT(*) as count FROM users GROUP BY role";
$user_distribution_result = $conn->query($user_distribution_query);
$user_distribution = [];
while($row = $user_distribution_result->fetch_assoc()) {
    $user_distribution[] = $row;
}

$event_status_query = "SELECT status, COUNT(*) as count FROM events GROUP BY status";
$event_status_result = $conn->query($event_status_query);
$event_status = [];
while($row = $event_status_result->fetch_assoc()) {
    $event_status[] = $row;
}

$registration_trend_query = "SELECT 
    DATE(registration_date) as date,
    COUNT(*) as count
    FROM registrations
    WHERE registration_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY DATE(registration_date)
    ORDER BY date";
$registration_trend_result = $conn->query($registration_trend_query);
$registration_trend = [];
while($row = $registration_trend_result->fetch_assoc()) {
    $registration_trend[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Analytics Dashboard - Campus Events</title>
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
                    <p class="text-muted">Comprehensive overview of system statistics and trends</p>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="admin-card">
                        <h4 class="admin-card-title">User Distribution</h4>
                        <canvas id="userDistributionChart"></canvas>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="admin-card">
                        <h4 class="admin-card-title">Event Status Distribution</h4>
                        <canvas id="eventStatusChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="admin-card">
                        <h4 class="admin-card-title">Registration Trends (Last 30 Days)</h4>
                        <canvas id="registrationTrendChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="row">
                <div class="col-md-12">
                    <div class="admin-card">
                        <h4 class="admin-card-title">Recent Activity (Last 7 Days)</h4>
                        <div class="table-responsive">
                            <table class="table admin-table">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Name</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($recent_activity_result && $recent_activity_result->num_rows > 0): ?>
                                        <?php while($activity = $recent_activity_result->fetch_assoc()): ?>
                                            <tr>
                                                <td>
                                                    <span class="badge bg-<?php echo $activity['type'] == 'event' ? 'primary' : 'success'; ?>">
                                                        <?php echo ucfirst($activity['type']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo htmlspecialchars($activity['name']); ?></td>
                                                <td><?php echo date('M d, Y h:i A', strtotime($activity['date'])); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php 
                                                        echo $activity['status'] == 'approved' ? 'success' : 
                                                            ($activity['status'] == 'pending' ? 'warning' : 'info'); 
                                                    ?>">
                                                        <?php echo ucfirst($activity['status']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center">No recent activity</td>
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
    <script>
        // User Distribution Chart
        const userDistributionCtx = document.getElementById('userDistributionChart').getContext('2d');
        new Chart(userDistributionCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_column($user_distribution, 'role')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($user_distribution, 'count')); ?>,
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 99, 132, 0.8)',
                        'rgba(75, 192, 192, 0.8)'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Event Status Chart
        const eventStatusCtx = document.getElementById('eventStatusChart').getContext('2d');
        new Chart(eventStatusCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($event_status, 'status')); ?>,
                datasets: [{
                    label: 'Number of Events',
                    data: <?php echo json_encode(array_column($event_status, 'count')); ?>,
                    backgroundColor: 'rgba(75, 192, 192, 0.8)'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Registration Trend Chart
        const registrationTrendCtx = document.getElementById('registrationTrendChart').getContext('2d');
        new Chart(registrationTrendCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($registration_trend, 'date')); ?>,
                datasets: [{
                    label: 'Registrations',
                    data: <?php echo json_encode(array_column($registration_trend, 'count')); ?>,
                    borderColor: 'rgba(54, 162, 235, 1)',
                    backgroundColor: 'rgba(54, 162, 235, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html> 