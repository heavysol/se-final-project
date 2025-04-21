<?php
session_start();

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header('Location: ../../../login.php');
    exit();
}

require_once '../../../db/config.php';

// Fetch user details
$userId = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT first_name FROM Users WHERE user_id = ? AND role = 'student'");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    session_destroy();
    header('Location: ../../../login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Calendar - Campus Events</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css">
    <link rel="stylesheet" href="../../../assets/css/general-styles.css">
    <link rel="stylesheet" href="../../../assets/css/dashboard-styles.css">
    <style>
        .calendar-container {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        #calendar {
            height: calc(100vh - 200px);
        }
        .fc-event {
            cursor: pointer;
        }
        .fc-event.cultural { background-color: #28a745; border-color: #28a745; }
        .fc-event.sports { background-color: #dc3545; border-color: #dc3545; }
        .fc-event.academic { background-color: #17a2b8; border-color: #17a2b8; }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h3><i class="bi bi-calendar-event"></i> Student Dashboard</h3>
            <div class="text-white-50 small">Student Dashboard</div>
        </div>
        <ul class="sidebar-menu">
            <li><a href="./student_dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
            <li><a href="./events.php"><i class="bi bi-calendar-event"></i> Events</a></li>
            <li><a href="./registrations.php"><i class="bi bi-journal-check"></i> My Registrations Event</a></li>
            <li><a href="./favourites.php"><i class="bi bi-star"></i> My Favorites Events</a></li>
            <li><a href="./calendar.php" class="active"><i class="bi bi-calendar3"></i> Calendar</a></li>

        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <div class="row mb-4">
                <div class="col-12">
                    <h1>My Calendar</h1>
                    <p class="text-muted">View and manage your registered and saved events</p>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="calendar-container">
                        <div id="calendar"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Event Details Modal -->
    <div class="modal fade" id="eventModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Event Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Event details will be populated here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="eventActionBtn">Register</button>
                    <button type="button" class="btn btn-outline-primary" id="calendarActionBtn">Add to Calendar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,listWeek'
                },
                events: 'calendar_events.php',
                eventClick: function(info) {
                    showEventDetails(info.event.id);
                },
                eventDidMount: function(info) {
                    // Add category class to event
                    if (info.event.extendedProps.category) {
                        info.el.classList.add(info.event.extendedProps.category.toLowerCase());
                    }
                }
            });
            calendar.render();

            // Function to show event details
            function showEventDetails(eventId) {
                $.ajax({
                    url: 'events_action.php',
                    method: 'GET',
                    data: { action: 'getEventDetails', event_id: eventId },
                    success: function(response) {
                        if (response.status === 'success' && response.event) {
                            const event = response.event;
                            const modalBody = $('#eventModal .modal-body');
                            modalBody.html(`
                                <p><strong>Date:</strong> ${new Date(event.start_datetime).toLocaleString()}</p>
                                <p><strong>Location:</strong> ${event.location}</p>
                                <p><strong>Category:</strong> ${event.category}</p>
                                <p><strong>Description:</strong> ${event.description}</p>
                                <p><strong>Capacity:</strong> ${event.current_registrations}/${event.max_capacity}</p>
                            `);

                            // Update action buttons
                            const eventActionBtn = $('#eventActionBtn');
                            const calendarActionBtn = $('#calendarActionBtn');

                            if (event.is_registered) {
                                eventActionBtn.text('Unregister')
                                    .removeClass('btn-primary')
                                    .addClass('btn-danger');
                            } else {
                                eventActionBtn.text('Register')
                                    .removeClass('btn-danger')
                                    .addClass('btn-primary');
                            }

                            if (event.in_calendar) {
                                calendarActionBtn.text('Remove from Calendar')
                                    .removeClass('btn-outline-primary')
                                    .addClass('btn-outline-danger');
                            } else {
                                calendarActionBtn.text('Add to Calendar')
                                    .removeClass('btn-outline-danger')
                                    .addClass('btn-outline-primary');
                            }

                            // Store event ID on buttons
                            eventActionBtn.data('event-id', event.event_id);
                            calendarActionBtn.data('event-id', event.event_id);

                            // Show the modal
                            new bootstrap.Modal(document.getElementById('eventModal')).show();
                        }
                    }
                });
            }

            // Handle registration button click
            $('#eventActionBtn').click(function() {
                const eventId = $(this).data('event-id');
                const isUnregister = $(this).hasClass('btn-danger');
                
                $.ajax({
                    url: 'event_registration.php',
                    type: 'POST',
                    data: {
                        action: isUnregister ? 'unregister' : 'register',
                        event_id: eventId
                    },
                    success: function(response) {
                        const data = JSON.parse(response);
                        if (data.status === 'success') {
                            calendar.refetchEvents();
                            $('#eventModal').modal('hide');
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: data.message,
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000
                            });
                        }
                    }
                });
            });

            // Handle calendar button click
            $('#calendarActionBtn').click(function() {
                const eventId = $(this).data('event-id');
                const isRemove = $(this).hasClass('btn-outline-danger');
                
                $.ajax({
                    url: 'calendar_action.php',
                    type: 'POST',
                    data: {
                        action: isRemove ? 'remove' : 'add',
                        event_id: eventId
                    },
                    success: function(response) {
                        const data = JSON.parse(response);
                        if (data.status === 'success') {
                            calendar.refetchEvents();
                            $('#eventModal').modal('hide');
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: data.message,
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000
                            });
                        }
                    }
                });
            });
        });
    </script>
</body>
</html> 