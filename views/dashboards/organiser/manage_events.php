<?php
session_start();
include('../../../db/config.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Events - Campus Events</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../../assets/css/general-styles.css">
    <link rel="stylesheet" href="../../../assets/css/dashboard-styles.css">
    <!-- Include your sidebar styles here -->
</head>
<body>
    <!-- Include your sidebar here -->

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <div class="row mb-4">
                <div class="col-md-6">
                    <h2>My Events</h2>
                </div>
            </div>

            <div class="dashboard-card">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Event Name</th>
                                <th>Date</th>
                                <th>Location</th>
                                <th>Registration</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Get events for the current organizer
                            $organizer_id = $_SESSION['user_id'];
                            $query = "SELECT * FROM events WHERE organizer_id = ? ORDER BY start_datetime";
                            $stmt = $conn->prepare($query);
                            $stmt->bind_param("i", $organizer_id);
                            $stmt->execute();
                            $result = $stmt->get_result();

                            while ($event = $result->fetch_assoc()) {
                                // Get registration count
                                $reg_query = "SELECT COUNT(*) as count FROM event_registrations WHERE event_id = ?";
                                $reg_stmt = $conn->prepare($reg_query);
                                $reg_stmt->bind_param("i", $event['event_id']);
                                $reg_stmt->execute();
                                $reg_result = $reg_stmt->get_result();
                                $reg_count = $reg_result->fetch_assoc()['count'];

                                // Calculate progress percentage
                                $progress = ($reg_count / $event['max_capacity']) * 100;
                                $progress_class = $progress >= 80 ? 'bg-success' : ($progress >= 50 ? 'bg-warning' : 'bg-info');

                                // Format date
                                $event_date = date('d M Y', strtotime($event['start_datetime']));

                                echo "<tr>";
                                echo "<td>{$event['title']}</td>";
                                echo "<td>{$event_date}</td>";
                                echo "<td>{$event['location']}</td>";
                                echo "<td>
                                        <div class='d-flex align-items-center'>
                                            <div class='flex-grow-1 me-2'>
                                                <div class='progress'>
                                                    <div class='progress-bar {$progress_class}' role='progressbar' 
                                                         style='width: {$progress}%' aria-valuenow='{$progress}' 
                                                         aria-valuemin='0' aria-valuemax='100'></div>
                                                </div>
                                            </div>
                                            <span>{$reg_count}/{$event['max_capacity']}</span>
                                        </div>
                                      </td>";
                                echo "<td><span class='event-status {$event['status']}'>" . ucfirst($event['status']) . "</span></td>";
                                echo "<td>
                                        <a href='view_event.php?id={$event['event_id']}' class='btn btn-sm btn-info'><i class='bi bi-eye'></i></a>
                                        <a href='edit_event.php?id={$event['event_id']}' class='btn btn-sm btn-primary'><i class='bi bi-pencil'></i></a>
                                        <a href='generate_qr.php?id={$event['event_id']}' class='btn btn-sm btn-secondary'><i class='bi bi-qr-code'></i></a>
                                      </td>";
                                echo "</tr>";
                            }

                            if ($result->num_rows === 0) {
                                echo "<tr><td colspan='6' class='text-center'>No events found</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 