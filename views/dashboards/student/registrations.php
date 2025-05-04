<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once __DIR__ . '/../../../db/config.php';

// Debug session state
if (!isset($_SESSION['user_id'])) {
    die("Error: You are not logged in. Please log in first.");
}

// Add console logging for debugging
echo "<script>
    console.log('Session user_id: " . $_SESSION['user_id'] . "');
    
    // Add AJAX error handling
    $(document).ajaxError(function(event, jqXHR, settings, error) {
        console.error('AJAX Error:', {
            status: jqXHR.status,
            responseText: jqXHR.responseText,
            error: error
        });
    });
</script>";

$userId = $_SESSION['user_id'];

// Get all registered events with their status
$query = "SELECT 
            e.event_id,
            e.title,
            e.location,
            DATE_FORMAT(e.start_datetime, '%M %d, %Y %h:%i %p') as start_date,
            DATE_FORMAT(e.end_datetime, '%M %d, %Y %h:%i %p') as end_date,
            r.attendance_status,
            CASE 
                WHEN e.end_datetime < NOW() THEN 'completed'
                WHEN e.start_datetime > NOW() THEN 'upcoming'
                ELSE 'ongoing'
            END as event_status,
            f.rating,
            f.comments
        FROM events e
        INNER JOIN registrations r ON e.event_id = r.event_id
        LEFT JOIN feedback f ON e.event_id = f.event_id AND f.user_id = r.user_id
        WHERE r.user_id = ?
        ORDER BY e.start_datetime DESC";

// Debug query
error_log("Executing query: " . $query);
error_log("With user_id: " . $userId);

$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}

$stmt->bind_param("i", $userId);
if (!$stmt->execute()) {
    die("Error executing statement: " . $stmt->error);
}

$result = $stmt->get_result();

// Debug result count
$rowCount = $result->num_rows;
error_log("Number of events found: " . $rowCount);


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Registrations - Campus Events</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="../../../assets/css/general-styles.css">
    <link href="../../../assets/css/student-dashboard-styles.css" rel="stylesheet">
    <style>
        .sidebar-menu .dropdown-menu {
            background-color: rgba(0, 0, 0, 0.2);
            border: none;
            padding: 0;
        }
        .sidebar-menu .dropdown-item {
            color: #fff;
            padding: 0.5rem 2rem;
        }
        .sidebar-menu .dropdown-item:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        .logout-btn {
            position: absolute;
            bottom: 20px;
            width: calc(100% - 40px);
            margin: 0 20px;
        }
        .dropdown-menu {
            background-color: #2c3e50;
            border: none;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            padding: 0.5rem 0;
            margin-top: 10px;
            min-width: 200px;
        }

        .dropdown-menu:before {
            content: '';
            position: absolute;
            top: -8px;
            left: 20px;
            border-left: 8px solid transparent;
            border-right: 8px solid transparent;
            border-bottom: 8px solid #2c3e50;
        }

        .dropdown-item {
            color: #ecf0f1;
            padding: 0.8rem 1.5rem;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            position: relative;
            display: flex;
            align-items: center;
        }

        .dropdown-item:hover {
            background-color: #34495e;
            color: #3498db;
            transform: translateX(5px);
        }

        .dropdown-item i {
            margin-right: 10px;
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }

        .dropdown-divider {
            border-top: 1px solid #34495e;
            margin: 0.5rem 0;
        }
        .logo-container {
            padding: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
        }
        .logo-container h2 {
            color: white;
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
            background-color: var(--primary-color);
            color: var(--text-light);
            width: 250px;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            padding: 1rem;
            overflow-y: auto;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
            transition: margin-left 0.3s ease;
        }
        .sidebar-header {
            padding: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 1rem;
        }

        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-menu li {
            margin-bottom: 0.5rem;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            color: var(--text-light);
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .sidebar-menu a:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .sidebar-menu a.active {
            background-color: var(--accent-color);
            color: var(--text-light);
        }

        .sidebar-menu i {
            margin-right: 0.75rem;
            width: 20px;
            text-align: center;
        }
        .rating {
            color: #ffc107;
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
        }
        .status-upcoming {
            background-color: #e3f2fd;
            color: #1976d2;
        }
        .status-ongoing {
            background-color: #fff3e0;
            color: #f57c00;
        }
        .status-completed {
            background-color: #e8f5e9;
            color: #388e3c;
        }
        .feedback-modal .rating-input {
            display: none;
        }
        .feedback-modal .rating-label {
            cursor: pointer;
            font-size: 2rem;
            color: #ddd;
            transition: color 0.2s;
        }
        .rating-input:checked ~ .rating-label {
            color: #ffc107;
        }
        .rating-label:hover,
        .rating-label:hover ~ .rating-label {
            color: #ffc107;
        }
        .feedback-comment {
            max-height: 100px;
            overflow-y: auto;
            word-wrap: break-word;
        }
        .event-card {
            border: none;
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 30px;
            background-color: #fff;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
            min-height: 400px;
        }
        .event-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.1);
        }
        .event-title {
            font-size: 1.4rem;
            font-weight: 600;
            margin-bottom: 20px;
            color: #2c3e50;
            padding-bottom: 10px;
            border-bottom: 1px solid #e9ecef;
        }
        .event-details {
            color: #6c757d;
            font-size: 0.95rem;
            margin-bottom: 20px;
            flex-grow: 1;
        }
        .event-details i {
            width: 25px;
            color: #3498db;
            font-size: 1.1rem;
        }
        .event-details div {
            margin-bottom: 12px;
            display: flex;
            align-items: center;
        }
        .status-badge {
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            display: inline-block;
            margin-top: 10px;
        }
        .status-upcoming {
            background-color: #e3f2fd;
            color: #1976d2;
        }
        .status-ongoing {
            background-color: #fff3e0;
            color: #f57c00;
        }
        .status-completed {
            background-color: #e8f5e9;
            color: #388e3c;
        }
        .feedback-btn {
            margin-top: 20px;
            padding: 10px 25px;
            font-size: 0.95rem;
            border-radius: 25px;
            transition: all 0.3s;
            background-color: #3498db;
            color: white;
            border: none;
            width: 100%;
            text-align: center;
        }
        .feedback-btn:hover {
            transform: scale(1.02);
            background-color: #2980b9;
            color: white;
        }
        .feedback-btn i {
            margin-right: 8px;
        }
        .existing-feedback {
            margin-top: 20px;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 10px;
            border-left: 4px solid #ffc107;
        }
        .rating {
            color: #ffc107;
            margin-bottom: 15px;
            font-size: 1.2rem;
        }
        .feedback-comment {
            color: #6c757d;
            font-size: 0.95rem;
            line-height: 1.6;
        }
        .modal-content {
            border-radius: 15px;
            border: none;
        }
        .modal-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
            border-radius: 15px 15px 0 0;
        }
        .rating-input {
            display: none;
        }
        .rating-inputs {
            display: flex;
            flex-direction: row-reverse;
            justify-content: center;
            margin: 20px 0;
        }
        
        
        .no-events {
            text-align: center;
            padding: 40px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
        .no-events i {
            font-size: 3rem;
            color: #6c757d;
            margin-bottom: 20px;
        }
        .event-actions {
            margin-top: auto;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .cancel-btn {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 0.9rem;
            transition: all 0.3s;
            width: 100%;
            text-align: center;
        }
        .cancel-btn:hover {
            background-color: #c82333;
            transform: scale(1.02);
        }
        .cancel-btn i {
            margin-right: 8px;
        }
        .rating-stars {
            direction: ltr;
        }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo-container">
            <h2>Campus Events</h2>
            <p class="subtitle">Student Dashboard</p>
        </div>
        <ul class="sidebar-menu">
            <li><a href="../../../index.php"><i class="bi bi-house-door"></i> Home</a></li>
            <li><a href="./student_dashboard.php" ><i class="bi bi-speedometer2"></i> Dashboard</a></li>
            <li><a href="./events.php"><i class="bi bi-calendar-event"></i> Events</a></li>
            <li><a href="./registrations.php" class="active"><i class="bi bi-journal-check"></i> My Registrations Events</a></li>
            <li><a href="./calendar.php" ><i class="bi bi-calendar3"></i> Calendar</a></li>
            <li><a href="./favorites.php"><i class="bi bi-star"></i> My Favorites Events</a></li>
            <li><a href="../../../views/logout.php"><i class="bi bi-box-arrow-right"></i> <span>Logout</span></a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <div class="row mb-4">
                <div class="col-12">
                    <div class="dashboard-card">
                        <h4>My Registered Events
                            <button class="btn btn-sm btn-outline-primary float-end refresh-events">
                                <i class="bi bi-arrow-clockwise"></i> Refresh
                            </button>
                        </h4>
                        <div class="row">
                            <?php if ($result->num_rows === 0): ?>
                                <div class="col-12">
                                    <div class="no-events">
                                        <i class="fas fa-calendar-times"></i>
                                        <h3>No Events Found</h3>
                                        <p class="text-muted">You haven't registered for any events yet.</p>
                                    </div>
                                </div>
                            <?php else: ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <div class="col-md-6 col-lg-4">
                                        <div class="event-card">
                                            <div class="event-title"><?php echo htmlspecialchars($row['title']); ?></div>
                                            <div class="event-details">
                                                <div><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($row['location']); ?></div>
                                                <div><i class="fas fa-calendar"></i> <?php echo $row['start_date']; ?></div>
                                                <div><i class="fas fa-clock"></i> <?php echo $row['end_date']; ?></div>
                                                <div>
                                                    <span class="status-badge status-<?php echo $row['event_status']; ?>">
                                                        <?php echo ucfirst($row['event_status']); ?>
                                                    </span>
                                                </div>
                                            </div>
                                            
                                            <div class="event-actions">
                                                <?php if ($row['event_status'] === 'completed'): ?>
                                                    <?php if ($row['rating']): ?>
                                                        <div class="existing-feedback">
                                                            <div class="rating">
                                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                                    <i class="fas fa-star<?php echo $i <= $row['rating'] ? '' : '-o'; ?>"></i>
                                                                <?php endfor; ?>
                                                            </div>
                                                            <div class="feedback-comment">
                                                                <?php echo htmlspecialchars($row['comments']); ?>
                                                            </div>
                                                        </div>
                                                    <?php else: ?>
                                                        <button class="btn feedback-btn" 
                                                                onclick="showFeedbackModal(<?php echo $row['event_id']; ?>, '<?php echo htmlspecialchars($row['title']); ?>')">
                                                            <i class="fas fa-comment"></i> Give Feedback
                                                        </button>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                                
                                                <?php if ($row['event_status'] !== 'completed'): ?>
                                                    <button class="btn cancel-btn" onclick="cancelRegistration(<?php echo $row['event_id']; ?>)">
                                                        <i class="fas fa-times"></i> Cancel Registration
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </div>
                        <div id="noEventsMessage" class="text-center p-4" style="display: none;">
                            <p>You haven't registered for any events yet.</p>
                            <a href="./events.php" class="btn btn-primary">Browse Events</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts - Moved to end of body -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>

    <script>
    $(document).ready(function() {
        // Add AJAX error handling
        $(document).ajaxError(function(event, jqXHR, settings, error) {
            console.error('AJAX Error:', {
                status: jqXHR.status,
                responseText: jqXHR.responseText,
                error: error
            });
        });

        // Load registered events
        function loadRegisteredEvents() {
            console.log('Loading registered events...');
            
            // Show loading spinner
            $('#registeredEventsTable').html('<tr><td colspan="8" class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></td></tr>');

            // Load registered events
            $.ajax({
                url: 'actions/registration_action.php',
                type: 'POST',
                data: {
                    action: 'getRegisteredEvents'
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'error') {
                        console.error('Server returned error:', response);
                        $('#registeredEventsTable').html(`
                            <tr>
                                <td colspan="8" class="text-center text-danger">
                                    Error: ${response.message}
                                </td>
                            </tr>
                        `);
                        $('#noEventsMessage').show();
                        return;
                    }

                    if (!response.events || response.events.length === 0) {
                        $('#registeredEventsTable').html('<tr><td colspan="8" class="text-center">You haven\'t registered for any events yet.</td></tr>');
                        $('#noEventsMessage').show();
                        return;
                    }
                    
                    // Clear existing table content
                    var tableContent = '';

                    // Add each event to the table
                    response.events.forEach(function(event) {
                        tableContent += `
                            <tr>
                                <td>${event.title || 'N/A'}</td>
                                <td>${event.category || 'N/A'}</td>
                                <td>${event.location || 'N/A'}</td>
                                <td>${event.start_date || 'N/A'}<br><small class="text-muted">${event.start_time || ''}</small></td>
                                <td>${event.end_date || 'N/A'}<br><small class="text-muted">${event.end_time || ''}</small></td>
                                <td>${event.registration_date || 'N/A'}</td>
                                <td>
                                    <span class="badge bg-${event.attendance_status === 'pending' ? 'warning' : 'success'}">
                                        ${event.attendance_status === 'pending' ? 'pending' : 'complete'}
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-danger btn-sm cancel-registration" data-event-id="${event.event_id}">
                                        Cancel Registration
                                    </button>
                                </td>
                            </tr>`;
                    });

                    $('#registeredEventsTable').html(tableContent);
                    $('#noEventsMessage').hide();
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', {
                        status: xhr.status,
                        responseText: xhr.responseText,
                        error: error
                    });
                    $('#registeredEventsTable').html(`
                        <tr>
                            <td colspan="8" class="text-center text-danger">
                                Error loading events. Please try again.
                            </td>
                        </tr>
                    `);
                }
            });
        }

        // Initial load
        loadRegisteredEvents();

        // Handle refresh button click
        $(document).on('click', '.refresh-events', function() {
            loadRegisteredEvents();
        });

        // Handle cancel registration
        $(document).on('click', '.cancel-registration', function() {
            const button = $(this);
            const eventId = button.data('event-id');
            
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, cancel it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Cancelling...');
                    
                    $.ajax({
                        url: 'actions/registration_action.php',
                        type: 'POST',
                        data: {
                            action: 'cancelRegistration',
                            event_id: eventId
                        },
                        dataType: 'json',
                        success: function(data) {
                            if (data.status === 'success') {
                                Swal.fire('Cancelled!', 'Your registration has been cancelled.', 'success')
                                    .then(() => loadRegisteredEvents());
                            } else {
                                Swal.fire('Error', data.message || 'Failed to cancel registration', 'error');
                                button.prop('disabled', false).text('Cancel Registration');
                            }
                        },
                        error: function() {
                            Swal.fire('Error', 'Failed to cancel registration. Please try again.', 'error');
                            button.prop('disabled', false).text('Cancel Registration');
                        }
                    });
                }
            });
        });
    });
    </script>

    <!-- Feedback Modal -->
    <div class="modal fade" id="feedbackModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Submit Feedback</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="feedbackForm">
                        <input type="hidden" id="eventId" name="event_id">
                        <div class="mb-3">
                            <label class="form-label">Event: <span id="eventTitle" class="fw-bold"></span></label>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Rating</label>
                            <div class="rating-inputs">
                                <?php for ($i = 5; $i >= 1; $i--): ?>
                                    <input type="radio" name="rating" id="rating<?php echo $i; ?>" value="<?php echo $i; ?>" class="rating-input" required>
                                    <label for="rating<?php echo $i; ?>" class="rating-label">★</label>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="comment" class="form-label">Comments</label>
                            <textarea class="form-control" id="comment" name="comment" rows="4" required 
                                    placeholder="Share your experience with this event..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="submitFeedback()">
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        Submit Feedback
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
    let feedbackModal;
    document.addEventListener('DOMContentLoaded', function() {
        feedbackModal = new bootstrap.Modal(document.getElementById('feedbackModal'));
    });

    function showFeedbackModal(eventId, eventTitle) {
        document.getElementById('eventId').value = eventId;
        document.getElementById('eventTitle').textContent = eventTitle;
        document.getElementById('feedbackForm').reset();
        feedbackModal.show();
    }

    function submitFeedback() {
        const form = document.getElementById('feedbackForm');
        if (!form) {
            console.error('Feedback form not found');
            return;
        }

        const submitButton = document.querySelector('#feedbackModal .btn-primary');
        const spinner = submitButton.querySelector('.spinner-border');
        
        // Get form data
        const eventId = document.getElementById('eventId').value;
        const rating = document.querySelector('input[name="rating"]:checked');
        const comment = document.getElementById('comment').value;

        // Validate form
        if (!rating) {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Please select a rating'
            });
            return;
        }

        if (!comment.trim()) {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Please enter your feedback comments'
            });
            return;
        }

        // Show loading state
        submitButton.disabled = true;
        spinner.classList.remove('d-none');
        
        // Prepare form data
        const formData = new FormData();
        formData.append('action', 'submitFeedback');
        formData.append('event_id', eventId);
        formData.append('rating', rating.value);
        formData.append('comment', comment);

        // Submit feedback
        fetch('actions/feedback_action.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(data => {
                    throw new Error(data.message || 'Network response was not ok');
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: data.message,
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    feedbackModal.hide();
                    location.reload();
                });
            } else {
                throw new Error(data.message || 'Failed to submit feedback');
            }
        })
        .catch(error => {
            console.error('Error submitting feedback:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: error.message || 'An unexpected error occurred. Please try again.'
            });
        })
        .finally(() => {
            // Reset loading state
            submitButton.disabled = false;
            spinner.classList.add('d-none');
        });
    }

    // Add event listener for form submission
    document.getElementById('feedbackForm').addEventListener('submit', function(e) {
        e.preventDefault();
        submitFeedback();
    });
    </script>

    <script>
    function cancelRegistration(eventId) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this action!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, cancel it!'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('actions/registration_action.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=cancelRegistration&event_id=${eventId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: data.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: data.message
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'An unexpected error occurred. Please try again.'
                    });
                });
            }
        });
    }
    </script>
</body>
</html>