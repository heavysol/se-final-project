<?php
session_start();
require_once '../../../db/config.php';  // Add this line to include the database connection

// Check if connection is successful
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Verify user is logged in and is an organizer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'organizer') {
    header("Location: ../../../login.php");
    exit();
}

?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Organizer Dashboard - Campus Events</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../../assets/css/general-styles.css">
    <link rel="stylesheet" href="../../../assets/css/dashboard-styles.css">
    <!-- Add jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
<!-- Updated Sidebar HTML -->
<div class="sidebar">
    <div class="logo-container">
        <h3>Campus Events</h3>
    </div>
    <ul class="nav-list">
        <li>
            <a href="organizer_dashboard.php" class="active">
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
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2>Organizer Dashboard</h2>
            <p class="text-muted">Welcome, <?php echo isset($_SESSION['first_name']) ? $_SESSION['first_name'] : 'Organizer'; ?>!</p>
        </div>
        <div class="col-md-6 text-end">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createEventModal"><i class="bi bi-plus"></i> Create New Event</button>
        </div>
    </div>

    <!-- Welcome message section -->
   

    <!-- Event Creation Modal -->
    <div class="modal fade" id="createEventModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create New Event</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="eventCreationForm" method="post">
                        <div class="mb-3">
                            <label for="title" class="form-label">Event Title</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="start_datetime" class="form-label">Date Event Post</label>
                            <input type="datetime-local" class="form-control" id="start_datetime" name="start_datetime" required readonly>
                        </div>
                        <div class="mb-3">
                            <label for="end_datetime" class="form-label">Date Event is Happening</label>
                            <input type="datetime-local" class="form-control" id="end_datetime" name="end_datetime" required>
                            <small class="text-muted">Event must be at least 1 hour and 30 minutes after post time</small>
                        </div>
                        <div class="mb-3">
                            <label for="location" class="form-label">Location</label>
                            <input type="text" class="form-control" id="location" name="location" required>
                        </div>
                        <div class="mb-3">
                            <label for="category" class="form-label">Category</label>
                            <select class="form-control" id="category" name="category" required>
                                <option value="">Select a category</option>
                                <option value="Academic">Academic</option>
                                <option value="Cultural">Cultural</option>
                                <option value="Social">Social</option>
                                <option value="Sports">Sports</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="max_capacity" class="form-label">Max Capacity</label>
                            <input type="number" class="form-control" id="max_capacity" name="max_capacity" min="1" required>
                        </div>
                        <div class="alert alert-danger" id="formError" style="display: none;"></div>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php
    // Add this code after your session checks and database connection

    // Get current date for comparison
    $current_date = date('Y-m-d H:i:s');

    // 1. Active Events Count (events by this organizer that haven't happened yet and are approved)
    $active_query = "SELECT COUNT(*) as active_count 
                     FROM Events 
                     WHERE organizer_id = ? 
                     AND status = 'approved' 
                     AND end_datetime > ?";
    $stmt = $conn->prepare($active_query);
    $stmt->bind_param("is", $_SESSION['user_id'], $current_date);
    $stmt->execute();
    $active_result = $stmt->get_result();
    $active_events = $active_result->fetch_assoc()['active_count'];

    // 2. Total Registrees (sum of all registrations for this organizer's events)
    $attendees_query = "SELECT COUNT(r.registration_id) as total_attendees
                       FROM Events e
                       LEFT JOIN Registrations r ON e.event_id = r.event_id
                       WHERE e.organizer_id = ?";
    $stmt = $conn->prepare($attendees_query);
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $attendees_result = $stmt->get_result();
    $total_attendees = $attendees_result->fetch_assoc()['total_attendees'];

    // 3. Pending Events Count
    $pending_query = "SELECT COUNT(*) as pending_count 
                     FROM events 
                     WHERE organizer_id = ? 
                     AND status = 'pending'";
    $stmt = $conn->prepare($pending_query);
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $pending_result = $stmt->get_result();
    $pending_events = $pending_result->fetch_assoc()['pending_count'];

    // 4. Average Rating
    $rating_query = "SELECT AVG(r.rating) as avg_rating
                     FROM events e
                     LEFT JOIN feedback r ON e.event_id = r.event_id
                     WHERE e.organizer_id = ?
                     AND r.rating IS NOT NULL";
    $stmt = $conn->prepare($rating_query);
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $rating_result = $stmt->get_result();
    $avg_rating = number_format($rating_result->fetch_assoc()['avg_rating'] ?? 0, 1);

    // Get recent registrations for organizer's events
    $recent_registrations_query = "
        SELECT r.registration_id, 
               CONCAT(u.first_name, ' ', u.last_name) as name, 
               u.email, 
               e.title as event_title, 
               r.registration_date
        FROM registrations r
        JOIN users u ON r.user_id = u.user_id
        JOIN events e ON r.event_id = e.event_id
        WHERE e.organizer_id = ?
        ORDER BY r.registration_date DESC
        LIMIT 5
    ";
    $stmt = $conn->prepare($recent_registrations_query);
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $recent_registrations_result = $stmt->get_result();
    $recent_registrations = [];
    while ($row = $recent_registrations_result->fetch_assoc()) {
        $recent_registrations[] = $row;
    }
    ?>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-icon primary">
                    <i class="bi bi-calendar2-check"></i>
                </div>
                <div class="stats-info">
                    <h3 class="stats-number"><?php echo $active_events; ?></h3>
                    <p class="stats-label">Active Events</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-icon success">
                    <i class="bi bi-person-check"></i>
                </div>
                <div class="stats-info">
                    <h3 class="stats-number"><?php echo $total_attendees; ?></h3>
                    <p class="stats-label">Total Registrees</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-icon warning">
                    <i class="bi bi-clock"></i>
                </div>
                <div class="stats-info">
                    <h3 class="stats-number"><?php echo $pending_events; ?></h3>
                    <p class="stats-label">Pending Events</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-icon danger">
                    <i class="bi bi-star"></i>
                </div>
                <div class="stats-info">
                    <h3 class="stats-number"><?php echo $avg_rating; ?></h3>
                    <p class="stats-label">Average Rating</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Second row with Tasks and Notifications side by side -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="dashboard-card">
                <h4>
                    Checklist
                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addTaskModal">Add Task</button>
                </h4>
                <div class="task-list">
                    <?php
                    // Fetch tasks for the current organizer
                    $tasks_query = "SELECT * FROM tasks WHERE organizer_id = ? ORDER BY due_date ASC";
                    $stmt = $conn->prepare($tasks_query);
                    if ($stmt) {
                        $stmt->bind_param("i", $_SESSION['user_id']);
                        $stmt->execute();
                        $tasks_result = $stmt->get_result();

                        if ($tasks_result->num_rows > 0) {
                            while ($task = $tasks_result->fetch_assoc()) {
                                $status_class = $task['status'] === 'completed' ? 'completed' : '';
                                $checked = $task['status'] === 'completed' ? 'checked' : '';
                                $due_date = $task['due_date'] ? date('M d', strtotime($task['due_date'])) : 'No due date';
                                echo "<div class='task-item {$status_class}' data-task-id='{$task['task_id']}'>
                                        <div class='task-checkbox'>
                                            <input type='checkbox' class='form-check-input task-status' {$checked}>
                                        </div>
                                        <div class='task-title'>{$task['title']}</div>
                                        <div class='task-date'>{$due_date}</div>
                                        <button class='btn btn-sm btn-danger delete-task' data-task-id='{$task['task_id']}'>
                                            <i class='fas fa-trash'></i>
                                        </button>
                                    </div>";
                            }
                        } else {
                            echo "<div class='text-center text-muted'>No tasks yet</div>";
                        }
                    } else {
                        echo "<div class='text-center text-danger'>Error loading tasks</div>";
                        error_log("Error preparing tasks query: " . $conn->error);
                    }
                    ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="dashboard-card">
                <h4>
                    Recent Notifications
                    <?php
                    // Count unread notifications
                    $notifications_count_query = "SELECT COUNT(*) as unread_count FROM notifications 
                                                WHERE organizer_id = ? AND is_read = 0";
                    $stmt = $conn->prepare($notifications_count_query);
                    if ($stmt) {
                        $stmt->bind_param("i", $_SESSION['user_id']);
                        $stmt->execute();
                        $notifications_count = $stmt->get_result()->fetch_assoc()['unread_count'];
                        
                        if ($notifications_count > 0) {
                            echo "<span class='badge badge-custom'>{$notifications_count} New</span>";
                        }
                    } else {
                        error_log("Error preparing notifications count query: " . $conn->error);
                    }
                    ?>
                </h4>
                <div class="list-group">
                    <?php
                    // Fetch recent notifications
                    $notifications_query = "SELECT n.*, e.title as event_title 
                                          FROM notifications n 
                                          LEFT JOIN events e ON n.event_id = e.event_id 
                                          WHERE n.organizer_id = ? 
                                          ORDER BY n.created_at DESC 
                                          LIMIT 5";
                    $stmt = $conn->prepare($notifications_query);
                    if ($stmt) {
                        $stmt->bind_param("i", $_SESSION['user_id']);
                        $stmt->execute();
                        $notifications_result = $stmt->get_result();

                        if ($notifications_result->num_rows > 0) {
                            while ($notification = $notifications_result->fetch_assoc()) {
                                $is_read_class = $notification['is_read'] ? '' : 'unread';
                                
                                echo "<a href='#' class='list-group-item list-group-item-action {$is_read_class}' data-notification-id='{$notification['notification_id']}'>
                                        <div class='d-flex w-100 justify-content-between'>
                                            <h6 class='mb-1'>{$notification['title']}</h6>
                                        </div>
                                        <p class='mb-1'>{$notification['message']}</p>
                                    </a>";
                            }
                        } else {
                            echo "<div class='text-center text-muted'>No notifications yet</div>";
                        }
                    } else {
                        echo "<div class='text-center text-danger'>Error loading notifications</div>";
                        error_log("Error preparing notifications query: " . $conn->error);
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Third row with full-width Recent Registrees -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="dashboard-card">
                <h4>
                    Recent Registrees
                    <a href="registrations.php" class="btn btn-sm btn-outline-primary">View All</a>
                </h4>
                <div class="attendee-list">
                    <?php
                    if (empty($recent_registrations)) {
                        echo '<div class="text-center text-muted">No recent registrations</div>';
                    } else {
                        foreach ($recent_registrations as $registration) {
                            echo '<div class="attendee-item">
                                    <div class="attendee-avatar">
                                        <i class="bi bi-person"></i>
                                    </div>
                                    <div class="attendee-info">
                                        <h6 class="attendee-name">' . htmlspecialchars($registration['name']) . '</h6>
                                        <p class="attendee-email">' . htmlspecialchars($registration['email']) . '</p>
                                    </div>
                                    <div class="attendee-event">
                                        <span class="badge bg-light text-dark">' . htmlspecialchars($registration['event_title']) . '</span>
                                        <small class="text-muted d-block">' . date('M d, Y', strtotime($registration['registration_date'])) . '</small>
                                    </div>
                                </div>';
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Task Modal -->
    <div class="modal fade" id="addTaskModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Task</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addTaskForm">
                        <div class="mb-3">
                            <label for="taskTitle" class="form-label">Task Title *</label>
                            <input type="text" class="form-control" id="taskTitle" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="taskDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="taskDescription" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="taskDueDate" class="form-label">Due Date</label>
                            <input type="datetime-local" class="form-control" id="taskDueDate" name="due_date">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="saveTask">Save Task</button>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<!-- Scripts -->
<script>
document.getElementById('eventCreationForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    // Show loading state
    const submitButton = this.querySelector('button[type="submit"]');
    submitButton.disabled = true;
    submitButton.textContent = 'Creating...';
    
    try {
        // Collect form data
        const formData = new FormData(this);
        formData.append('action', 'create');
        
        // Debug: Log form data
        for (let [key, value] of formData.entries()) {
            console.log(`${key}: ${value}`);
        }

        // Make the request
        const response = await fetch('../../../actions/organizer_action.php', {
            method: 'POST',
            body: formData
        });

        // Debug: Log response details
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);

        // Get the raw text first
        const responseText = await response.text();
        console.log('Raw response:', responseText);

        // Try to parse as JSON
        let data;
        try {
            data = JSON.parse(responseText);
        } catch (parseError) {
            console.error('Failed to parse JSON:', parseError);
            throw new Error('Server returned invalid JSON response');
        }

        if (!data) {
            throw new Error('Empty response received');
        }

        if (data.success) {
            alert('Event created successfully!');
            // Close modal and refresh
            document.querySelector('#createEventModal .btn-close').click();
            location.reload();
        } else {
            throw new Error(data.message || 'Failed to create event');
        }

    } catch (error) {
        console.error('Error:', error);
        alert('Error: ' + error.message);
    } finally {
        // Reset button state
        submitButton.disabled = false;
        submitButton.textContent = 'Submit';
    }
});

// Function to set current date and time for post date
function setCurrentDateTime() {
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    
    // Set start datetime to current time
    const startInput = document.getElementById('start_datetime');
    startInput.value = `${year}-${month}-${day}T${hours}:${minutes}`;
    
    // Set minimum end datetime
    const minEndDate = new Date(now.getTime() + (90 * 60000)); // 90 minutes in milliseconds
    const endInput = document.getElementById('end_datetime');
    endInput.min = `${year}-${month}-${day}T${hours}:${minutes}`;
    
    // Clear any previous end datetime value when modal opens
    endInput.value = '';
}

// Validate end datetime when it changes
document.getElementById('end_datetime').addEventListener('change', function() {
    const startDate = new Date(document.getElementById('start_datetime').value);
    const endDate = new Date(this.value);
    const minEndDate = new Date(startDate.getTime() + (90 * 60000)); // 90 minutes in milliseconds
    
    if (endDate < startDate) {
        this.setCustomValidity('Event happening time cannot be before post time');
    } else if (endDate < minEndDate) {
        this.setCustomValidity('Event must be at least 1 hour and 30 minutes after post time');
    } else {
        this.setCustomValidity('');
    }
});

// Set current date/time when modal opens
document.getElementById('createEventModal').addEventListener('show.bs.modal', function () {
    setCurrentDateTime();
});

// Add functions for edit and delete
async function editEvent(eventId) {
    // Fetch event details and populate form
    const formData = new FormData();
    formData.append('action', 'edit');
    formData.append('event_id', eventId);
    // Add other form data...
    
    try {
        const response = await fetch('../../../actions/organizer_action.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert(data.message || 'Failed to update event');
        }
    } catch (error) {
        alert('An error occurred while updating the event');
    }
}

async function deleteEvent(eventId) {
    if (!confirm('Are you sure you want to delete this event?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('event_id', eventId);
    
    try {
        const response = await fetch('../../../actions/organizer_action.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert(data.message || 'Failed to delete event');
        }
    } catch (error) {
        alert('An error occurred while deleting the event');
    }
}

// Task Management
$(document).ready(function() {
    // Add new task
    $('#saveTask').click(function(e) {
        e.preventDefault();
        
        // Get form data
        const title = $('#taskTitle').val().trim();
        const description = $('#taskDescription').val().trim();
        const dueDate = $('#taskDueDate').val();
        
        // Validate form
        if (!title) {
            alert('Please enter a task title');
            return;
        }

        // Create form data
        const formData = new FormData();
        formData.append('action', 'add');
        formData.append('title', title);
        formData.append('description', description);
        if (dueDate) {
            formData.append('due_date', dueDate);
        }

        // Show loading state
        const saveButton = $('#saveTask');
        saveButton.prop('disabled', true)
                 .html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...');

        // Make AJAX request
        $.ajax({
            url: '../../../actions/task_action.php',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                console.log('Server response:', response);
                if (response.success) {
                    // Show success message
                    alert('Task added successfully!');
                    // Close modal and refresh page
                    $('#addTaskModal').modal('hide');
                    location.reload();
                } else {
                    alert('Error adding task: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', { xhr, status, error });
                alert('Error adding task. Please check the console for details.');
            },
            complete: function() {
                // Reset button state
                saveButton.prop('disabled', false).html('Save Task');
            }
        });
    });

    // Clear form when modal is closed
    $('#addTaskModal').on('hidden.bs.modal', function () {
        $('#addTaskForm')[0].reset();
    });

    // Update task status
    $('.task-status').change(function() {
        const taskId = $(this).closest('.task-item').data('task-id');
        const isCompleted = $(this).is(':checked');
        
        console.log('Updating task status:', { taskId, isCompleted });
        
        $.ajax({
            url: '../../../actions/task_action.php',
            method: 'POST',
            data: {
                action: 'update_status',
                task_id: taskId,
                status: isCompleted ? 'completed' : 'pending'
            },
            success: function(response) {
                console.log('Server response:', response);
                if (response.success) {
                    location.reload();
                } else {
                    alert('Error updating task: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', { xhr, status, error });
                alert('Error updating task. Please check the console for details.');
            }
        });
    });

    // Delete task
    $('.delete-task').click(function() {
        if (confirm('Are you sure you want to delete this task?')) {
            const taskId = $(this).data('task-id');
            
            console.log('Deleting task:', taskId);
            
            $.ajax({
                url: '../../../actions/task_action.php',
                method: 'POST',
                data: {
                    action: 'delete',
                    task_id: taskId
                },
                success: function(response) {
                    console.log('Server response:', response);
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Error deleting task: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', { xhr, status, error });
                    alert('Error deleting task. Please check the console for details.');
                }
            });
        }
    });

    // Handle notification clicks
    $('.list-group-item').click(function(e) {
        e.preventDefault();
        const notificationId = $(this).data('notification-id');
        
        // Mark notification as read
        $.ajax({
            url: '../../../actions/notification_action.php',
            method: 'POST',
            data: {
                action: 'mark_read',
                notification_id: notificationId
            },
            success: function(response) {
                if (response.success) {
                    // Remove unread class and update badge
                    $(this).removeClass('unread');
                    updateUnreadCount();
                }
            }
        });
    });

    // Function to update unread count
    function updateUnreadCount() {
        $.ajax({
            url: '../../../actions/notification_action.php',
            method: 'POST',
            data: {
                action: 'get_unread_count'
            },
            success: function(response) {
                if (response.success) {
                    const badge = $('.badge-custom');
                    if (response.count > 0) {
                        badge.text(response.count + ' New');
                        badge.show();
                    } else {
                        badge.hide();
                    }
                }
            }
        });
    }

    // Update unread count every 30 seconds
    setInterval(updateUnreadCount, 30000);
});
</script>
</body>
</html>