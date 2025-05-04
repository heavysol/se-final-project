<?php
session_start();

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header('Location: ../../../login.php');
    exit();
}

// Include database configuration
require_once '../../../db/config.php';

// Fetch user details
$userId = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT first_name FROM users WHERE user_id = ? AND role = 'student'");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    // If user not found in database, destroy session and redirect
    session_destroy();
    header('Location: ../../../login.php');
    exit();
}

$_SESSION['first_name'] = $user['first_name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Campus Events</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css">
    <link rel="stylesheet" href="../../../assets/css/general-styles.css">
    <link rel="stylesheet" href="../../../assets/css/dashboard-styles.css">
    <style>
        .student-sidebar {
            background: var(--primary-color);
            color: var(--text-light);
            width: 250px;
            position: fixed;
            height: 100vh;
            padding-top: 0;
            box-shadow: 4px 0 10px var(--shadow-color);
        }
        
        .student-sidebar-header {
            padding: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
        }
        
        .student-sidebar-header h3 {
            color: var(--text-light);
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        
        .student-sidebar-header .small {
            color: rgba(255, 255, 255, 0.7);
            font-size: 14px;
            margin: 5px 0 0 0;
        }
        
        .student-sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .student-sidebar-menu li a {
            color: var(--text-light);
            text-decoration: none;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            transition: background-color 0.3s ease;
        }
        
        .student-sidebar-menu li a:hover {
            background-color: var(--hover-color);
        }
        
        .student-sidebar-menu li a.active {
            background-color: var(--active-color);
        }
        
        .student-sidebar-menu li a i {
            margin-right: 10px;
            font-size: 1.2rem;
        }
        
        .student-sidebar-menu li:last-child {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            margin-top: 20px;
        }
        .main-content {
            margin-left: 250px;
            padding: 2rem;
            min-height: 100vh;
            background: #f8f9fa;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
                padding: 1rem 0.5rem;
            }

            .sidebar-header h3 span,
            .sidebar-menu a span {
                display: none;
            }

            .sidebar-header h3 {
                justify-content: center;
            }

            .sidebar-menu a {
                justify-content: center;
                padding: 0.8rem;
            }

            .main-content {
                margin-left: 70px;
            }
        }
        .dashboard-stats {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .stat-card {
            text-align: center;
            padding: 15px;
            border-radius: 8px;
            background: #f8f9fa;
            margin-bottom: 15px;
        }
        .event-card {
            background: #fff;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            border-left: 4px solid var(--primary-color);
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            transition: transform 0.3s ease;
        }
        .event-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px var(--shadow-color);
        }
        .event-card.cultural { border-left-color: var(--success-color); }
        .event-card.sports { border-left-color: var(--danger-color); }
        .event-card.academic { border-left-color: var(--accent-color); }
        .notification {
            background-color: var(--secondary-color);
            border-left: 4px solid var(--warning-color);
            color: var(--text-dark);
        }
        .notification.warning {
            background-color: var(--secondary-color);
            border-left-color: var(--warning-color);
        }
        .notification.success {
            background-color: var(--secondary-color);
            border-left-color: var(--success-color);
        }
        .stats-card {
            background-color: var(--text-light);
            border: 1px solid var(--border-color);
            box-shadow: 0 2px 4px var(--shadow-color);
        }
        .stats-icon {
            background-color: var(--secondary-color);
            color: var(--primary-color);
        }
        .calendar-wrapper {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .quick-actions {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        .quick-action-btn {
            flex: 1;
            text-align: center;
            padding: 15px;
            border-radius: 8px;
            background: #f8f9fa;
            border: none;
            transition: all 0.3s;
        }
        .quick-action-btn:hover {
            background: #e9ecef;
            transform: translateY(-2px);
        }
        .favorite-btn {
            background: none;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            font-size: 1.1em;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            margin-left: 8px;
            background-color: #fff3cd;
            border: 1px solid #ffeeba;
        }
        .favorite-btn:hover {
            transform: scale(1.05);
            background-color: #ffe69c;
        }
        .favorite-btn .bi-plus-lg {
            color: #856404;
        }
        .favorite-btn .bi-star-fill {
            color: #ffd700;
        }
        .favorite-btn:not([data-favorite="true"]) {
            background-color: #e9ecef;
            border-color: #dee2e6;
        }
        .favorite-btn:not([data-favorite="true"]):hover {
            background-color: #dde2e6;
        }
        .favorite-btn span {
            margin-left: 4px;
            font-size: 0.9em;
        }
        .notification-card {
            transition: all 0.3s ease;
        }
        
        .notification-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .bg-light-warning {
            background-color: rgba(255, 193, 7, 0.1);
            border-left: 4px solid #ffc107;
        }
        
        .bg-light-info {
            background-color: rgba(13, 202, 240, 0.1);
            border-left: 4px solid #0dcaf0;
        }
        
        .notification-title {
            color: #333;
            font-weight: 600;
        }
        
        .notification-message {
            color: #666;
        }
        
        .notification-card .btn {
            margin-left: 10px;
        }
        .rating {
            display: flex;
            flex-direction: row-reverse;
            justify-content: flex-end;
        }
        .rating input {
            display: none;
        }
        .rating label {
            font-size: 2rem;
            color: #ddd;
            cursor: pointer;
            transition: color 0.3s;
        }
        .rating input:checked ~ label,
        .rating label:hover,
        .rating label:hover ~ label {
            color: #ffc107;
        }
        .rating input:checked + label:hover,
        .rating input:checked ~ label:hover,
        .rating label:hover ~ input:checked ~ label,
        .rating input:checked ~ label:hover ~ label {
            color: #ffc107;
        }
    </style>
</head>



<body>
    <!-- Sidebar -->
    <div class="student-sidebar">
        <div class="student-sidebar-header">
            <h3>Campus Events</h3>
            <small>Student Dashboard</small>
        </div>
        <ul class="student-sidebar-menu">
            <li><a href="../../../index.php"><i class="bi bi-house-door"></i> Home</a></li>
            <li><a href="./student_dashboard.php" ><i class="bi bi-speedometer2"></i> Dashboard</a></li>
            <li><a href="./events.php"><i class="bi bi-calendar-event"></i> Events</a></li>
            <li><a href="./registrations.php"><i class="bi bi-journal-check"></i> My Registrations Events</a></li>
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
                    <h1 class="welcome-header">Welcome, <?php echo htmlspecialchars($_SESSION['first_name']); ?>!</h1>
                    <p class="text-muted">Here's what's happening on campus this week</p>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="quick-actions">
                        <button class="quick-action-btn" onclick="window.location.href='./events.php'">
                            <i class="bi bi-search"></i><br>
                            Find Events
                        </button>
                       
                        <button class="quick-action-btn" onclick="window.location.href='./calendar.php'">
                            <i class="bi bi-calendar3"></i><br>
                            Calendar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Dashboard Stats -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="stat-card">
                        <i class="bi bi-calendar2-check text-primary fs-4"></i>
                        <h3 class="mt-2" id="registered-events-count">
                            <?php
                            // Get initial registered events count
                            $countStmt = $conn->prepare("SELECT COUNT(*) as count FROM registrations WHERE user_id = ?");
                            $countStmt->bind_param("i", $_SESSION['user_id']);
                            $countStmt->execute();
                            $registeredCount = $countStmt->get_result()->fetch_assoc()['count'];
                            echo $registeredCount;
                            ?>
                        </h3>
                        <p class="text-muted mb-0">Registered Events</p>
                    </div>
                </div>
                   
                <div class="col-md-3">
                    <div class="stat-card">
                        <i class="bi bi-star text-warning fs-4"></i>
                        <h3 class="mt-2" id="favorite-events-count">0</h3>
                        <p class="text-muted mb-0">Favorite Events</p>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Notifications -->
                <div class="col-md-8">
                    <div class="dashboard-card">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4><i class="bi bi-bell"></i> Notifications</h4>
                           
                        </div>
                        <div id="notifications-list">
                            <!-- Notifications will be loaded here -->
                        </div>
                    </div>
                </div>

                <!-- Upcoming Events -->
                <div class="col-md-4">
                    <div class="dashboard-card">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4><i class="bi bi-calendar2-week"></i> Upcoming Events</h4>
                            <a href="./events.php" class="btn btn-sm btn-primary">
                                <i class="bi bi-list"></i> View All Events
                            </a>
                        </div>
                        <div id="upcoming-events-list">
                            <?php
                            // Get upcoming events (limited to 5 by default)
                            $query = "SELECT e.*, 
                                    CASE WHEN r.registration_id IS NOT NULL THEN 1 ELSE 0 END as is_registered,
                                    CASE WHEN c.calendar_id IS NOT NULL THEN 1 ELSE 0 END as in_calendar,
                                    CASE WHEN f.favorite_id IS NOT NULL THEN 1 ELSE 0 END as is_favorite,
                                    (SELECT COUNT(*) FROM registrations WHERE event_id = e.event_id) as current_registrations
                                    FROM events e 
                                    LEFT JOIN registrations r ON e.event_id = r.event_id AND r.user_id = ?
                                    LEFT JOIN eventcalendar c ON e.event_id = c.event_id AND c.user_id = ?
                                    LEFT JOIN favorites f ON e.event_id = f.event_id AND f.user_id = ?
                                    WHERE e.end_datetime >= NOW() 
                                    AND e.status = 'approved'
                                    ORDER BY e.start_datetime ASC 
                                    LIMIT 5";
                            
                            $stmt = $conn->prepare($query);
                            $stmt->bind_param("iii", $_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id']);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            
                            if ($result->num_rows > 0) {
                                while ($event = $result->fetch_assoc()) {
                                    $startDate = new DateTime($event['start_datetime']);
                                    $endDate = new DateTime($event['end_datetime']);
                                    
                                    // Set button states
                                    $registerBtnClass = $event['is_registered'] ? 'btn-danger' : 'btn-primary register-btn';
                                    $registerBtnText = $event['is_registered'] ? 'Unregister' : 'Register';
                                    
                                    $calendarBtnClass = $event['in_calendar'] ? 'btn-outline-danger' : 'btn-outline-primary calendar-btn';
                                    $calendarBtnText = $event['in_calendar'] ? 'Remove from Calendar' : 'Add to Calendar';
                                    ?>
                                    <div class="event-card mb-3 p-3 border rounded" id="event-<?php echo $event['event_id']; ?>">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h5 class="event-title mb-2"><?php echo htmlspecialchars($event['title']); ?></h5>
                                                <div class="event-meta">
                                                    <span class="event-category"><i class="bi bi-tag"></i> <?php echo htmlspecialchars($event['category']); ?></span>
                                                    <span class="event-capacity"><i class="bi bi-people"></i> <?php echo $event['current_registrations']; ?>/<?php echo $event['max_capacity']; ?> registered</span>
                                                </div>
                                                <div class="event-details">
                                                    <p class="mb-1">
                                                        <i class="bi bi-calendar"></i> 
                                                        <?php echo $startDate->format('F j, Y'); ?> 
                                                        <span class="text-muted">at</span> 
                                                        <?php echo $startDate->format('g:i A'); ?>
                                                        <?php if ($startDate->format('Y-m-d') !== $endDate->format('Y-m-d')): ?>
                                                            - <?php echo $endDate->format('F j, Y'); ?> at <?php echo $endDate->format('g:i A'); ?>
                                                        <?php else: ?>
                                                            - <?php echo $endDate->format('g:i A'); ?>
                                                        <?php endif; ?>
                                                    </p>
                                                    <p class="mb-1"><i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($event['location']); ?></p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="event-actions mt-3">
                                            <button type="button" class="btn btn-sm <?php echo $registerBtnClass; ?>" 
                                                    data-event-id="<?php echo $event['event_id']; ?>">
                                                <?php echo $registerBtnText; ?>
                                            </button>
                                            <button type="button" class="btn btn-sm <?php echo $calendarBtnClass; ?>" 
                                                    data-event-id="<?php echo $event['event_id']; ?>">
                                                <?php echo $calendarBtnText; ?>
                                            </button>
                                            <button class="favorite-btn" onclick="toggleFavorite(this, <?php echo $event['event_id']; ?>)" 
                                                    data-event-id="<?php echo $event['event_id']; ?>"
                                                    <?php echo $event['is_favorite'] ? 'data-favorite="true"' : ''; ?>>
                                                <i class="bi <?php echo $event['is_favorite'] ? 'bi-star-fill' : 'bi-plus-lg'; ?>"></i>
                                                <span><?php echo $event['is_favorite'] ? 'Remove from Favorites' : 'Add to Favorites'; ?></span>
                                            </button>
                                        </div>
                                    </div>
                                    <?php
                                }
                            } else {
                                echo '<div class="alert alert-info">
                                        <i class="bi bi-info-circle"></i> No upcoming events found. Check back later for new events!
                                    </div>';
                            }
                            ?>
                        </div>
                    </div>
                </div>

                <!-- Calendar -->
                <div class="col-md-4">
                    <div class="calendar-wrapper">
                        <h4 class="mb-4"><i class="bi bi-calendar3"></i> My Calendar</h4>
                        <div id="calendar"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Function to animate counter - in global scope
        function animateCounter($element, start, end, duration) {
            if (typeof start !== 'number') start = 0;
            if (typeof end !== 'number') end = 0;
            
            const startTime = performance.now();
            const change = end - start;

            function step(currentTime) {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);
                
                const currentCount = Math.floor(start + (change * progress));
                $element.text(Math.max(0, currentCount));
                
                if (progress < 1) {
                    window.requestAnimationFrame(step);
                }
            }
            window.requestAnimationFrame(step);
        }

        // Function to update dashboard stats
        function updateDashboardStats() {
            $.ajax({
                url: 'get_dashboard_stats.php',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        $('#registered-events-count').text(response.registered_events);
                        $('#favorite-events-count').text(response.favorite_events);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error updating dashboard stats:', error);
                }
            });
        }

        // Update stats when page loads
        $(document).ready(function() {
            updateDashboardStats();
            
            // Update stats every 5 minutes
            setInterval(updateDashboardStats, 300000);
        });

        // Function to load upcoming events - in global scope
        function loadUpcomingEvents() {
            $.ajax({
                url: 'get_upcoming_events.php',
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    const eventsContainer = $('#upcoming-events-list');
                    eventsContainer.empty();
                    
                    if (data.events && data.events.length > 0) {
                        data.events.forEach(event => {
                            const eventHtml = `
                                <div class="event-card mb-3 p-3 border rounded" id="event-${event.id}">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h5 class="event-title mb-2">${event.title}</h5>
                                            <div class="event-meta">
                                                <span class="event-category"><i class="bi bi-tag"></i> ${event.category}</span>
                                                <span class="event-capacity"><i class="bi bi-people"></i> ${event.current_registrations}/${event.max_capacity} registered</span>
                                            </div>
                                            <div class="event-details">
                                                <p class="mb-1">
                                                    <i class="bi bi-calendar"></i> 
                                                    ${event.formatted_start_date} 
                                                    <span class="text-muted">at</span> 
                                                    ${event.formatted_start_time}
                                                    ${event.formatted_start_date !== event.formatted_end_date ? 
                                                        ` - ${event.formatted_end_date} at ${event.formatted_end_time}` : 
                                                        ` - ${event.formatted_end_time}`}
                                                </p>
                                                <p class="mb-1"><i class="bi bi-geo-alt"></i> ${event.location}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="event-actions mt-3">
                                        <button type="button" class="btn btn-sm ${event.is_registered ? 'btn-danger' : 'btn-primary register-btn'}" 
                                                data-event-id="${event.id}">
                                            ${event.is_registered ? 'Unregister' : 'Register'}
                                        </button>
                                        <button type="button" class="btn btn-sm ${event.in_calendar ? 'btn-outline-danger' : 'btn-outline-primary calendar-btn'}" 
                                                data-event-id="${event.id}">
                                                    ${event.in_calendar ? 'Remove from Calendar' : 'Add to Calendar'}
                                                </button>
                                        <button class="favorite-btn" onclick="toggleFavorite(this, ${event.id})" 
                                                data-event-id="${event.id}"
                                                ${event.is_favorite ? 'data-favorite="true"' : ''}>
                                            <i class="bi ${event.is_favorite ? 'bi-star-fill' : 'bi-plus-lg'}"></i>
                                            <span>${event.is_favorite ? 'Remove from Favorites' : 'Add to Favorites'}</span>
                                        </button>
                                    </div>
                                </div>
                            `;
                            eventsContainer.append(eventHtml);
                        });
                    } else {
                        eventsContainer.html('<p class="text-muted">No upcoming events found.</p>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Failed to load upcoming events:', error);
                    $('#upcoming-events-list').html('<p class="text-muted">Error loading events. Please try again later.</p>');
                }
            });
        }

        // Set interval to refresh events every 5 minutes
        setInterval(loadUpcomingEvents, 300000);

        // Initial load
        $(document).ready(function() {
            loadUpcomingEvents();
            // ... rest of your document.ready code ...
        });

        $(document).ready(function() {
            // Initialize FullCalendar
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,listWeek'
                },
                events: 'calendar_events.php',
                eventClick: function(info) {
                    showEventDetails(info.event.id);
                },
                eventDidMount: function(info) {
                    const category = info.event.extendedProps.category;
                    if (category) {
                        info.el.classList.add(`event-${category.toLowerCase()}`);
                    }
                }
            });
            calendar.render();

            // Function to bind event handlers
            function bindEventHandlers() {
                // Remove existing handlers
                $(document).off('click', '.register-btn, .btn-danger, .calendar-btn, .btn-outline-danger');
                
                // Handle Register/Unregister button clicks
                $(document).on('click', '.register-btn, .btn-danger', function() {
                    const eventId = $(this).data('event-id');
                    const button = $(this);
                    const isUnregister = button.hasClass('btn-danger');
                    const currentCount = parseInt($('#registered-events-count').text()) || 0;
                    
                    button.prop('disabled', true);
                    
                    $.ajax({
                        url: 'event_registration.php',
                        type: 'POST',
                        data: {
                            event_id: eventId,
                            action: isUnregister ? 'unregister' : 'register'
                        },
                        success: function(response) {
                            try {
                                const data = JSON.parse(response);
                                if (data.status === 'success') {
                                    if (isUnregister) {
                                        button.text('Register')
                                            .removeClass('btn-danger')
                                            .addClass('btn-primary register-btn');
                                        // Animate counter down
                                        animateCounter($('#registered-events-count'), currentCount, currentCount - 1, 500);
                                        
                                        // Remove from calendar if it was there
                                        removeFromCalendar(eventId);
                                    } else {
                                        button.text('Unregister')
                                            .removeClass('btn-primary register-btn')
                                            .addClass('btn-danger');
                                        // Animate counter up
                                        animateCounter($('#registered-events-count'), currentCount, currentCount + 1, 500);
                                        
                                        // Add to calendar automatically
                                        addToCalendar(eventId);
                                    }
                                    
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Success!',
                                        text: isUnregister ? 
                                            'You have successfully unregistered from this event' :
                                            'You have successfully registered for this event',
                                        toast: true,
                                        position: 'top-end',
                                        showConfirmButton: false,
                                        timer: 3000
                                    });
                                    
                                    // Update upcoming events count as well
                                    updateUpcomingEventsCount();
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error!',
                                        text: data.message || 'Failed to process your request',
                                        toast: true,
                                        position: 'top-end',
                                        showConfirmButton: false,
                                        timer: 3000
                                    });
                                }
                            } catch (e) {
                                console.error('Error parsing response:', e);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error!',
                                    text: 'An error occurred while processing your request',
                                    toast: true,
                                    position: 'top-end',
                                    showConfirmButton: false,
                                    timer: 3000
                                });
                            }
                        },
                        error: function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: 'Failed to connect to the server',
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000
                            });
                        },
                        complete: function() {
                            button.prop('disabled', false);
                        }
                    });
                });
                
                // Handle Calendar button clicks (both Add and Remove)
                $(document).on('click', '.calendar-btn, .btn-outline-danger', function() {
                    const eventId = $(this).data('event-id');
                    const button = $(this);
                    const isRemove = button.hasClass('btn-outline-danger');
                    
                    button.prop('disabled', true);
                    
                    console.log('Calendar button clicked:', {
                        eventId: eventId,
                        isRemove: isRemove,
                        buttonClasses: button.attr('class')
                    });
                    
                    $.ajax({
                        url: 'calendar_action.php',
                        type: 'POST',
                        data: {
                            event_id: eventId,
                            action: isRemove ? 'remove' : 'add'
                        },
                        success: function(response) {
                            console.log('Calendar action response:', response);
                            try {
                                const data = JSON.parse(response);
                                if (data.status === 'success') {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Success!',
                                        text: isRemove ? 
                                            'Event has been removed from your calendar' :
                                            'Event has been added to your calendar'
                                    });
                                    
                                    if (isRemove) {
                                        button.text('Add to Calendar')
                                            .removeClass('btn-outline-danger')
                                            .addClass('btn-outline-primary calendar-btn');
                                    } else {
                                        button.text('Remove from Calendar')
                                            .removeClass('btn-outline-primary calendar-btn')
                                            .addClass('btn-outline-danger');
                                    }
                                    
                                    // Refresh the calendar
                                    if (calendar) {
                                        calendar.refetchEvents();
                                    }
                                    
                                    // Update dashboard stats
                                    updateDashboardStats();
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error!',
                                        text: data.message || 'Failed to process your request'
                                    });
                                }
                            } catch (e) {
                                console.error('Error parsing response:', e);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error!',
                                    text: 'An error occurred while processing your request'
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Calendar action error:', {
                                status: status,
                                error: error,
                                response: xhr.responseText
                            });
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: 'Failed to connect to the server'
                            });
                        },
                        complete: function() {
                            button.prop('disabled', false);
                        }
                    });
                });
            }

            // Function to show event details in a modal
            function showEventDetails(eventId) {
                $.ajax({
                    url: 'events_action.php',
                    method: 'GET',
                    data: { action: 'getEventDetails', event_id: eventId },
                    success: function(response) {
                        if (response.status === 'success' && response.event) {
                            const event = response.event;
                            const modal = `
                                <div class="modal fade" id="eventModal" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">${event.title}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p><strong>Date:</strong> ${new Date(event.start_datetime).toLocaleString()}</p>
                                                <p><strong>Location:</strong> ${event.location}</p>
                                                <p><strong>Category:</strong> ${event.category}</p>
                                                <p><strong>Description:</strong> ${event.description}</p>
                                                <p><strong>Capacity:</strong> ${event.current_registrations}/${event.max_capacity}</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                <button type="button" class="btn ${event.is_registered ? 'btn-danger' : 'btn-primary'}" 
                                                        data-event-id="${event.event_id}">
                                                    ${event.is_registered ? 'Unregister' : 'Register'}
                                                </button>
                                                <button type="button" class="btn ${event.in_calendar ? 'btn-outline-danger' : 'btn-outline-primary'}" 
                                                        data-event-id="${event.event_id}">
                                                    ${event.in_calendar ? 'Remove from Calendar' : 'Add to Calendar'}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;
                            $('body').append(modal);
                            const eventModal = new bootstrap.Modal(document.getElementById('eventModal'));
                            eventModal.show();
                            
                            // Bind event handlers for modal buttons
                            $('#eventModal').on('click', '.btn-primary, .btn-danger', function() {
                                const eventId = $(this).data('event-id');
                                const isUnregister = $(this).hasClass('btn-danger');
                                if (isUnregister) {
                                    cancelRegistration(eventId);
                                } else {
                                    registerForEvent(eventId);
                                }
                            });
                            
                            $('#eventModal').on('click', '.btn-outline-primary, .btn-outline-danger', function() {
                                const eventId = $(this).data('event-id');
                                const isRemove = $(this).hasClass('btn-outline-danger');
                                if (isRemove) {
                                    removeFromCalendar(eventId);
                                } else {
                                    addToCalendar(eventId);
                                }
                            });
                            
                            $('#eventModal').on('hidden.bs.modal', function () {
                                $(this).remove();
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error loading event details:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Failed to load event details'
                        });
                    }
                });
            }

            // Function to register for an event
            function registerForEvent(eventId) {
                $.ajax({
                    url: 'event_registration.php',
                    method: 'POST',
                    data: { action: 'register', event_id: eventId },
                    success: function(response) {
                        if (response.status === 'success') {
                            // Refresh the events list and calendar
                            loadUpcomingEvents();
                            calendar.refetchEvents();
                            // Show success message
                            showAlert('Successfully registered for the event!', 'success');
                            updateDashboardStats();
                            const currentCount = parseInt($('#registered-events-count').text()) || 0;
                            animateCounter($('#registered-events-count'), currentCount, currentCount + 1, 500);
                            
                            // Automatically add to calendar
                            addToCalendar(eventId);
                        } else {
                            showAlert(response.message || 'Failed to register for the event', 'danger');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error registering for event:', error);
                        showAlert('Failed to register for the event. Please try again later.', 'danger');
                    }
                });
            }

            // Function to cancel registration
            function cancelRegistration(eventId) {
                if (confirm('Are you sure you want to cancel this registration?')) {
                    $.ajax({
                        url: 'event_registration.php',
                        method: 'POST',
                        data: { action: 'unregister', event_id: eventId },
                        success: function(response) {
                            if (response.status === 'success') {
                                // Refresh the events list and calendar
                                loadUpcomingEvents();
                                calendar.refetchEvents();
                                // Show success message
                                showAlert('Registration cancelled successfully!', 'success');
                                updateDashboardStats();
                                const currentCount = parseInt($('#registered-events-count').text()) || 0;
                                animateCounter($('#registered-events-count'), currentCount, currentCount - 1, 500);
                                
                                // Automatically remove from calendar
                                removeFromCalendar(eventId);
                            } else {
                                showAlert(response.message || 'Failed to cancel registration', 'danger');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error cancelling registration:', error);
                            showAlert('Failed to cancel registration. Please try again later.', 'danger');
                        }
                    });
                }
            }

            // Function to add event to calendar
            function addToCalendar(eventId) {
                $.ajax({
                    url: 'calendar_action.php',
                    type: 'POST',
                    data: {
                        action: 'add',
                        event_id: eventId
                    },
                    success: function(response) {
                        try {
                            const data = JSON.parse(response);
                            if (data.status === 'success') {
                                // Update calendar button if it exists
                                const calendarBtn = $(`.calendar-btn[data-event-id="${eventId}"]`);
                                if (calendarBtn.length) {
                                    calendarBtn.removeClass('btn-outline-primary calendar-btn')
                                             .addClass('btn-outline-danger')
                                             .text('Remove from Calendar');
                                }
                                
                                // Refresh the calendar view
                                if (calendar) {
                                    calendar.refetchEvents();
                                }
                            }
                        } catch (e) {
                            console.error('Error adding to calendar:', e);
                        }
                    }
                });
            }

            // Function to remove event from calendar
            function removeFromCalendar(eventId) {
                $.ajax({
                    url: 'calendar_action.php',
                    type: 'POST',
                    data: {
                        action: 'remove',
                        event_id: eventId
                    },
                    success: function(response) {
                        try {
                            const data = JSON.parse(response);
                            if (data.status === 'success') {
                                // Update calendar button if it exists
                                const calendarBtn = $(`.btn-outline-danger[data-event-id="${eventId}"]`);
                                if (calendarBtn.length) {
                                    calendarBtn.removeClass('btn-outline-danger')
                                             .addClass('btn-outline-primary calendar-btn')
                                             .text('Add to Calendar');
                                }
                                
                                // Refresh the calendar view
                                if (calendar) {
                                    calendar.refetchEvents();
                                }
                            }
                        } catch (e) {
                            console.error('Error removing from calendar:', e);
                        }
                    }
                });
            }

            // Function to show alerts
            function showAlert(message, type) {
                const alert = `
                    <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                        ${message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;
                $('#alerts-container').html(alert);
                setTimeout(() => {
                    $('.alert').alert('close');
                }, 5000);
            }

            // Function to update upcoming events count
            function updateUpcomingEventsCount() {
                $.ajax({
                    url: 'get_dashboard_stats.php',
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            const currentCount = parseInt($('#upcoming-events-count').text()) || 0;
                            const newCount = parseInt(response.stats.upcoming_events) || 0;
                            if (currentCount !== newCount) {
                                animateCounter($('#upcoming-events-count'), currentCount, newCount, 500);
                            }
                        }
                    }
                });
            }

            // Initial binding of event handlers
            bindEventHandlers();

            // Load initial data
            updateDashboardStats();
            loadUpcomingEvents();

            // Refresh data every 5 minutes
            setInterval(() => {
                console.log('Running scheduled stats update');
                updateDashboardStats();
                calendar.refetchEvents();
            }, 300000);

            // Handle View All button click
            $('#viewAllEvents').click(function() {
                const button = $(this);
                const originalText = button.html();
                button.html('<i class="bi bi-hourglass-split"></i> Loading...').prop('disabled', true);

                $.ajax({
                    url: 'events_action.php',
                    method: 'POST',
                    data: {
                        action: 'getAllEvents'
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            const eventsContainer = $('#upcoming-events-list');
                            eventsContainer.empty();

                            if (response.events.length === 0) {
                                eventsContainer.html('<p class="text-muted">No upcoming events found.</p>');
                                return;
                            }

                            // Group events by date
                            const groupedEvents = {};
                            response.events.forEach(event => {
                                const date = new Date(event.start_date).toDateString();
                                if (!groupedEvents[date]) {
                                    groupedEvents[date] = [];
                                }
                                groupedEvents[date].push(event);
                            });

                            // Sort dates
                            const sortedDates = Object.keys(groupedEvents).sort((a, b) => 
                                new Date(a) - new Date(b)
                            );

                            // Display events grouped by date
                            sortedDates.forEach(date => {
                                const dateHeader = formatDateHeader(new Date(date));
                                const eventsHtml = groupedEvents[date].map(event => {
                                    const startDate = new Date(event.start_date);
                                    const endDate = new Date(event.end_date);
                                    return `
                                        <div class="event-card mb-3 p-3 border rounded">
                                            <h5 class="event-title">${escapeHtml(event.title)}</h5>
                                            <p class="event-location"><i class="bi bi-geo-alt"></i> ${escapeHtml(event.venue)}</p>
                                            <p class="event-date"><i class="bi bi-calendar"></i> ${formatEventTime(startDate, endDate)}</p>
                                            <div class="event-actions mt-2">
                                                <button type="button" class="btn btn-sm ${event.is_registered ? 'btn-danger' : 'btn-primary register-btn'}" 
                                                        data-event-id="${event.id}">
                                                    ${event.is_registered ? 'Unregister' : 'Register'}
                                                </button>
                                                <button type="button" class="btn btn-sm ${event.in_calendar ? 'btn-outline-danger' : 'btn-outline-primary calendar-btn'}" 
                                                        data-event-id="${event.id}">
                                                    ${event.in_calendar ? 'Remove from Calendar' : 'Add to Calendar'}
                                                </button>
                                                <button class="favorite-btn" onclick="toggleFavorite(this, ${event.id})" 
                                                        data-event-id="${event.id}"
                                                        ${event.is_favorite ? 'data-favorite="true"' : ''}>
                                                    <i class="bi ${event.is_favorite ? 'bi-star-fill' : 'bi-plus-lg'}"></i>
                                                    <span>${event.is_favorite ? 'Remove from Favorites' : 'Add to Favorites'}</span>
                                                </button>
                                            </div>
                                        </div>
                                    `;
                                }).join('');

                                eventsContainer.append(`
                                    <div class="date-group mb-4">
                                        <h5 class="text-muted mb-3">${dateHeader}</h5>
                                        ${eventsHtml}
                                    </div>
                                `);
                            });

                            // Add a button to show less events
                            eventsContainer.append(`
                                <div class="text-center mt-4">
                                    <button class="btn btn-secondary" onclick="location.reload()">
                                        <i class="bi bi-arrow-up"></i> Show Less
                                    </button>
                                </div>
                            `);

                            // Rebind event handlers
                            bindEventHandlers();
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Failed to load events'
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to connect to the server'
                        });
                    },
                    complete: function() {
                        button.html(originalText).prop('disabled', false);
                    }
                });
            });

            function formatDateHeader(date) {
                const today = new Date();
                const tomorrow = new Date(today);
                tomorrow.setDate(tomorrow.getDate() + 1);

                if (date.toDateString() === today.toDateString()) {
                    return 'Today';
                } else if (date.toDateString() === tomorrow.toDateString()) {
                    return 'Tomorrow';
                } else {
                    return date.toLocaleDateString('en-US', { 
                        weekday: 'long',
                        month: 'long',
                        day: 'numeric'
                    });
                }
            }

            function formatEventTime(startDate, endDate) {
                const timeOptions = { hour: 'numeric', minute: '2-digit', hour12: true };
                if (startDate.toDateString() === endDate.toDateString()) {
                    return `${startDate.toLocaleTimeString('en-US', timeOptions)} - ${endDate.toLocaleTimeString('en-US', timeOptions)}`;
                } else {
                    return `${startDate.toLocaleString('en-US', timeOptions)} - ${endDate.toLocaleString('en-US', timeOptions)}`;
                }
            }

            function escapeHtml(unsafe) {
                return unsafe
                    .replace(/&/g, "&amp;")
                    .replace(/</g, "&lt;")
                    .replace(/>/g, "&gt;")
                    .replace(/"/g, "&quot;")
                    .replace(/'/g, "&#039;");
            }
        });

        function toggleFavorite(button, eventId) {
            const isFavorite = button.getAttribute('data-favorite') === 'true';
            
            // Disable the button while processing
            button.disabled = true;
            
            // Store the current favorite count
            const currentCount = parseInt($('#favorite-events-count').text()) || 0;
            
            $.ajax({
                url: 'favorites_action.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    action: isFavorite ? 'removeFavorite' : 'addFavorite',
                    event_id: eventId
                },
                success: function(data) {
                    console.log('Server response:', data);
                    
                    if (data.status === 'success') {
                        // Toggle the favorite state
                        if (isFavorite) {
                            $(button).find('i').removeClass('bi-star-fill').addClass('bi-plus-lg');
                            $(button).find('span').text('Add to Favorites');
                            button.setAttribute('data-favorite', 'false');
                            // Animate counter down
                            animateCounter($('#favorite-events-count'), currentCount, currentCount - 1, 500);
                        } else {
                            $(button).find('i').removeClass('bi-plus-lg').addClass('bi-star-fill');
                            $(button).find('span').text('Remove from Favorites event');
                            button.setAttribute('data-favorite', 'true');
                            // Animate counter up
                            animateCounter($('#favorite-events-count'), currentCount, currentCount + 1, 500);
                        }
                        
                        // Show success message
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: data.message,
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });

                        // Update the events list and dashboard stats
                        setTimeout(() => {
                            loadUpcomingEvents();
                            updateDashboardStats();
                        }, 500);
                    } else {
                        console.error('Error response:', data);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: data.message || 'Failed to update favorites',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', {
                        status: status,
                        error: error,
                        response: xhr.responseText
                    });
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Failed to update favorites',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                },
                complete: function() {
                    // Re-enable the button
                    button.disabled = false;
                }
            });
        }

        function updateFavoriteCount() {
            $.ajax({
                url: 'favorites_action.php',
                type: 'GET',
                data: { action: 'getFavorites' },
                success: function(response) {
                    if (response.status === 'success' && response.favorites) {
                        $('#favorite-events-count').text(response.favorites.length);
                    }
                }
            });
        }

        // Initial update of favorite count
        $(document).ready(function() {
            updateFavoriteCount();
        });

        // Function to handle club membership
        function toggleClubMembership(button, clubId) {
            const isMember = button.getAttribute('data-member') === 'true';
            const currentCount = parseInt($('#clubs-count').text()) || 0;
            
            button.disabled = true;
            
            $.ajax({
                url: 'club_membership_action.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    action: isMember ? 'leave' : 'join',
                    club_id: clubId
                },
                success: function(data) {
                    if (data.status === 'success') {
                        if (isMember) {
                            $(button).removeClass('btn-danger').addClass('btn-primary');
                            $(button).text('Join Club');
                            button.setAttribute('data-member', 'false');
                            // Animate counter down
                            animateCounter($('#clubs-count'), currentCount, currentCount - 1, 500);
                        } else {
                            $(button).removeClass('btn-primary').addClass('btn-danger');
                            $(button).text('Leave Club');
                            button.setAttribute('data-member', 'true');
                            // Animate counter up
                            animateCounter($('#clubs-count'), currentCount, currentCount + 1, 500);
                        }
                        
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: data.message,
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: data.message || 'Failed to update club membership',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });
                    }
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Failed to update club membership',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                },
                complete: function() {
                    button.disabled = false;
                }
            });
        }

        // Function to update upcoming events count
        function updateUpcomingEventsCount() {
            $.ajax({
                url: 'get_dashboard_stats.php',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        const currentCount = parseInt($('#upcoming-events-count').text()) || 0;
                        const newCount = parseInt(response.stats.upcoming_events) || 0;
                        if (currentCount !== newCount) {
                            animateCounter($('#upcoming-events-count'), currentCount, newCount, 500);
                        }
                    }
                }
            });
        }

        // Update all counters periodically
        setInterval(() => {
            updateUpcomingEventsCount();
        }, 300000); // Every 5 minutes

        // Function to load notifications
        function loadNotifications() {
            $.ajax({
                url: 'get_notifications.php',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    const notificationsContainer = $('#notifications-list');
                    notificationsContainer.empty();
                    
                    if (response.status === 'success' && response.notifications.length > 0) {
                        response.notifications.forEach(notification => {
                            const notificationHtml = `
                                <div class="notification-card mb-3 p-3 border rounded ${notification.type === 'recommendation' ? 'bg-light-warning' : 'bg-light-info'}">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h5 class="notification-title mb-2">${notification.title}</h5>
                                            <p class="notification-message mb-2">${notification.message}</p>
                                            <small class="text-muted">${new Date(notification.timestamp).toLocaleString()}</small>
                                        </div>
                                        <button class="btn btn-sm btn-outline-primary" onclick="window.location.href='./events.php?event_id=${notification.event_id}'">
                                            View Event
                                        </button>
                                    </div>
                                </div>
                            `;
                            notificationsContainer.append(notificationHtml);
                        });
                    } else {
                        notificationsContainer.html(`
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i> No notifications at the moment.
                            </div>
                        `);
                    }
                },
                error: function() {
                    $('#notifications-list').html(`
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-circle"></i> Failed to load notifications.
                        </div>
                    `);
                }
            });
        }

        // Load notifications on page load
        loadNotifications();

        // Refresh notifications every 5 minutes
        setInterval(loadNotifications, 300000);
    </script>

    <!-- Feedback Modal -->
    <div class="modal fade" id="feedbackModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Event Feedback</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="feedbackForm">
                        <div class="mb-3">
                            <label for="eventSelect" class="form-label">Select Event</label>
                            <select class="form-select" id="eventSelect" name="event_id" required>
                                <option value="">Choose an event...</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="rating" class="form-label">Rating</label>
                            <div class="rating">
                                <input type="radio" name="rating" id="star5" value="5" required>
                                <label for="star5" title="5 stars">☆</label>
                                <input type="radio" name="rating" id="star4" value="4">
                                <label for="star4" title="4 stars">☆</label>
                                <input type="radio" name="rating" id="star3" value="3">
                                <label for="star3" title="3 stars">☆</label>
                                <input type="radio" name="rating" id="star2" value="2">
                                <label for="star2" title="2 stars">☆</label>
                                <input type="radio" name="rating" id="star1" value="1">
                                <label for="star1" title="1 star">☆</label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="comment" class="form-label">Feedback Comment</label>
                            <textarea class="form-control" id="comment" name="comment" rows="4" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Submit Feedback</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Load completed events for feedback
            function loadCompletedEvents() {
                $.ajax({
                    url: 'feedback_action.php',
                    type: 'POST',
                    data: { 
                        action: 'getCompletedEvents',
                        user_id: <?php echo $_SESSION['user_id']; ?>
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            const select = $('#eventSelect');
                            select.empty();
                            select.append('<option value="">Choose an event...</option>');
                            
                            if (response.events.length === 0) {
                                select.append('<option value="" disabled>No completed events found</option>');
                                Swal.fire({
                                    icon: 'info',
                                    title: 'No Events',
                                    text: 'You have no completed events to provide feedback for.'
                                });
                            } else {
                                response.events.forEach(function(event) {
                                    select.append(`<option value="${event.event_id}">${event.title} (${event.end_date})</option>`);
                                });
                            }
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to load events'
                        });
                    }
                });
            }

            // Handle form submission
            $('#feedbackForm').on('submit', function(e) {
                e.preventDefault();
                
                const formData = {
                    action: 'submitFeedback',
                    event_id: $('#eventSelect').val(),
                    rating: $('input[name="rating"]:checked').val(),
                    comment: $('#comment').val()
                };

                $.ajax({
                    url: 'feedback_action.php',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: 'Your feedback has been submitted successfully',
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                $('#feedbackForm')[0].reset();
                                loadCompletedEvents();
                                $('#feedbackModal').modal('hide');
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to submit feedback'
                        });
                    }
                });
            });

            // Load events when modal is shown
            $('#feedbackModal').on('show.bs.modal', function() {
                loadCompletedEvents();
            });
        });
    </script>
</body>
</html>