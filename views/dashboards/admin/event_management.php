<?php 
session_start();
include('../../../db/config.php'); // Ensure the path is correct

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Check if the user is an admin
$user_id = $_SESSION['user_id'];
$query = "SELECT role FROM Users WHERE user_id = ?";
$stmt = $conn->prepare($query);

if ($stmt) {
    $stmt->bind_param("s", $user_id); // "s" means string
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user || $user['role'] !== 'admin') {
        header('Location: index.php');
        exit;
    }

    $stmt->close();
} else {
    echo "Failed to prepare the statement.";
    exit;
}

// Calculate the number of pending events
$query = "SELECT COUNT(*) as pending_count FROM events WHERE status = 'pending'";
$result = $conn->query($query);

if ($result) {
    $row = $result->fetch_assoc();
    $pendingCount = $row['pending_count'];
} else {
    $pendingCount = 0;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Comprehensive Event Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.2/main.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../../assets/css/general-styles.css">
    <link rel="stylesheet" href="../../../assets/css/dashboard-styles.css">
</head>
<body>
    <div class="container-fluid mt-3">
        <h2 class="mb-4">Event Management System</h2>
        
        <!-- Navigation Tabs -->
        <ul class="nav nav-tabs mb-4" id="eventTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="list-tab" data-bs-toggle="tab" data-bs-target="#listView" type="button">
                    <i class="fas fa-list"></i> Event List
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="calendar-tab" data-bs-toggle="tab" data-bs-target="#calendarView" type="button">
                    <i class="fas fa-calendar-alt"></i> Calendar
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="approval-tab" data-bs-toggle="tab" data-bs-target="#approvalView" type="button">
                    <i class="fas fa-check-circle"></i> Approval Queue 
                    <span class="badge bg-danger" id="pendingCount"><?php echo $pendingCount; ?></span>
                </button>
            </li>
        </ul>
        
        <!-- Tab Content -->
        <div class="tab-content" id="eventTabContent">
            <!-- Event List View -->
            <div class="tab-pane fade show active" id="listView" role="tabpanel">
                <div class="row">
                    <div class="col-12 mb-3">
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createEventModal">
                            <i class="fas fa-plus"></i> Create New Event
                        </button>
                        
                        <div class="float-end">
                            <div class="btn-group">
                                <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                    Bulk Actions
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#" id="bulkApprove">Approve Selected</a></li>
                                    <li><a class="dropdown-item" href="#" id="bulkCancel">Cancel Selected</a></li>
                                    <li><a class="dropdown-item" href="#" id="bulkDelete">Delete Selected</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Search and Filter Options -->
                    <div class="col-12 search-filters mb-3">
                        <div class="row">
                            <div class="col-md-3">
                                <input type="text" class="form-control" placeholder="Search events..." id="eventSearch">
                            </div>
                            <div class="col-md-2">
                                <select class="form-select" id="statusFilter">
                                    <option value="">All Statuses</option>
                                    <option value="active">Active</option>
                                    <option value="pending">Pending</option>
                                    <option value="cancelled">Cancelled</option>
                                    <option value="completed">Completed</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select class="form-select" id="categoryFilter">
                                    <option value="">All Categories</option>
                                    <option value="Academic">Academic</option>
                                    <option value="Social">Social</option>
                                    <option value="Career">Career</option>
                                    <option value="Cultural">Cultural</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" id="sortOption">
                                    <option value="date_asc">Date (Ascending)</option>
                                    <option value="date_desc">Date (Descending)</option>
                                    <option value="title">Title (A-Z)</option>
                                    <option value="organization">Organization</option>
                                    <option value="venue">Venue</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button class="btn btn-primary w-100" id="applyFilters">Apply</button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Event Table -->
                    <div class="col-12">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" id="selectAll"></th>
                                        <th>Title</th>
                                        <th>Date & Time</th>
                                        <th>Category</th>
                                        <th>Venue</th>
                                        <th>Organizer</th>
                                        <th>Capacity</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="eventsTableBody">
                                <?php
                $query = "SELECT * FROM events";
                $result = $conn->query($query);

                if ($result) {
                    while ($event = $result->fetch_assoc()) {
                        $eventId = $event['event_id'] ?? '';
                        $maxCapacity = $event['max_capacity'] ?? 'N/A';
                        
                        // Determine the appropriate Bootstrap badge class
                        $statusBadgeClass = '';
                        switch(strtolower($event['status'])) {
                            case 'approved':
                                $statusBadgeClass = 'bg-success';
                                break;
                            case 'pending':
                                $statusBadgeClass = 'bg-warning text-dark';
                                break;
                            case 'rejected':
                                $statusBadgeClass = 'bg-danger';
                                break;
                            default:
                                $statusBadgeClass = 'bg-secondary';
                        }
                        
                        echo "<tr data-event-id='{$eventId}'>";
                        echo "<td><input type='checkbox' class='event-select' data-id='{$eventId}'></td>";
                        echo "<td>{$event['title']}</td>";
                        echo "<td>{$event['start_datetime']} - {$event['end_datetime']}</td>";
                        echo "<td>{$event['category']}</td>";
                        echo "<td>{$event['location']}</td>";
                        echo "<td>{$event['organizer_id']}</td>";
                        echo "<td>{$maxCapacity}</td>";
                        echo "<td><span class='badge {$statusBadgeClass}'>" . ucfirst($event['status']) . "</span></td>";
                        echo "<td>
                                <button class='btn btn-sm btn-info' onclick='viewEventDetails({$eventId})'>View</button>
                                <button class='btn btn-sm btn-primary' onclick='editEvent({$eventId})'>Edit</button>
                                <button class='btn btn-sm btn-danger' onclick='deleteEvent({$eventId})'>Delete</button>
                            </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='9'>Failed to fetch events.</td></tr>";
                }
                ?>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Calendar View -->
            <div class="tab-pane fade" id="calendarView" role="tabpanel">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-header">
                                <h5>Calendar Legend</h5>
                            </div>
                            <div class="card-body">
                                <div><span class="badge bg-success">&nbsp;</span> Academic Events</div>
                                <div><span class="badge bg-primary">&nbsp;</span> Social Events</div>
                                <div><span class="badge bg-info">&nbsp;</span> Career Events</div>
                                <div><span class="badge bg-warning">&nbsp;</span> Cultural Events</div>
                                <hr>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" id="showAcademic" checked>
                                    <label class="form-check-label" for="showAcademic">Show Academic</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" id="showSocial" checked>
                                    <label class="form-check-label" for="showSocial">Show Social</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" id="showCareer" checked>
                                    <label class="form-check-label" for="showCareer">Show Career</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" id="showCultural" checked>
                                    <label class="form-check-label" for="showCultural">Show Cultural</label>
                                </div>
                                <hr>
                                <h6>Venue Availability</h6>
                                <div class="mb-3">
                                    <select class="form-select" id="venueFilter">
                                        <option value="">All Venues</option>
                                        <option value="Main Hall">Main Hall</option>
                                        <option value="Student Center">Student Center</option>
                                        <option value="Conference Room A">Conference Room A</option>
                                        <option value="Conference Room B">Conference Room B</option>
                                    </select>
                                </div>
                                <button class="btn btn-primary btn-sm w-100" id="applyCalendarFilters">Apply Filters</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-9">
                        <div class="card">
                            <div class="card-body">
                                <div id="calendar"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Approval Workflow View -->
            <div class="tab-pane fade" id="approvalView" role="tabpanel">
                <div class="row">
                    <div class="col-12">
                        <h4>Pending Events for Approval (<?php echo $pendingCount; ?>)</h4>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" id="selectAllPending"></th>
                                        <th>Title</th>
                                        <th>Date & Time</th>
                                        <th>Organizer</th>
                                        <th>Venue</th>
                                        <th>Submission Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="pendingEventsTable">
                                    <?php
                                    $query = "SELECT * FROM events WHERE status = 'pending'";
                                    $result = $conn->query($query);
                                    while ($event = $result->fetch_assoc()) {
                                        $eventId = $event['event_id'] ?? '';
                                        
                                        echo "<tr data-event-id='{$eventId}'>";
                                        echo "<td><input type='checkbox' class='pending-select' data-id='{$eventId}'></td>";
                                        echo "<td>{$event['title']}</td>";
                                        echo "<td>{$event['start_datetime']} - {$event['end_datetime']}</td>";
                                        echo "<td>{$event['organizer_id']}</td>";
                                        echo "<td>{$event['location']}</td>";
                                        echo "<td>{$event['created_at']}</td>";
                                        echo "<td>
                                                <button class='btn btn-sm btn-info' onclick='viewPendingEvent({$eventId})'>Review</button>
                                                <button class='btn btn-sm btn-success' onclick='approveEvent({$eventId})'>Approve</button>
                                                <button class='btn btn-sm btn-danger' onclick='rejectEvent({$eventId})'>Reject</button>
                                              </td>";
                                        echo "</tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Create New Event Tab -->
            <div class="tab-pane fade" id="createEventView" role="tabpanel">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createEventModal">Create New Event</button>
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
                            <div class="mb-3">
                                <label for="title" class="form-label">Event Title</label>
                                <input type="text" class="form-control" id="title" name="title" required>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="start_datetime" class="form-label">Start Date & Time</label>
                                <input type="datetime-local" class="form-control" id="start_datetime" name="start_datetime" required>
                            </div>
                            <div class="mb-3">
                                <label for="end_datetime" class="form-label">End Date & Time</label>
                                <input type="datetime-local" class="form-control" id="end_datetime" name="end_datetime" required>
                            </div>
                            <div class="mb-3">
                                <label for="location" class="form-label">Location</label>
                                <input type="text" class="form-control" id="location" name="location" required>
                            </div>
                            <div class="mb-3">
                                <label for="category" class="form-label">Category</label>
                                <select class="form-control" id="category" name="category" required>
                                    <option value="">Select Category</option>
                                    <option value="academic">Academic</option>
                                    <option value="social">Social</option>
                                    <option value="cultural">Cultural</option>
                                    <option value="sports">Sports</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="max_capacity" class="form-label">Maximum Capacity</label>
                                <input type="number" class="form-control" id="max_capacity" name="max_capacity" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Create Event</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- View Event Modal -->
        <div class="modal fade" id="eventDetailModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Event Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Content will be dynamically populated -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
                        <form id="editEventForm" action="../../../actions/event_management_action.php" method="POST">
                            <!-- Form will be dynamically populated -->
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <script>
            document.getElementById('createEventModal')?.addEventListener('submit', function(e) {
                e.preventDefault();
                console.log('event Creation Submitted');
            });
            document.addEventListener("DOMContentLoaded", function () {
        const now = new Date();
        const pad = num => String(num).padStart(2, '0');

        const formattedDateTime = `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}T${pad(now.getHours())}:${pad(now.getMinutes())}`;

        const startInput = document.querySelector('input[name="start_datetime"]');
        const endInput = document.querySelector('input[name="end_datetime"]');

        startInput.min = formattedDateTime;
        endInput.min = formattedDateTime;

        startInput.value = formattedDateTime;

        // Add 1 hour for end time by default
        const oneHourLater = new Date(now.getTime() + 60 * 60 * 1000);
        const formattedEndDateTime = `${oneHourLater.getFullYear()}-${pad(oneHourLater.getMonth() + 1)}-${pad(oneHourLater.getDate())}T${pad(oneHourLater.getHours())}:${pad(oneHourLater.getMinutes())}`;
        endInput.value = formattedEndDateTime;
    });

    document.getElementById('eventCreationForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('action', 'create'); // Add action parameter
    
    fetch('../../../actions/event_management_action.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Event created successfully!');
            location.reload(); // Refresh the page to show the new event
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    });
});

// Function to load approval queue (you'll need to implement this)
function loadApprovalQueue() {
    // Implement AJAX call to load pending events
    console.log('Loading approval queue...');
    // This would update the #pendingEventsTable content
}

function viewEventDetails(eventId) {
    // Fetch event details and show in modal
    fetch(`../../../actions/event_management_action.php?action=view&event_id=${eventId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Populate modal with event details
                document.getElementById('eventDetailModal').querySelector('.modal-body').innerHTML = `
                    <div class="container">
                        <h4>${data.event.title}</h4>
                        <p><strong>Description:</strong> ${data.event.description}</p>
                        <p><strong>Date & Time:</strong> ${data.event.start_datetime} - ${data.event.end_datetime}</p>
                        <p><strong>Location:</strong> ${data.event.location}</p>
                        <p><strong>Category:</strong> ${data.event.category}</p>
                        <p><strong>Status:</strong> ${data.event.status}</p>
                    </div>
                `;
                // Show the modal
                new bootstrap.Modal(document.getElementById('eventDetailModal')).show();
            } else {
                alert('Error loading event details: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error: ' + error.message);
        });
}

function editEvent(eventId) {
    // Fetch event details and populate edit form
    fetch(`../../../actions/event_management_action.php?action=view&event_id=${eventId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Populate edit form
                document.getElementById('editEventForm').innerHTML = `
                    <input type="hidden" name="event_id" value="${eventId}">
                    <input type="hidden" name="action" value="update">
                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" class="form-control" name="title" value="${data.event.title}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" required>${data.event.description}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Start Date & Time</label>
                        <input type="datetime-local" class="form-control" name="start_datetime" value="${data.event.start_datetime}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">End Date & Time</label>
                        <input type="datetime-local" class="form-control" name="end_datetime" value="${data.event.end_datetime}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Location</label>
                        <input type="text" class="form-control" name="location" value="${data.event.location}" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Event</button>
                `;
                // Show edit modal
                new bootstrap.Modal(document.getElementById('editEventModal')).show();
            } else {
                alert('Error loading event details: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error: ' + error.message);
        });
}

function deleteEvent(eventId) {
    if (confirm('Are you sure you want to delete this event? This action cannot be undone.')) {
        // Add console.log for debugging
        console.log('Deleting event:', eventId);
        
        fetch('../../../actions/event_management_action.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=delete&event_id=' + eventId
        })
        .then(response => {
            // Add console.log for debugging
            console.log('Response:', response);
            return response.json();
        })
        .then(data => {
            // Add console.log for debugging
            console.log('Data:', data);
            
            if (data.success) {
                alert('Event deleted successfully');
                // Remove the row from the table
                const row = document.querySelector(`tr[data-event-id="${eventId}"]`);
                if (row) {
                    row.remove();
                } else {
                    location.reload(); // Fallback: reload the page
                }
            } else {
                alert('Error deleting event: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error: ' + error.message);
        });
    }
}

function viewPendingEvent(eventId) {
    fetch(`../../../actions/event_management_action.php?action=view&event_id=${eventId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Populate modal with event details
                document.getElementById('eventDetailModal').querySelector('.modal-body').innerHTML = `
                    <div class="container">
                        <h4>${data.event.title}</h4>
                        <p><strong>Description:</strong> ${data.event.description}</p>
                        <p><strong>Date & Time:</strong> ${data.event.start_datetime} - ${data.event.end_datetime}</p>
                        <p><strong>Location:</strong> ${data.event.location}</p>
                        <p><strong>Category:</strong> ${data.event.category}</p>
                        <p><strong>Organization:</strong> ${data.event.organizer_id}</p>
                    </div>
                `;
                // Show the modal
                new bootstrap.Modal(document.getElementById('eventDetailModal')).show();
            } else {
                alert('Error loading event details: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error: ' + error.message);
        });
}

function approveEvent(eventId) {
    if (confirm('Are you sure you want to approve this event?')) {
        fetch('../../../actions/event_management_action.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=approve&event_id=${eventId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Event approved successfully');
                // Remove the row from the pending queue
                const row = document.querySelector(`tr[data-event-id="${eventId}"]`);
                if (row) {
                    row.remove();
                } else {
                    location.reload(); // Fallback: reload the page
                }
            } else {
                alert('Error approving event: ' + data.message);
                console.error('Error details:', data);
            }
        })
        .catch(error => {
            alert('Error: ' + error.message);
            console.error('Error details:', error);
        });
    }
}

function rejectEvent(eventId) {
    if (confirm('Are you sure you want to reject this event?')) {
        fetch('../../../actions/event_management_action.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=reject&event_id=${eventId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Event rejected successfully');
                // Remove the row from the pending queue
                const row = document.querySelector(`tr[data-event-id="${eventId}"]`);
                if (row) {
                    row.remove();
                } else {
                    location.reload(); // Fallback: reload the page
                }
            } else {
                alert('Error rejecting event: ' + data.message);
                console.error('Error details:', data);
            }
        })
        .catch(error => {
            alert('Error: ' + error.message);
            console.error('Error details:', error);
        });
    }
}
        </script>
        
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    </body>
        </html>