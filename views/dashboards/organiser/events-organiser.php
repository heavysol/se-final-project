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
                <a href="events-organiser.php" class="active">
                    <i class="fas fa-calendar"></i>
                    <span class="link-name">My Events</span>
                </a>
            </li>
            <li>
                <a href="analytics.php">
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
                            <div class="col-md-3">
                                <input type="text" class="form-control w-100" placeholder="Search events..." id="eventSearch">
                            </div>
                            <div class="col-md-2">
                                <select class="form-select w-100" id="statusFilter">
                                    <option value="">All Statuses</option>
                                    <option value="pending">Pending</option>
                                    <option value="approved">Approved</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select class="form-select w-100" id="categoryFilter">
                                    <option value="">All Categories</option>
                                    <option value="Academic">Academic</option>
                                    <option value="Social">Social</option>
                                    <option value="Cultural">Cultural</option>
                                    <option value="Sports">Sports</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select w-100" id="sortOption">
                                    <option value="date_desc">Date (Newest First)</option>
                                    <option value="date_asc">Date (Oldest First)</option>
                                    <option value="title">Title (A-Z)</option>
                                    <option value="status">Status</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button class="btn btn-primary w-100" id="applyFilters">Apply</button>
                            </div>
                    </div>
                </div>  
            </div> 
        </div>
    </div>

        <!-- Events Table -->
        <div class="card w-100">
            <div class="card-body p-0">
                <table class="table table-striped table-hover mb-0">
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
                                            <button type='button' class='btn btn-sm btn-info' onclick='viewEvent({$event['event_id']})'>
                                                <i class='fas fa-eye'></i>
                                            </button>
                                            <button type='button' class='btn btn-sm btn-primary' onclick='editEvent({$event['event_id']})'>
                                                <i class='fas fa-edit'></i>
                                            </button>
                                            <button type='button' class='btn btn-sm btn-danger' onclick='deleteEvent({$event['event_id']})'>
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
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Event</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editEventForm">
                        <input type="hidden" id="edit_event_id" name="event_id">
                        <div class="mb-3">
                            <label for="edit_title" class="form-label">Event Title</label>
                            <input type="text" class="form-control" id="edit_title" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_description" class="form-label">Description</label>
                            <textarea class="form-control" id="edit_description" name="description" required></textarea>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="edit_start_datetime" class="form-label">Start Date & Time</label>
                                <input type="datetime-local" class="form-control" id="edit_start_datetime" name="start_datetime" required>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_end_datetime" class="form-label">End Date & Time</label>
                                <input type="datetime-local" class="form-control" id="edit_end_datetime" name="end_datetime" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_location" class="form-label">Venue</label>
                            <input type="text" class="form-control" id="edit_location" name="location" required>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="edit_category" class="form-label">Category</label>
                                <select class="form-control" id="edit_category" name="category" required>
                                    <option value="Academic">Academic</option>
                                    <option value="Social">Social</option>
                                    <option value="Cultural">Cultural</option>
                                    <option value="Sports">Sports</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_max_capacity" class="form-label">Maximum Capacity</label>
                                <input type="number" class="form-control" id="edit_max_capacity" name="max_capacity" required min="1">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Add View Modal -->
    <div class="modal fade" id="viewEventModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Event Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="viewEventDetails">
                    <!-- Content will be dynamically populated -->
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
        // Initialize DataTable
        $(document).ready(function() {
            const table = $('#eventsTable').DataTable({
                processing: true,
                serverSide: false,
                ajax: {
                    url: '../../../actions/organizer_action.php?action=get_my_events',
                    type: 'GET',
                    dataSrc: 'data'
                },
                paging: false,
                scrollY: false,
                scrollX: false,
                info: false,
                columns: [
                    { 
                        data: 'title',
                        width: '20%'
                    },
                    { 
                        data: 'description',
                        width: '35%',
                        render: function(data) {
                            return data.length > 200 ? data.substr(0, 197) + '...' : data;
                        }
                    },
                    { 
                        data: 'max_capacity',
                        width: '10%',
                        className: 'text-center'
                    },
                    { 
                        data: 'category',
                        width: '15%'
                    },
                    { 
                        data: 'status',
                        width: '10%',
                        className: 'text-center',
                        render: function(data) {
                            const statusClasses = {
                                'pending': 'warning',
                                'approved': 'success',
                                'rejected': 'danger'
                            };
                            return `<span class="badge bg-${statusClasses[data] || 'secondary'}">${data}</span>`;
                        }
                    },
                    {
                        data: 'id',
                        width: '10%',
                        orderable: false,
                        className: 'text-center',
                        render: function(data, type, row) {
                            return `
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-primary edit-event" data-id="${data}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-event" data-id="${data}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            `;
                        }
                    }
                ],
                dom: '<"row"<"col-sm-12 col-md-6"f>>',
                language: {
                    search: "Search events:",
                    zeroRecords: "No matching events found",
                    emptyTable: "No events available"
                }
            });

            // Edit Event Handler
            $('#eventsTable').on('click', '.edit-event', function() {
                const eventId = $(this).data('id');
                // Fetch event details and populate modal
                fetch(`../../../actions/organizer_action.php?action=get_event&id=${eventId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const event = data.event;
                            $('#edit_event_id').val(event.id);
                            $('#edit_title').val(event.title);
                            $('#edit_description').val(event.description);
                            $('#edit_max_capacity').val(event.max_capacity);
                            $('#edit_category').val(event.category);
                            $('#edit_location').val(event.location);
                            $('#edit_start_datetime').val(event.start_datetime.slice(0, 16));
                            $('#edit_end_datetime').val(event.end_datetime.slice(0, 16));
                            $('#editEventModal').modal('show');
                        } else {
                            alert('Failed to load event details');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while loading event details');
                    });
            });

            // Delete Event Handler
            $('#eventsTable').on('click', '.delete-event', function() {
                if (confirm('Are you sure you want to delete this event?')) {
                    const eventId = $(this).data('id');
                    const formData = new FormData();
                    formData.append('action', 'delete');
                    formData.append('event_id', eventId);

                    fetch('../../../actions/organizer_action.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            table.ajax.reload();
                            alert('Event deleted successfully');
                        } else {
                            alert(data.message || 'Failed to delete event');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while deleting the event');
                    });
                }
            });

            // Edit Event Form Submit Handler
            $('#editEventForm').on('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                formData.append('action', 'update');

                fetch('../../../actions/organizer_action.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        $('#editEventModal').modal('hide');
                        table.ajax.reload();
                        alert('Event updated successfully');
                    } else {
                        alert(data.message || 'Failed to update event');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while updating the event');
                });
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            // Handle View Event
            function handleViewClick(eventId) {
                    fetch(`../../../actions/organizer_action.php?action=get_event&event_id=${eventId}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                const event = data.event;
                            // Format dates
                            const startDate = new Date(event.start_datetime).toLocaleString();
                            const endDate = new Date(event.end_datetime).toLocaleString();
                            
                            document.getElementById('viewEventDetails').innerHTML = `
                                <div class="event-info">
                                    <h4>${event.title}</h4>
                                    <p><strong>Description:</strong> ${event.description}</p>
                                    <p><strong>Category:</strong> ${event.category}</p>
                                    <p><strong>Venue:</strong> ${event.location}</p>
                                    <p><strong>Capacity:</strong> ${event.max_capacity}</p>
                                    <p><strong>Status:</strong> ${event.status}</p>
                                    <p><strong>Start Date:</strong> ${startDate}</p>
                                    <p><strong>End Date:</strong> ${endDate}</p>
                                </div>
                            `;
                            new bootstrap.Modal(document.getElementById('viewEventModal')).show();
                        } else {
                            alert('Error loading event details: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error loading event details');
                    });
            }

            // Handle Edit Event
            function handleEditClick(eventId) {
                fetch(`../../../actions/organizer_action.php?action=get_event&event_id=${eventId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const event = data.event;
                            document.getElementById('edit_event_id').value = event.event_id;
                            document.getElementById('edit_title').value = event.title;
                            document.getElementById('edit_description').value = event.description;
                            document.getElementById('edit_start_datetime').value = event.start_datetime.slice(0, 16);
                            document.getElementById('edit_end_datetime').value = event.end_datetime.slice(0, 16);
                            document.getElementById('edit_location').value = event.location;
                            document.getElementById('edit_category').value = event.category;
                            document.getElementById('edit_max_capacity').value = event.max_capacity;
                            
                            new bootstrap.Modal(document.getElementById('editEventModal')).show();
                        } else {
                            alert('Error loading event details: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error loading event details');
                    });
            }

            // Add click handlers for both buttons and icons
            document.addEventListener('click', function(e) {
                // Handle view clicks
                if (e.target.classList.contains('view-btn') || e.target.classList.contains('fa-eye')) {
                    const button = e.target.closest('.view-btn');
                    if (button) {
                        handleViewClick(button.dataset.id);
                    }
                }
                
                // Handle edit clicks
                if (e.target.classList.contains('edit-btn') || e.target.classList.contains('fa-edit')) {
                    const button = e.target.closest('.edit-btn');
                    if (button) {
                        handleEditClick(button.dataset.id);
                    }
                }
                
                // Handle delete clicks
                if (e.target.classList.contains('delete-btn') || e.target.classList.contains('fa-trash')) {
                    const button = e.target.closest('.delete-btn');
                    if (button && confirm('Are you sure you want to delete this event?')) {
                        const eventId = button.dataset.id;
                        const formData = new FormData();
                        formData.append('action', 'delete');
                        formData.append('event_id', eventId);

                        fetch('../../../actions/organizer_action.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                button.closest('tr').remove();
                                alert('Event deleted successfully');
                            } else {
                                alert('Error deleting event: ' + data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Error deleting event');
                        });
                    }
                }
            });

            // Handle Edit Form Submit
            document.getElementById('editEventForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const submitButton = this.querySelector('button[type="submit"]');
                const originalText = submitButton.innerHTML;
                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
                
                const formData = new FormData(this);
                formData.append('action', 'update');

                fetch('../../../actions/organizer_action.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Event updated successfully!');
                        location.reload();
                    } else {
                        alert('Error updating event: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error updating event');
                })
                .finally(() => {
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalText;
                });
            });
        });
    </script>
</body>
</html>