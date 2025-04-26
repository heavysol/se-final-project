<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include events action file for event-related functions
require_once 'events_action.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header('Location: ../../../login.php');
    exit();
}

// Include database configuration
require_once '../../../db/config.php';

// Fetch user details
$userId = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT first_name FROM Users WHERE user_id = ? AND role = 'student'");
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
    <title>Events - Campus Events</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../../../assets/css/student-dashboard-styles.css" rel="stylesheet">
    <!-- Add SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        /* Sidebar Styles */
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

        .sidebar-header {
            padding: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 1rem;
        }

        .sidebar-header h3 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .sidebar-header h3 i {
            color: #3498db;
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

        /* Main Content Adjustment */
        .main-content {
            margin-left: 250px;
            padding: 20px;
            min-height: 100vh;
            background: #f8f9fa;
            transition: margin-left 0.3s ease;
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
        .event-item {
            border: 1px solid #ddd;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .event-title {
            font-size: 1.3em;
            font-weight: bold;
            margin-bottom: 10px;
            color: #2c3e50;
        }
        .event-description {
            color: #666;
            margin-bottom: 15px;
        }
        .event-details {
            color: #666;
            margin-bottom: 15px;
        }
        .event-meta {
            display: flex;
            gap: 20px;
            margin-bottom: 15px;
            font-size: 0.9em;
        }
        .event-category {
            background-color: #e9ecef;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.85em;
        }
        .event-capacity {
            color: #666;
        }
        .event-actions {
            margin-top: 15px;
            display: flex;
            gap: 10px;
        }
        .search-area {
            margin-bottom: 30px;
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
        }
        #searchInput {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px;
        }
        /* Add highlighting styles */
        mark {
            background-color: #fff3cd;
            padding: 0 2px;
            border-radius: 2px;
        }
        .alert {
            border-radius: 8px;
            padding: 12px 20px;
        }
        .alert i {
            margin-right: 8px;
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
            <h3><i class="bi bi-calendar-event"></i> <span>Campus Events</span></h3>
        </div>
        <ul class="sidebar-menu">
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
        <div class='container-fluid'>
            <!-- Search Bar -->
            <div class="row mb-4 search-area">
                <div class="col-12">
                    <h4>Search for events</h4>
                    <div class="input-group">
                        <input type="text" id="searchInput" class="form-control" placeholder="Search for events here">
                        <button class="btn btn-primary" id="searchButton">Search</button>
                    </div>
                </div>
            </div>

            <!-- Event list -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="dashboard-card">
                        <h4>
                            Upcoming Events
                            <span class="badge bg-primary">This Week</span>
                        </h4>
                        <div id="eventsList">
                            <?php
                            $upcomingEvents = getAllUpcomingEvents($userId);
                            if ($upcomingEvents->num_rows > 0) {
                                while ($event = $upcomingEvents->fetch_assoc()) {
                                    $startDate = new DateTime($event['start_datetime']);
                                    $endDate = new DateTime($event['end_datetime']);
                                    
                                    // Set button states
                                    $registerBtnClass = $event['is_registered'] ? 'btn-danger' : 'btn-primary register-btn';
                                    $registerBtnText = $event['is_registered'] ? 'Unregister' : 'Register';
                                    
                                    $calendarBtnClass = $event['in_calendar'] ? 'btn-outline-danger' : 'btn-outline-primary calendar-btn';
                                    $calendarBtnText = $event['in_calendar'] ? 'Remove from Calendar' : 'Add to Calendar';
                                    ?>
                                    <div class="event-card mb-3 p-3 border rounded">
                                        <h5 class="event-title"><?php echo htmlspecialchars($event['title']); ?></h5>
                                        <p class="event-location"><i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($event['location']); ?></p>
                                        <p class="event-date"><i class="bi bi-calendar"></i> <?php echo $startDate->format('F j, Y g:i A'); ?> - <?php echo $endDate->format('F j, Y g:i A'); ?></p>
                                        <div class="event-actions mt-2">
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
                                echo '<p class="text-muted">No events found.</p>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add these scripts right before closing body tag -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            let searchTimeout;
            
            // Real-time search on input
            $('#searchInput').on('input', function() {
                clearTimeout(searchTimeout);
                const searchTerm = $(this).val().trim();
                
                // If search term is empty, show all upcoming events
                if (searchTerm === '') {
                    location.reload();
                    return;
                }
                
                // Add small delay to prevent too many requests
                searchTimeout = setTimeout(function() {
                    performSearch(searchTerm);
                }, 300);
            });

            // Search button click
            $('#searchButton').on('click', function() {
                const searchTerm = $('#searchInput').val().trim();
                performSearch(searchTerm);
            });

            // Enter key press
            $('#searchInput').on('keypress', function(e) {
                if (e.which === 13) {
                    const searchTerm = $(this).val().trim();
                    performSearch(searchTerm);
                }
            });

            function performSearch(searchTerm) {
                if (searchTerm === '') {
                    location.reload();
                    return;
                }

                // Show loading state
                const searchButton = $('#searchButton');
                const originalButtonText = searchButton.text();
                searchButton.prop('disabled', true).text('Searching...');

                $.ajax({
                    url: 'events_action.php',
                    method: 'POST',
                    data: {
                        action: 'search',
                        search_term: searchTerm
                    },
                    dataType: 'json',
                    success: function(response) {
                        console.log('Search response:', response);
                        
                        const eventsContainer = $('#eventsList');
                        eventsContainer.empty();
                        
                        if (response.status === 'success') {
                            // Add search results header
                            eventsContainer.append(`
                                <div class="alert alert-success mb-4">
                                    <i class="bi bi-search"></i> ${response.message}
                                </div>
                            `);
                            displaySearchResults(response.events, searchTerm);
                            bindEventHandlers();
                        } 
                        else if (response.status === 'no_results') {
                            eventsContainer.html(`
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle"></i> ${response.message}
                                </div>
                                <button class="btn btn-secondary mt-3" onclick="location.reload()">
                                    <i class="bi bi-arrow-clockwise"></i> Show All Events
                                </button>
                            `);
                        }
                        else {
                            eventsContainer.html(`
                                <div class="alert alert-danger">
                                    <i class="bi bi-exclamation-triangle"></i> ${response.message}
                                </div>
                                <button class="btn btn-secondary mt-3" onclick="location.reload()">
                                    <i class="bi bi-arrow-clockwise"></i> Show All Events
                                </button>
                            `);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Search error:', { xhr, status, error });
                        const eventsContainer = $('#eventsList');
                        eventsContainer.html(`
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle"></i> Unable to connect to the server. Please try again later.
                            </div>
                            <button class="btn btn-secondary mt-3" onclick="location.reload()">
                                <i class="bi bi-arrow-clockwise"></i> Show All Events
                            </button>
                        `);
                    },
                    complete: function() {
                        searchButton.prop('disabled', false).text(originalButtonText);
                    }
                });
            }

            function displaySearchResults(events, searchTerm) {
                const eventsContainer = $('#eventsList');
                
                events.forEach(function(event) {
                    const startDate = new Date(event.start_date);
                    const endDate = new Date(event.end_date);
                    
                    const eventHtml = `
                        <div class="event-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h5 class="event-title mb-2">${highlightSearchTerm(event.title, searchTerm)}</h5>
                                    <div class="event-description">${highlightSearchTerm(event.description, searchTerm)}</div>
                                    <div class="event-meta">
                                        <span class="event-category"><i class="bi bi-tag"></i> ${escapeHtml(event.category)}</span>
                                        <span class="event-capacity"><i class="bi bi-people"></i> Capacity: ${event.max_capacity}</span>
                                    </div>
                                    <div class="event-details">
                                        <i class="bi bi-calendar"></i> ${formatDateRange(startDate, endDate)}
                                        <br>
                                        <i class="bi bi-geo-alt"></i> ${highlightSearchTerm(event.venue, searchTerm)}
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
                                            <span>${event.is_favorite ? 'Remove from Favorites events' : 'Add to Favorites'}</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    eventsContainer.append(eventHtml);
                });
            }

            function highlightSearchTerm(text, searchTerm) {
                if (!searchTerm) return escapeHtml(text);
                
                const escapedText = escapeHtml(text);
                const terms = searchTerm.split(' ').filter(term => term.length > 0);
                
                let highlightedText = escapedText;
                terms.forEach(term => {
                    const regex = new RegExp('(' + escapeRegExp(term) + ')', 'gi');
                    highlightedText = highlightedText.replace(regex, '<mark>$1</mark>');
                });
                
                return highlightedText;
            }

            function escapeRegExp(string) {
                return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            }

            function formatDateRange(startDate, endDate) {
                const options = { year: 'numeric', month: 'long', day: 'numeric', hour: 'numeric', minute: '2-digit', hour12: true };
                if (startDate.toDateString() === endDate.toDateString()) {
                    return `${startDate.toLocaleDateString('en-US', options)} - ${endDate.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true })}`;
                }
                return `${startDate.toLocaleDateString('en-US', options)} - ${endDate.toLocaleDateString('en-US', options)}`;
            }

            function escapeHtml(unsafe) {
                return unsafe
                    .replace(/&/g, "&amp;")
                    .replace(/</g, "&lt;")
                    .replace(/>/g, "&gt;")
                    .replace(/"/g, "&quot;")
                    .replace(/'/g, "&#039;");
            }

            function bindEventHandlers() {
                // Remove existing handlers
                $(document).off('click', '.register-btn, .btn-danger, .calendar-btn, .btn-outline-danger');
                
                // Handle Register/Unregister button clicks
                $(document).on('click', '.register-btn, .btn-danger', function() {
                    const eventId = $(this).data('event-id');
                    const button = $(this);
                    const isUnregister = button.hasClass('btn-danger');
                    
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
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Success!',
                                        text: isUnregister ? 
                                            'You have successfully unregistered from this event' :
                                            'You have successfully registered for this event'
                                    });
                                    
                                    if (isUnregister) {
                                        button.text('Register')
                                            .removeClass('btn-danger')
                                            .addClass('btn-primary register-btn');
                                    } else {
                                        button.text('Unregister')
                                            .removeClass('btn-primary register-btn')
                                            .addClass('btn-danger');
                                    }
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
                        error: function() {
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

            // Initial binding of event handlers
            bindEventHandlers();
        });

        function toggleFavorite(button, eventId) {
            const isFavorite = button.getAttribute('data-favorite') === 'true';
            const action = isFavorite ? 'removeFavorite' : 'addFavorite';
            
            // Disable the button while processing
            button.disabled = true;
            
            // Log the request details
            console.log('Sending favorite request:', {
                action: action,
                eventId: eventId,
                isFavorite: isFavorite
            });
            
            $.ajax({
                url: 'favorites_action.php',
                type: 'POST',
                data: {
                    action: action,
                    event_id: eventId
                },
                success: function(response) {
                    console.log('Server response:', response); // Log the raw response
                    
                    try {
                        const data = response;
                        console.log('Parsed response:', data); // Log the parsed data
                        
                        if (data.status === 'success') {
                            // Toggle the favorite state
                            if (isFavorite) {
                                $(button).find('i').removeClass('bi-star-fill').addClass('bi-plus-lg');
                                $(button).find('span').text('Add to Favorites event');
                                button.setAttribute('data-favorite', 'false');
                            } else {
                                $(button).find('i').removeClass('bi-plus-lg').addClass('bi-star-fill');
                                $(button).find('span').text('Remove from Favorites events');
                                button.setAttribute('data-favorite', 'true');
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
                        } else {
                            console.error('Error response:', data); // Log error details
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: data.message || 'An error occurred while updating favorites',
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000
                            });
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e, 'Raw response:', response);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'An error occurred while processing the server response',
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
                    
                    let errorMessage = 'An error occurred while updating favorites';
                    try {
                        const response = JSON.parse(xhr.responseText);
                        errorMessage = response.message || errorMessage;
                    } catch (e) {
                        console.error('Error parsing error response:', e);
                    }
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: errorMessage,
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
    </script>
</body>
</html>
