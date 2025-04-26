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
$query = "SELECT role FROM users WHERE user_id = ?";
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
    <title>Event Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../../assets/css/general-styles.css">
    <link rel="stylesheet" href="../../../assets/css/admin-styles.css">
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
            color: rgba(255, 255, 255, 0.7);
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

        .event-card {
            background-color: var(--background-color);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .event-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px var(--shadow-color);
        }

        .event-status {
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .status-pending {
            background-color: var(--warning-color);
            color: var(--text-light);
        }

        .status-approved {
            background-color: var(--success-color);
            color: var(--text-light);
        }

        .status-rejected {
            background-color: var(--danger-color);
            color: var(--text-light);
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

        .btn-danger {
            background-color: var(--danger-color);
            border-color: var(--danger-color);
        }

        .btn-danger:hover {
            background-color: var(--danger-hover);
            border-color: var(--danger-hover);
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
            <li><a href="event_management.php" class="active">
                <i class="bi bi-calendar-event"></i> Events Management
            </a></li>
            <li><a href="user_management.php">
                <i class="bi bi-people"></i> User Management
            </a></li>
            <li><a href="analytics.php">
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
                    <h2>Event Management</h2>
                    <p class="text-muted">Manage and monitor all campus events</p>
                </div>
            </div>

            <!-- Navigation Tabs -->
            <ul class="nav nav-tabs mb-4" id="eventTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="list-tab" data-bs-toggle="tab" data-bs-target="#listView" type="button">
                        <i class="bi bi-list"></i> Event List
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="approval-tab" data-bs-toggle="tab" data-bs-target="#approvalView" type="button">
                        <i class="bi bi-check-circle"></i> Approval Queue 
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
                            <div class="d-flex justify-content-between align-items-center">
                                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createEventModal">
                                    <i class="bi bi-plus-circle"></i> Create New Event
                                </button>
                                <div class="search-container" style="width: 300px;">
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0">
                                            <i class="bi bi-search"></i>
                                        </span>
                                        <input type="text" class="form-control border-start-0" id="eventSearch" placeholder="Search events...">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Title</th>
                                            <th>Date & Time</th>
                                            <th>Category</th>
                                            <th>Location</th>
                                            <th>Organizer</th>
                                            <th>Capacity</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="eventsTableBody">
                                        <!-- Populated dynamically -->
                                    </tbody>
                                </table>
                                <div id="noResultsMessage" class="text-center py-4 d-none">
                                    <i class="bi bi-search" style="font-size: 2rem; color: #6c757d;"></i>
                                    <p class="mt-2 text-muted">No events found matching your search</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Approval Queue View -->
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
                                        $query = "SELECT e.*, u.first_name, u.last_name 
                                                 FROM events e 
                                                 JOIN users u ON e.organizer_id = u.user_id 
                                                 WHERE e.status = 'pending' 
                                                 ORDER BY e.created_at DESC";
                                        $result = $conn->query($query);

                                        if ($result && $result->num_rows > 0) {
                                            while ($event = $result->fetch_assoc()) {
                                                ?>
                                                <tr>
                                                    <td><input type="checkbox" class="pending-checkbox" value="<?php echo $event['event_id']; ?>"></td>
                                                    <td><?php echo htmlspecialchars($event['title']); ?></td>
                                                    <td><?php echo date('M d, Y H:i', strtotime($event['start_datetime'])); ?></td>
                                                    <td><?php echo htmlspecialchars($event['first_name'] . ' ' . $event['last_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($event['location']); ?></td>
                                                    <td><?php echo date('M d, Y', strtotime($event['created_at'])); ?></td>
                                                    <td>
                                                        <button class="btn btn-sm btn-info" onclick="viewPendingEvent(<?php echo $event['event_id']; ?>)">
                                                            <i class="bi bi-eye"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-success" onclick="approveEvent(<?php echo $event['event_id']; ?>)">
                                                            <i class="bi bi-check-circle"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-danger" onclick="rejectEvent(<?php echo $event['event_id']; ?>)">
                                                            <i class="bi bi-x-circle"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                                <?php
                                            }
                                        } else {
                                            ?>
                                            <tr>
                                                <td colspan="7" class="text-center">No pending events</td>
                                            </tr>
                                            <?php
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Event Creation Modal -->
            <div class="modal fade" id="createEventModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Create New Event</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form id="eventCreationForm">
                                <input type="hidden" name="action" value="create">
                                <div class="mb-3">
                                    <label class="form-label">Title</label>
                                    <input type="text" class="form-control" name="title" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control" name="description" required></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Start Date & Time</label>
                                    <input type="datetime-local" class="form-control" name="start_datetime" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">End Date & Time</label>
                                    <input type="datetime-local" class="form-control" name="end_datetime" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Location</label>
                                    <input type="text" class="form-control" name="location" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Category</label>
                                    <select class="form-select" name="category" required>
                                        <option value="">Select Category</option>
                                        <option value="academic">Academic</option>
                                        <option value="social">Social</option>
                                        <option value="cultural">Cultural</option>
                                        <option value="sports">Sports</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Maximum Capacity</label>
                                    <input type="number" class="form-control" name="max_capacity" required>
                                </div>
                                <button type="submit" class="btn btn-primary">Create Event</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Function to load events and display them in the table
        document.addEventListener("DOMContentLoaded", loadEvents);

        function loadEvents() {
            fetch("../../../actions/event_management_action.php")
                .then(res => res.json())
                .then(data => {
                    const tbody = document.getElementById("eventsTableBody");
                    const noResultsMessage = document.getElementById("noResultsMessage");
                    
                    if (data.length === 0) {
                        tbody.innerHTML = "";
                        noResultsMessage.classList.remove("d-none");
                        return;
                    }
                    
                    tbody.innerHTML = "";
                    noResultsMessage.classList.add("d-none");
                    
                    data.forEach(event => {
                        tbody.innerHTML += `
                            <tr>
                                <td>${event.title}</td>
                                <td>${new Date(event.start_datetime).toLocaleString()}</td>
                                <td>${event.category}</td>
                                <td>${event.location}</td>
                                <td>${event.first_name} ${event.last_name}</td>
                                <td>${event.max_capacity}</td>
                                <td>
                                    <span class="badge bg-${getStatusBadgeClass(event.status)}">
                                        ${event.status}
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-info" onclick="viewEvent(${event.event_id})">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-primary" onclick="editEvent(${event.event_id})">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteEvent(${event.event_id})">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                })
                .catch(error => {
                    console.error('Error loading events:', error);
                    alert('An error occurred while loading events. Please try again.');
                });
        }

        // Function to get status badge class
        function getStatusBadgeClass(status) {
            switch(status.toLowerCase()) {
                case 'pending':
                    return 'warning';
                case 'approved':
                    return 'success';
                case 'rejected':
                    return 'danger';
                default:
                    return 'secondary';
            }
        }

        // Real-time search functionality
        document.getElementById('eventSearch').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('#eventsTableBody tr');
            const noResultsMessage = document.getElementById("noResultsMessage");
            let hasVisibleRows = false;
            
            rows.forEach(row => {
                const title = row.cells[0].textContent.toLowerCase();
                const category = row.cells[2].textContent.toLowerCase();
                const location = row.cells[3].textContent.toLowerCase();
                const organizer = row.cells[4].textContent.toLowerCase();
                
                if (title.includes(searchTerm) || 
                    category.includes(searchTerm) || 
                    location.includes(searchTerm) ||
                    organizer.includes(searchTerm)) {
                    row.style.display = '';
                    hasVisibleRows = true;
                } else {
                    row.style.display = 'none';
                }
            });
            
            // Show/hide no results message
            if (!hasVisibleRows) {
                noResultsMessage.classList.remove("d-none");
            } else {
                noResultsMessage.classList.add("d-none");
            }
        });

        // Add some CSS for the search bar and table
        const style = document.createElement('style');
        style.textContent = `
            .search-container {
                position: relative;
            }
            .search-container .input-group {
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                border-radius: 4px;
            }
            .search-container .input-group-text {
                border-right: none;
                color: #6c757d;
            }
            .search-container .form-control {
                border-left: none;
                padding-left: 0;
            }
            .search-container .form-control:focus {
                box-shadow: none;
                border-color: #ced4da;
            }
            #eventSearch {
                transition: all 0.3s ease;
            }
            #eventSearch:focus {
                width: 350px;
            }
            .table {
                margin-top: 1rem;
            }
            .table th {
                background-color: #f8f9fa;
                border-bottom: 2px solid #dee2e6;
            }
            .table td {
                vertical-align: middle;
            }
            .btn-sm {
                padding: 0.25rem 0.5rem;
                font-size: 0.875rem;
                margin: 0 2px;
            }
            .badge {
                font-size: 0.875rem;
                padding: 0.35em 0.65em;
            }
            #noResultsMessage {
                background-color: #f8f9fa;
                border-radius: 4px;
                margin-top: 1rem;
            }
        `;
        document.head.appendChild(style);

        // Event creation form submission
        document.getElementById('eventCreationForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get form data
            const formData = new FormData(this);
            
            // Add required parameters
            formData.append('organizer_id', '<?php echo $_SESSION['user_id']; ?>');
            
            // Send the request
            fetch('../../../actions/event_management_action.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Event created successfully!');
                    // Close the modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('createEventModal'));
                    modal.hide();
                    // Refresh the page to show the new event
                    window.location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while creating the event. Please try again.');
            });
        });

        // View event function
        function viewEvent(eventId) {
            window.location.href = `event_details.php?event_id=${eventId}`;
        }

        // Edit event function
        function editEvent(eventId) {
            window.location.href = `edit_event.php?event_id=${eventId}`;
        }

        // Delete event function
        function deleteEvent(eventId) {
            if (confirm('Are you sure you want to delete this event?')) {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('event_id', eventId);

                fetch('../../../actions/event_management_action.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Event deleted successfully!');
                        window.location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting the event. Please try again.');
                });
            }
        }

        // View pending event function
        function viewPendingEvent(eventId) {
            window.location.href = `event_details.php?event_id=${eventId}`;
        }

        // Approve event function
        function approveEvent(eventId) {
            if (confirm('Are you sure you want to approve this event?')) {
                const formData = new FormData();
                formData.append('action', 'approve');
                formData.append('event_id', eventId);

                fetch('../../../actions/event_management_action.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Event approved successfully!');
                        window.location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while approving the event. Please try again.');
                });
            }
        }

        // Reject event function
        function rejectEvent(eventId) {
            if (confirm('Are you sure you want to reject this event?')) {
                const formData = new FormData();
                formData.append('action', 'reject');
                formData.append('event_id', eventId);

                fetch('../../../actions/event_management_action.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Event rejected successfully!');
                        window.location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while rejecting the event. Please try again.');
                });
            }
        }
    </script>
</body>
</html>