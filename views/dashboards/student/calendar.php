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
$stmt = $conn->prepare("SELECT first_name FROM users WHERE user_id = ? AND role = 'student'");
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
        .logo-container {
            padding: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
        }
        .logo-container h2 {
            color: #FFFFFF !important;
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .logo-container .subtitle {
            color: #FFFFFF !important;
            opacity: 0.7;
            font-size: 14px;
            margin: 5px 0 0 0;
        }
        .sidebar {
            background: #A53838 !important;  /* Fallback to direct color */
            color: #FFFFFF !important;
            width: 250px;
            position: fixed;
            height: 100vh;
            padding-top: 0;
            box-shadow: 4px 0 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
            transition: margin-left 0.3s ease;
        }
        .sidebar-header {
            padding: 0 1.5rem;
            margin-bottom: 2rem;
            text-align: center;
        }

        .sidebar-header h3 {
            color: #FFFFFF !important;
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            font-weight: 600;
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
            padding: 0.75rem 1.5rem;
            color: #FFFFFF !important;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 1rem;
        }

        .sidebar-menu a:hover {
            background: #8B2D2D !important;
            color: #FFFFFF !important;
            padding-left: 2rem;
        }

        .sidebar-menu a.active {
            background: #D45D5D !important;
            color: #FFFFFF !important;
            border-left: 4px solid #D45D5D;
        }

        .sidebar-menu i {
            margin-right: 0.75rem;
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }

        .sidebar-menu a.active i {
            color: #D45D5D !important;
        }
        .calendar-container {
            background: var(--background-color);
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 4px var(--shadow-color);
            margin-bottom: 20px;
        }
        #calendar {
            height: calc(100vh - 200px);
        }
        .fc-event {
            cursor: pointer;
        }
        .fc-event.cultural { background-color: var(--success-color); border-color: var(--success-color); }
        .fc-event.sports { background-color: var(--danger-color); border-color: var(--danger-color); }
        .fc-event.academic { background-color: var(--accent-color); border-color: var(--accent-color); }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo-container">
            <h2>Campus Events</h2>
            <p class="subtitle">Student Dashboard</p>
        </div>
        <div class="sidebar-header">
            <h3><i class="bi bi-calendar-event"></i> Student Dashboard</h3>
            <div class="text-white-50 small">Student Dashboard</div>
        </div>
        <ul class="sidebar-menu">
            <li><a href="../../../index.php"><i class="bi bi-house-door"></i> Home</a></li>
            <li><a href="./student_dashboard.php" ><i class="bi bi-speedometer2"></i> Dashboard</a></li>
            <li><a href="./events.php"><i class="bi bi-calendar-event"></i> Events</a></li>
            <li><a href="./registrations.php"><i class="bi bi-journal-check"></i> My Registrations Events</a></li>
            <li><a href="./favorites.php"><i class="bi bi-star"></i> My Favorites Events</a></li>
            <li><a href="../../../views/logout.php"><i class="bi bi-box-arrow-right"></i> <span>Logout</span></a></li>
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