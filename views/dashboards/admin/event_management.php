<?php 
    session_start();
    include('db/config.php');

    // Check if the user is logged in
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }

    // Check if the user is an admin
    $user_id = $_SESSION['user_id'];
    $query = "SELECT role FROM Users WHERE user_id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if ($user['role'] !== 'admin') {
        header('Location: index.php');
        exit;
    }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
</head>

<body>

    <div class="container">
        <div class="row mt-3">
            <div class="col-12">
                <h4>Event Management</h4>
                <!-- Tabs Navigation -->
                <ul class="nav nav-tabs" id="eventTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link active" id="approval-tab" data-bs-toggle="tab" href="#approvalView" role="tab" aria-controls="approvalView" aria-selected="true">Approval Queue</a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="create-tab" data-bs-toggle="tab" href="#createEventView" role="tab" aria-controls="createEventView" aria-selected="false">Create New Event</a>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content mt-3" id="eventTabsContent">
                    <!-- Approval Queue Tab -->
                    <div class="tab-pane fade show active" id="approvalView" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" id="selectAllPending"></th>
                                        <th>Title</th>
                                        <th>Date & Time</th>
                                        <th>Organization</th>
                                        <th>Venue</th>
                                        <th>Submitted By</th>
                                        <th>Submission Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="pendingEventsTable">
                                    <!-- PHP Code to Fetch Pending Events -->
                                    <?php
                                        $query = "SELECT * FROM Events WHERE is_public = 0";
                                        $stmt = $pdo->query($query);
                                        while ($event = $stmt->fetch()) {
                                            echo "<tr>";
                                            echo "<td><input type='checkbox' class='pending-select' data-id='{$event['event_id']}'></td>";
                                            echo "<td>{$event['title']}</td>";
                                            echo "<td>{$event['start_datetime']} - {$event['end_datetime']}</td>";
                                            echo "<td>{$event['category']}</td>";
                                            echo "<td>{$event['location']}</td>";
                                            echo "<td>{$event['organizer_id']}</td>";
                                            echo "<td>{$event['created_at']}</td>";
                                            echo "<td>";
                                            echo "<button class='btn btn-sm btn-info' onclick='viewPendingEvent({$event['event_id']})'>Review</button>";
                                            echo "<button class='btn btn-sm btn-success' onclick='approveEvent({$event['event_id']})'>Approve</button>";
                                            echo "<button class='btn btn-sm btn-danger' onclick='rejectEvent({$event['event_id']})'>Reject</button>";
                                            echo "</td>";
                                            echo "</tr>";
                                        }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Create New Event Tab -->
                    <div class="tab-pane fade" id="createEventView" role="tabpanel">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createEventModal">Create New Event</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Event Creation Modal -->
    <div class="modal fade" id="createEventModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create New Event</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="eventCreationForm">
                        <!-- Form Content as previously provided -->
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // JavaScript functions for handling event actions
        function approveEvent(eventId) {
            fetch('event_management_action.php', {
                method: 'POST',
                body: new URLSearchParams({
                    action: 'approve',
                    event_id: eventId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert(data.message);
                }
            });
        }

        function rejectEvent(eventId) {
            fetch('event_management_action.php', {
                method: 'POST',
                body: new URLSearchParams({
                    action: 'reject',
                    event_id: eventId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert(data.message);
                }
            });
        }
    </script>
</body>

</html>
