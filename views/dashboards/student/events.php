<?php
require_once 'events_action.php';
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events - Campus Events</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../../assets/css/general-styles.css">
    <link rel="stylesheet" href="../../../assets/css/dashboard-styles.css">
    <!-- Add SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
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
    </style>
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h3>Campus Events</h3>
            <div class="text-white-50 small">Student Dashboard</div>
        </div>
        <ul class="sidebar-menu">
            <li><a href="./student_dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
            <li><a href="./events.php" class='active'><i class="bi bi-calendar-event"></i> Events</a></li>
            <li><a href="./registrations.php"><i class="bi bi-journal-check"></i> My Registrations</a></li>
            <li><a href="./favourites.php"><i class="bi bi-star"></i> Favorites</a></li>
            <li><a href="./clubs-orgs.php"><i class="bi bi-people"></i> Clubs & Organizations</a></li>
            <li><a href="../notifications.php"><i class="bi bi-bell"></i> Notifications</a></li>
            <li><a href="../settings.php"><i class="bi bi-gear"></i> Settings</a></li>
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
                            if (!isset($_SESSION['user_id'])) {
                                session_start();
                            }
                            $userId = $_SESSION['user_id'] ?? 0;
                            $upcomingEvents = getUpcomingEvents($userId);
                            if ($upcomingEvents->num_rows > 0) {
                                while ($event = $upcomingEvents->fetch_assoc()) {
                                    $startDate = new DateTime($event['start_datetime']);
                                    $endDate = new DateTime($event['end_datetime']);
                                    
                                    // Set button states based on registration and calendar status
                                    $registerBtnClass = $event['is_registered'] ? 'btn-danger' : 'btn-primary register-btn';
                                    $registerBtnText = $event['is_registered'] ? 'Unregister' : 'Register';
                                    
                                    $calendarBtnClass = $event['in_calendar'] ? 'btn-outline-danger' : 'btn-outline-primary calendar-btn';
                                    $calendarBtnText = $event['in_calendar'] ? 'Remove from Calendar' : 'Add to Calendar';
                                    ?>
                                    <div class="event-item">
                                        <div class="event-title"><?php echo htmlspecialchars($event['title']); ?></div>
                                        <div class="event-description"><?php echo htmlspecialchars($event['description']); ?></div>
                                        <div class="event-meta">
                                            <span class="event-category"><i class="bi bi-tag"></i> <?php echo htmlspecialchars($event['category']); ?></span>
                                            <span class="event-capacity"><i class="bi bi-people"></i> Capacity: <?php echo htmlspecialchars($event['max_capacity']); ?></span>
                                        </div>
                                        <div class="event-details">
                                            <i class="bi bi-calendar"></i> <?php 
                                                echo $startDate->format('F j, Y g:i A');
                                                if ($startDate->format('Y-m-d') !== $endDate->format('Y-m-d')) {
                                                    echo ' - ' . $endDate->format('F j, Y g:i A');
                                                } else {
                                                    echo ' - ' . $endDate->format('g:i A');
                                                }
                                            ?>
                                            <br>
                                            <i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($event['location']); ?>
                                        </div>
                                        <div class="event-actions">
                                            <button type="button" class="btn btn-sm <?php echo $registerBtnClass; ?>" data-event-id="<?php echo (int)$event['event_id']; ?>"><?php echo $registerBtnText; ?></button>
                                            <button type="button" class="btn btn-sm <?php echo $calendarBtnClass; ?>" data-event-id="<?php echo (int)$event['event_id']; ?>"><?php echo $calendarBtnText; ?></button>
                                        </div>
                                    </div>
                                    <?php
                                }
                            } else {
                                echo '<p>No upcoming events found.</p>';
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
                    success: function(response) {
                        try {
                            const data = JSON.parse(response);
                            if (data.status === 'success') {
                                displaySearchResults(data.events, searchTerm);
                                // Rebind event handlers for the new buttons
                                bindEventHandlers();
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Search Failed',
                                    text: 'Failed to retrieve search results'
                                });
                            }
                        } catch (e) {
                            console.error('Error parsing search results:', e);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'An error occurred while processing search results'
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
                        searchButton.prop('disabled', false).text(originalButtonText);
                    }
                });
            }

            function displaySearchResults(events, searchTerm) {
                const eventsContainer = $('#eventsList');
                eventsContainer.empty();

                if (events.length === 0) {
                    eventsContainer.html(`
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> No events found matching "${escapeHtml(searchTerm)}"
                        </div>
                    `);
                    return;
                }

                // Add search results header
                eventsContainer.append(`
                    <div class="alert alert-success mb-4">
                        <i class="bi bi-search"></i> Found ${events.length} event(s) matching "${escapeHtml(searchTerm)}"
                    </div>
                `);

                events.forEach(function(event) {
                    const startDate = new Date(event.start_date);
                    const endDate = new Date(event.end_date);
                    
                    // Check if user is registered and event is in calendar
                    const isRegistered = event.is_registered || false;
                    const isInCalendar = event.in_calendar || false;
                    
                    const registerBtnClass = isRegistered ? 'btn-danger' : 'btn-primary register-btn';
                    const registerBtnText = isRegistered ? 'Unregister' : 'Register';
                    
                    const calendarBtnClass = isInCalendar ? 'btn-outline-danger' : 'btn-outline-primary calendar-btn';
                    const calendarBtnText = isInCalendar ? 'Remove from Calendar' : 'Add to Calendar';
                    
                    const eventHtml = `
                        <div class="event-item">
                            <div class="event-title">${highlightSearchTerm(event.title, searchTerm)}</div>
                            <div class="event-description">${highlightSearchTerm(event.description, searchTerm)}</div>
                            <div class="event-meta">
                                <span class="event-category"><i class="bi bi-tag"></i> ${escapeHtml(event.category)}</span>
                                <span class="event-capacity"><i class="bi bi-people"></i> Capacity: ${event.max_capacity}</span>
                            </div>
                            <div class="event-details">
                                <i class="bi bi-calendar"></i> ${formatDateRange(startDate, endDate)}
                                <br>
                                <i class="bi bi-geo-alt"></i> ${escapeHtml(event.venue)}
                            </div>
                            <div class="event-actions">
                                <button type="button" class="btn btn-sm ${registerBtnClass}" data-event-id="${event.id}">${registerBtnText}</button>
                                <button type="button" class="btn btn-sm ${calendarBtnClass}" data-event-id="${event.id}">${calendarBtnText}</button>
                            </div>
                        </div>
                    `;
                    eventsContainer.append(eventHtml);
                });
                
                // Rebind event handlers for the new buttons
                bindEventHandlers();
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
    </script>
</body>
</html>