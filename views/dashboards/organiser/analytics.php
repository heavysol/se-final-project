<?php
session_start();

// Check if user is logged in and is an organizer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'organizer') {
    header("Location: ../../unauthorized.php");
    exit();
}

// Include database connection
require_once '../../../db/config.php';

// Get organizer's events statistics
$organizer_id = $_SESSION['user_id'];

// Get total events count
$total_events_query = "SELECT COUNT(*) as total FROM events WHERE organizer_id = ?";
$stmt = $conn->prepare($total_events_query);
$stmt->bind_param("i", $organizer_id);
$stmt->execute();
$total_events = $stmt->get_result()->fetch_assoc()['total'];

// Get events by status
$status_query = "SELECT status, COUNT(*) as count FROM events WHERE organizer_id = ? GROUP BY status";
$stmt = $conn->prepare($status_query);
$stmt->bind_param("i", $organizer_id);
$stmt->execute();
$status_counts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get events by category
$category_query = "SELECT category, COUNT(*) as count FROM events WHERE organizer_id = ? GROUP BY category";
$stmt = $conn->prepare($category_query);
$stmt->bind_param("i", $organizer_id);
$stmt->execute();
$category_counts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get registration trends
$registration_query = "SELECT 
    DATE(e.start_datetime) as event_date,
    COUNT(r.registration_id) as registrations
    FROM events e
    LEFT JOIN registrations r ON e.event_id = r.event_id
    WHERE e.organizer_id = ?
    GROUP BY DATE(e.start_datetime)
    ORDER BY event_date DESC
    LIMIT 10";
$stmt = $conn->prepare($registration_query);
$stmt->bind_param("i", $organizer_id);
$stmt->execute();
$registration_trends = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get top events by registration
$top_events_query = "SELECT 
    e.title,
    e.category,
    COUNT(r.registration_id) as registration_count
    FROM events e
    LEFT JOIN registrations r ON e.event_id = r.event_id
    WHERE e.organizer_id = ?
    GROUP BY e.event_id
    ORDER BY registration_count DESC
    LIMIT 5";
$stmt = $conn->prepare($top_events_query);
$stmt->bind_param("i", $organizer_id);
$stmt->execute();
$top_events = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get registration completion rate
$completion_query = "SELECT 
    COUNT(r.registration_id) as total
    FROM registrations r
    JOIN events e ON r.event_id = e.event_id
    WHERE e.organizer_id = ?";
$stmt = $conn->prepare($completion_query);
$stmt->bind_param("i", $organizer_id);
$stmt->execute();
$completion_stats = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Analytics - Campus Events</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../../assets/css/general-styles.css">
    <link rel="stylesheet" href="../../../assets/css/dashboard-styles.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <style>
        .analytics-card {
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .chart-container {
            position: relative;
            height: 300px;
            margin-bottom: 20px;
        }
        .stat-card {
            background: #fff;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .stat-card h3 {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        .stat-card p {
            color: #666;
            margin: 0;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo-container">
            <h3>Campus Events</h3>
        </div>
        <ul class="nav-list">
            <li>
                <a href="organizer_dashboard.php">
                    <i class="fas fa-home"></i>
                    <span class="link-name">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="events-organiser.php">
                    <i class="fas fa-calendar"></i>
                    <span class="link-name">My Events</span>
                </a>
            </li>
            <li>
                <a href="analytics.php" class="active">
                    <i class="fas fa-chart-bar"></i>
                    <span class="link-name">Analytics</span>
                </a>
            </li>
            <li class="logout-divider">
                <a href="../../logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    <span class="link-name">Logout</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid px-4">
            <div class="row mb-4">
                <div class="col-md-6">
                    <h2>Event Analytics</h2>
                    <p class="text-muted">View detailed statistics about your events</p>
                </div>
            </div>

            <!-- Summary Statistics -->
            <div class="row">
                <div class="col-md-4">
                    <div class="stat-card">
                        <h3><?php echo $total_events; ?></h3>
                        <p>Total Events</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <h3><?php echo $completion_stats['total']; ?></h3>
                        <p>Total Registrations</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-card">
                        <h3><?php echo round(($completion_stats['total'] / max($total_events, 1)) * 100, 1); ?>%</h3>
                        <p>Average Registrations per Event</p>
                    </div>
                </div>
            </div>

            <!-- Charts Row 1 -->
            <div class="row">
                <div class="col-md-6">
                    <div class="analytics-card">
                        <div class="card-body">
                            <h5 class="card-title">Events by Status</h5>
                            <div class="chart-container">
                                <canvas id="statusChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="analytics-card">
                        <div class="card-body">
                            <h5 class="card-title">Events by Category</h5>
                            <div class="chart-container">
                                <canvas id="categoryChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row 2 -->
            <div class="row">
                <div class="col-md-6">
                    <div class="analytics-card">
                        <div class="card-body">
                            <h5 class="card-title">Registration Trends</h5>
                            <div class="chart-container">
                                <canvas id="registrationChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="analytics-card">
                        <div class="card-body">
                            <h5 class="card-title">Top Events by Registration</h5>
                            <div class="chart-container">
                                <canvas id="topEventsChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Events Table -->
            <div class="row">
                <div class="col-12">
                    <div class="analytics-card">
                        <div class="card-body">
                            <h5 class="card-title">Top Events Details</h5>
                            <div class="table-responsive">
                                <table class="table table-striped" id="topEventsTable">
                                    <thead>
                                        <tr>
                                            <th>Event Title</th>
                                            <th>Category</th>
                                            <th>Registrations</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($top_events as $event): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($event['title']); ?></td>
                                                <td><?php echo htmlspecialchars($event['category']); ?></td>
                                                <td><?php echo $event['registration_count']; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
    $(document).ready(function() {
        // Initialize DataTable
        $('#topEventsTable').DataTable({
            searching: false,
            paging: false,
            info: false,
            ordering: false
        });

        // Status Chart
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_column($status_counts, 'status')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($status_counts, 'count')); ?>,
                    backgroundColor: [
                        '#28a745', // Approved
                        '#ffc107', // Pending
                        '#dc3545', // Rejected
                        '#6c757d'  // Other
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        // Category Chart
        const categoryCtx = document.getElementById('categoryChart').getContext('2d');
        new Chart(categoryCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($category_counts, 'category')); ?>,
                datasets: [{
                    label: 'Number of Events',
                    data: <?php echo json_encode(array_column($category_counts, 'count')); ?>,
                    backgroundColor: '#007bff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Registration Trends Chart
        const registrationCtx = document.getElementById('registrationChart').getContext('2d');
        new Chart(registrationCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($registration_trends, 'event_date')); ?>,
                datasets: [{
                    label: 'Registrations',
                    data: <?php echo json_encode(array_column($registration_trends, 'registrations')); ?>,
                    borderColor: '#17a2b8',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Top Events Chart
        const topEventsCtx = document.getElementById('topEventsChart').getContext('2d');
        new Chart(topEventsCtx, {
            type: 'horizontalBar',
            data: {
                labels: <?php echo json_encode(array_column($top_events, 'title')); ?>,
                datasets: [{
                    label: 'Registrations',
                    data: <?php echo json_encode(array_column($top_events, 'registration_count')); ?>,
                    backgroundColor: '#20c997'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                scales: {
                    x: {
                        beginAtZero: true
                    }
                }
            }
        });
    });
    </script>
</body>
</html>
