<?php
session_start();

// Check if user is logged in and is an organizer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'organizer') {
    header("Location: ../../unauthorized.php");
    exit();
}

// Include database connection with error checking
$database_path = '../../../db/config.php';
if (!file_exists($database_path)) {
    die("Error: Database configuration file not found");
}

require_once $database_path;

// Verify database connection
if (!isset($conn) || $conn->connect_error) {
    die("Database connection failed: " . ($conn->connect_error ?? "Connection variable not set"));
}

// Test the connection
try {
    $test_query = "SELECT 1";
    $test_result = $conn->query($test_query);
    if (!$test_result) {
        throw new Exception("Database connection test failed");
    }
} catch (Exception $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Events - Campus Events</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../../assets/css/general-styles.css">
    <link rel="stylesheet" href="../../../assets/css/dashboard-styles.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <style>
        /* Main content styles */
        .main-content {
            padding: 20px;
            width: calc(100% - 250px); /* Adjust based on your sidebar width */
            margin-left: 250px;
            overflow: hidden;
        }

        .container-fluid {
            width: 100%;
            padding: 0 20px;
            margin: 0;
            overflow: hidden;
        }

        /* Card styles */
        .card {
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            overflow: hidden;
        }

        /* Table styles */
        .table-responsive {
            overflow: visible;
        }

        .table {
            width: 100%;
            margin-bottom: 0;
            table-layout: fixed;
        }

        .table th,
        .table td {
            padding: 12px;
            vertical-align: middle;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Column widths */
        .table th:nth-child(1) { width: 20%; } /* Title */
        .table th:nth-child(2) { width: 12%; } /* Date */
        .table th:nth-child(3) { width: 12%; } /* Category */
        .table th:nth-child(4) { width: 20%; } /* Venue */
        .table th:nth-child(5) { width: 8%; }  /* Capacity */
        .table th:nth-child(6) { width: 12%; } /* Status */
        .table th:nth-child(7) { width: 16%; } /* Actions */

        /* Form control styles */
        .form-control, 
        .form-select {
            height: 36px;
            font-size: 14px;
        }

        /* Button styles */
        .btn-group {
            display: flex;
            gap: 5px;
            justify-content: flex-start;
        }

        .btn-group .btn {
            padding: 4px 8px;
        }

        /* Status badge styles */
        .badge {
            padding: 6px 10px;
            font-size: 12px;
            font-weight: 500;
        }

        /* Row spacing */
        .row {
            margin: 0;
            width: 100%;
        }

        /* Search and filter section */
        .search-filters .form-control,
        .search-filters .form-select {
            height: 36px;
            font-size: 14px;
        }

        /* Ensure content fits without scrolling */
        .card-body {
            padding: 0;
        }

        /* Make sure the table header looks good */
        .table thead th {
            background-color: #f8f9fa;
            font-weight: 600;
            border-bottom: 2px solid #dee2e6;
            padding: 12px;
        }

        /* Ensure consistent row height */
        .table tbody td {
            padding: 12px;
            line-height: 1.2;
        }

        /* Remove any horizontal scroll possibility */
        html, body {
            overflow-x: hidden;
        }

        /* Hide DataTables controls */
        .dataTables_length,
        .dataTables_filter,
        .dataTables_info,
        .dataTables_paginate {
            display: none !important;
        }
        
        /* Ensure table takes full width */
        .table {
            width: 100% !important;
        }

        .logo-container {
            padding: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
        }
        .logo-container h2 {
            color: var(--text-light);
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .logo-container .subtitle {
            color: rgba(255, 255, 255, 0.7);
            font-size: 14px;
            margin: 5px 0 0 0;
        }
        .sidebar {
            background: var(--primary-color);
            color: var(--text-light);
            width: 250px;
            position: fixed;
            height: 100vh;
            padding-top: 0;
            box-shadow: 4px 0 10px var(--shadow-color);
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
            transition: margin-left 0.3s ease;
        }

        .nav-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .nav-list li a {
            color: var(--text-light);
            text-decoration: none;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            transition: background-color 0.3s ease;
        }
    </style>
</head>
    <body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo-container">
            <h2>Campus Events</h2>
            <p class="subtitle">Organizer Dashboard</p>
        </div>

        <ul class="nav-list">
            <li>
                <a href="../../../index.php">
                    <i class="bi bi-house-door"></i>
                    <span class="link-name">Home</span>
                </a>
            </li>
            <li>
                <a href="organizer_dashboard.php">
                    <i class="bi bi-speedometer2"></i>
                    <span class="link-name">Dashboard</span>
                </a>
            </li>
            <li>
                <li>
                    <a href="events-organiser.php">
                        <i class="bi bi-calendar-event"></i>
                        <span class="link-name">My Events</span>
                    </a>
                </li>
                <li>
                    <a href="analytics.php" class="active">
                        <i class="bi bi-graph-up"></i>
                        <span class="link-name">Analytics</span>
                    </a>
                </li>
                <li class="logout-divider">
                    <a href="../../logout.php">
                        <i class="bi bi-box-arrow-right"></i>
                        <span class="link-name">Logout</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Main Content -->
<div class="main-content">
    <div class="container-fluid px-4">
        <div class="row mb-4">
            <div class="col-md-6">
                <h2>My Events</h2>
                <p class="text-muted">Manage your events here</p>
            </div>
        </div>

        <!-- Search and Filter Options -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card w-100">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <div class="input-group">
                                    <input type="text" class="form-control" placeholder="Search events by title..." id="eventSearch">
                                    <button class="btn btn-outline-secondary" type="button" id="clearSearch">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Events Table -->
        <div class="card w-100">
            <div class="card-body p-0">
                <table class="table table-striped table-hover mb-0" id="eventsTable">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Date</th>
                            <th>Category</th>
                            <th>Venue</th>
                            <th>Capacity</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "SELECT * FROM events WHERE organizer_id = ? ORDER BY created_at DESC";
                        $stmt = $conn->prepare($query);
                        $stmt->bind_param("i", $_SESSION['user_id']);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($result->num_rows > 0) {
                            while ($event = $result->fetch_assoc()) {
                                // Format dates
                                $start_date = date('M d, Y', strtotime($event['start_datetime']));
                                
                                // Determine status badge class
                                $statusClass = '';
                                switch(strtolower($event['status'])) {
                                    case 'approved':
                                        $statusClass = 'bg-success';
                                        break;
                                    case 'pending':
                                        $statusClass = 'bg-warning text-dark';
                                        break;
                                    case 'rejected':
                                        $statusClass = 'bg-danger';
                                        break;
                                    default:
                                        $statusClass = 'bg-secondary';
                                }

                                echo "<tr>";
                                echo "<td>{$event['title']}</td>";
                                echo "<td>{$start_date}</td>";
                                echo "<td>{$event['category']}</td>";
                                echo "<td>{$event['location']}</td>";
                                echo "<td>{$event['max_capacity']}</td>";
                                echo "<td><span class='badge {$statusClass}'>" . ucfirst($event['status']) . "</span></td>";
                                echo "<td>
                                        <div class='btn-group'>
                                            <button type='button' class='btn btn-sm btn-info view-event' data-event-id='{$event['event_id']}'>
                                                <i class='fas fa-eye'></i>
                                            </button>
                                            <button type='button' class='btn btn-sm btn-primary edit-event' data-event-id='{$event['event_id']}'>
                                                <i class='fas fa-edit'></i>
                                            </button>
                                            <button type='button' class='btn btn-sm btn-danger delete-event' data-event-id='{$event['event_id']}'>
                                                <i class='fas fa-trash'></i>
                                            </button>
                                        </div>
                                    </td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='7' class='text-center'>No events found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

    <!-- Edit Event Modal -->
    <div class="modal fade" id="editEventModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Event</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editEventForm">
                        <input type="hidden" id="editEventId" name="event_id">
                        <div class="mb-3">
                            <label for="editTitle" class="form-label">Title</label>
                            <input type="text" class="form-control" id="editTitle" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="editDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="editDescription" name="description" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="editLocation" class="form-label">Location</label>
                            <input type="text" class="form-control" id="editLocation" name="location" required>
                        </div>
                        <div class="mb-3">
                            <label for="editCategory" class="form-label">Category</label>
                            <select class="form-control" id="editCategory" name="category" required>
                                <option value="Academic">Academic</option>
                                <option value="Cultural">Cultural</option>
                                <option value="Social">Social</option>
                                <option value="Sports">Sports</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="editMaxCapacity" class="form-label">Maximum Capacity</label>
                            <input type="number" class="form-control" id="editMaxCapacity" name="max_capacity" required>
                        </div>
                        <div class="mb-3">
                            <label for="editStartDatetime" class="form-label">Start Date & Time</label>
                            <input type="datetime-local" class="form-control" id="editStartDatetime" name="start_datetime" required>
                        </div>
                        <div class="mb-3">
                            <label for="editEndDatetime" class="form-label">End Date & Time</label>
                            <input type="datetime-local" class="form-control" id="editEndDatetime" name="end_datetime" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="editEventForm" class="btn btn-primary" id="saveEdit">Save Changes</button>
                </div>
            </div>
        </div>
    </div>

    <!-- View Event Modal -->
    <div class="modal fade" id="viewEventModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Event Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <h6>Title</h6>
                        <p id="viewEventTitle"></p>
                    </div>
                    <div class="mb-3">
                        <h6>Description</h6>
                        <p id="viewEventDescription"></p>
                    </div>
                    <div class="mb-3">
                        <h6>Location</h6>
                        <p id="viewEventLocation"></p>
                    </div>
                    <div class="mb-3">
                        <h6>Category</h6>
                        <p id="viewEventCategory"></p>
                    </div>
                    <div class="mb-3">
                        <h6>Maximum Capacity</h6>
                        <p id="viewEventCapacity"></p>
                    </div>
                    <div class="mb-3">
                        <h6>Start Date & Time</h6>
                        <p id="viewEventStart"></p>
                    </div>
                    <div class="mb-3">
                        <h6>End Date & Time</h6>
                        <p id="viewEventEnd"></p>
                    </div>
                    <div class="mb-3">
                        <h6>Status</h6>
                        <p id="viewEventStatus"></p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
        // View Event
        $('.view-event').click(function() {
            const eventId = $(this).data('event-id');
            $.ajax({
                url: '../../../actions/organizer_action.php',
                method: 'POST',
                data: {
                    action: 'view',
                    event_id: eventId
                },
                success: function(response) {
                    if (response.success && response.event) {
                        const event = response.event;
                        // Populate modal with event details
                        $('#viewEventTitle').text(event.title);
                        $('#viewEventDescription').text(event.description);
                        $('#viewEventLocation').text(event.location);
                        $('#viewEventCategory').text(event.category);
                        $('#viewEventCapacity').text(event.max_capacity);
                        $('#viewEventStart').text(new Date(event.start_datetime).toLocaleString());
                        $('#viewEventEnd').text(new Date(event.end_datetime).toLocaleString());
                        $('#viewEventStatus').text(event.status);
                        
                        // Show the modal
                        $('#viewEventModal').modal('show');
                    } else {
                        alert('Error loading event details: ' + (response.message || 'Unknown error'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    alert('Error loading event details. Please try again.');
                }
            });
        });

        // Edit Event
        $('.edit-event').click(function() {
            const eventId = $(this).data('event-id');
            $.ajax({
                url: '../../../actions/organizer_action.php',
                method: 'POST',
                data: {
                    action: 'view',
                    event_id: eventId
                },
                success: function(response) {
                    if (response.success && response.event) {
                        const event = response.event;
                        // Populate edit form with event details
                        $('#editEventId').val(event.event_id);
                        $('#editTitle').val(event.title);
                        $('#editDescription').val(event.description);
                        $('#editLocation').val(event.location);
                        $('#editCategory').val(event.category);
                        $('#editMaxCapacity').val(event.max_capacity);
                        $('#editStartDatetime').val(event.start_datetime.replace(' ', 'T'));
                        $('#editEndDatetime').val(event.end_datetime.replace(' ', 'T'));
                        
                        // Show the modal
                        $('#editEventModal').modal('show');
                    } else {
                        alert('Error loading event details: ' + (response.message || 'Unknown error'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    alert('Error loading event details. Please try again.');
                }
            });
        });

        // Handle edit form submission
        $('#editEventForm').submit(function(e) {
            e.preventDefault();
            
            // Show loading state
            const submitButton = $('#saveEdit');
            submitButton.prop('disabled', true)
                       .html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...');

            const formData = new FormData(this);
            formData.append('action', 'edit');

            $.ajax({
                url: '../../../actions/organizer_action.php',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        alert('Event updated successfully!');
                        $('#editEventModal').modal('hide');
                        location.reload();
                    } else {
                        alert('Error updating event: ' + (response.message || 'Unknown error'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    alert('Error updating event. Please try again.');
                },
                complete: function() {
                    submitButton.prop('disabled', false).html('Save Changes');
                }
            });
        });

        // Delete Event
        $('.delete-event').click(function() {
            if (confirm('Are you sure you want to delete this event? This action cannot be undone.')) {
                const eventId = $(this).data('event-id');
                $.ajax({
                    url: '../../../actions/organizer_action.php',
                    method: 'POST',
                    data: {
                        action: 'delete',
                        event_id: eventId
                    },
                    success: function(response) {
                        if (response.success) {
                            alert('Event deleted successfully!');
                            location.reload();
                        } else {
                            alert('Error deleting event: ' + (response.message || 'Unknown error'));
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                        alert('Error deleting event. Please try again.');
                    }
                });
            }
        });

        // Initialize DataTable with minimal features
        $('#eventsTable').DataTable({
            searching: false,
            paging: false,
            info: false,
            ordering: false,
            responsive: true
        });
    });
    </script>
</body>
</html>