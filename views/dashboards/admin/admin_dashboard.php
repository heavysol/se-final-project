<?php
session_start();
include('../../../db/config.php');

// Get count of approved events
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
    <link rel="stylesheet" href="../../../assets/css/general-styles.css">
    <link rel="stylesheet" href="../../../assets/css/dashboard-styles.css">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h3>Campus Events</h3>
            <div class="text-white-50 small">Admin Dashboard</div>
        </div>
        <ul class="sidebar-menu">
            <li><a href="admin_dashboard.php" class="active"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
            <li><a href="event_management.php"><i class="bi bi-calendar-event"></i> Events Management</a></li>
            <li><a href="user_management.php"><i class="bi bi-people"></i> User Management</a></li>
            <li><a href="event_organization.php"><i class="bi bi-building"></i> Organizations</a></li>
            <li><a href="analytics.html"><i class="bi bi-graph-up"></i> Analytics</a></li>
            <li><a href="../notifications.php"><i class="bi bi-bell"></i> Notifications</a></li>
            <li><a href="../settings.php"><i class="bi bi-gear"></i> Settings</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <div class="row mb-4">
                <div class="col-md-6">
                    <h2>Administrator Dashboard</h2>
                    <p class="text-muted">Overview of campus events and activities</p>
                </div>
                <div class="col-md-6 text-end">
                    <div class="btn-group">
                        <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-file-earmark-arrow-down"></i> Export
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#">PDF Report</a></li>
                            <li><a class="dropdown-item" href="#">Excel Spreadsheet</a></li>
                            <li><a class="dropdown-item" href="#">CSV Data</a></li>
                        </ul>
                    </div>
                    <!-- <button class="btn btn-primary ms-2"><i class="bi bi-plus"></i> Create New Event</button> -->
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row g-3 my-2">
                <div class="col-md-3">
                    <div class="p-3 bg-white shadow-sm d-flex justify-content-around align-items-center rounded">
                        <div>
                            <h3 class="fs-2"><?php echo $active_events; ?></h3>
                            <p class="fs-5">Active Events</p>
                        </div>
                        <i class="fas fa-calendar-check fs-1 primary-text"></i>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="p-3 bg-white shadow-sm d-flex justify-content-around align-items-center rounded">
                        <div>
                            <h3 class="fs-2"><?php echo $total_registrations; ?></h3>
                            <p class="fs-5">Event Registrations</p>
                        </div>
                        <i class="fas fa-ticket-alt fs-1 primary-text"></i>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="p-3 bg-white shadow-sm d-flex justify-content-around align-items-center rounded">
                        <div>
                            <h3 class="fs-2"><?php echo $total_users; ?></h3>
                            <p class="fs-5">Total Users</p>
                        </div>
                        <i class="fas fa-users fs-1 primary-text"></i>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="p-3 bg-white shadow-sm d-flex justify-content-around align-items-center rounded">
                        <div>
                            <h3 class="fs-2"><?php echo $total_organizers; ?></h3>
                            <p class="fs-5">Active Organizations</p>
                        </div>
                        <i class="fas fa-building fs-1 primary-text"></i>
                    </div>
                </div>
            </div>

            <!-- Main content area can be used for other dashboard features -->
            <div class="row my-5">
                <div class="col">
                    <!-- Add any additional dashboard content here -->
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="dashboard-card">
                       
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Event Name</th>
                                        <th>Organizer</th>
                                        <th>Date</th>
                                        <th>Registered</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Query to get events
                                    $query = "SELECT * FROM events ORDER BY created_at DESC";
                                    $result = $conn->query($query);

                                    if ($result && $result->num_rows > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                            // Determine status class
                                            $statusClass = '';
                                            switch(strtolower($row['status'])) {
                                                case 'approved':
                                                    $statusClass = 'active';
                                                    break;
                                                case 'pending':
                                                    $statusClass = 'pending';
                                                    break;
                                                case 'rejected':
                                                    $statusClass = 'cancelled';
                                                    break;
                                                default:
                                                    $statusClass = '';
                                            }

                                            // Format the date
                                            $eventDate = date('d M Y', strtotime($row['start_datetime']));
                                            
                                            // Hardcoded registration value (you can modify this as needed)
                                            $registrationRatio = "0/100";

                                            echo "<tr>";
                                            echo "<td>{$row['title']}</td>";
                                            echo "<td>{$row['organizer_id']}</td>";
                                            echo "<td>{$eventDate}</td>";
                                            echo "<td>{$registrationRatio}</td>";
                                            echo "<td><span class='event-status {$statusClass}'>" . ucfirst($row['status']) . "</span></td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='5' class='text-center'>No events found</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
    
            </div>
        </div>
    </div>
</div>
</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css"></script>
</body>
</html>