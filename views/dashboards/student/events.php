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
                            $upcomingEvents = getUpcomingEvents();
                            if ($upcomingEvents->num_rows > 0) {
                                while ($event = $upcomingEvents->fetch_assoc()) {
                                    $startDate = new DateTime($event['start_datetime']);
                                    $endDate = new DateTime($event['end_datetime']);
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
                                            <button type="button" class="btn btn-sm btn-primary register-btn" data-event-id="<?php echo (int)$event['event_id']; ?>">Register</button>
                                            <button type="button" class="btn btn-sm btn-outline-primary calendar-btn" data-event-id="<?php echo (int)$event['event_id']; ?>">Add to Calendar</button>
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
        // Wait for document to be ready
        $(document).ready(function() {
            // Handle Register button click
            $('.register-btn').on('click', function() {
                const eventId = $(this).data('event-id');
                const button = $(this);
                
                // Disable button to prevent double clicks
                button.prop('disabled', true);
                
                $.ajax({
                    url: 'event_registration.php',
                    type: 'POST',
                    data: {
                        event_id: eventId
                    },
                    success: function(response) {
                        try {
                            const data = JSON.parse(response);
                            if (data.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success!',
                                    text: data.message
                                });
                                button.text('Registered').addClass('btn-success').removeClass('btn-primary');
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error!',
                                    text: data.message
                                });
                                button.prop('disabled', false);
                            }
                        } catch (e) {
                            console.error('Error parsing response:', e);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: 'An error occurred while processing your request'
                            });
                            button.prop('disabled', false);
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Failed to connect to the server'
                        });
                        button.prop('disabled', false);
                    }
                });
            });

            // Handle Add to Calendar button click
            $('.calendar-btn').on('click', function() {
                const eventId = $(this).data('event-id');
                const button = $(this);
                
                // Disable button temporarily
                button.prop('disabled', true);
                
                $.ajax({
                    url: 'calendar_action.php',
                    type: 'POST',
                    data: {
                        event_id: eventId
                    },
                    xhrFields: {
                        responseType: 'blob'
                    },
                    success: function(response) {
                        // Create blob link to download
                        const blob = new Blob([response], { type: 'text/calendar' });
                        const url = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = 'event.ics';
                        document.body.appendChild(a);
                        a.click();
                        window.URL.revokeObjectURL(url);
                        document.body.removeChild(a);
                        
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'Event has been added to your calendar'
                        });
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Failed to add event to calendar'
                        });
                    },
                    complete: function() {
                        button.prop('disabled', false);
                    }
                });
            });

            // Search functionality
            $('#searchButton').click(function() {
                performSearch();
            });

            $('#searchInput').keypress(function(e) {
                if (e.which == 13) {
                    performSearch();
                }
            });

            function performSearch() {
                const searchTerm = $('#searchInput').val();
                
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
                                displayEvents(data.events);
                            }
                        } catch (e) {
                            console.error(e);
                            Swal.fire({
                                title: 'Error!',
                                text: 'Error parsing search results',
                                icon: 'error',
                                confirmButtonColor: '#3085d6'
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            title: 'Error!',
                            text: 'Error occurred while searching events',
                            icon: 'error',
                            confirmButtonColor: '#3085d6'
                        });
                    }
                });
            }

            function displayEvents(events) {
                const eventsContainer = $('#eventsList');
                eventsContainer.empty();

                if (events.length === 0) {
                    eventsContainer.html('<p>No events found matching your search.</p>');
                    return;
                }

                events.forEach(function(event) {
                    const startDate = new Date(event.start_date);
                    const endDate = new Date(event.end_date);
                    const eventHtml = `
                        <div class="event-item">
                            <div class="event-title">${event.title}</div>
                            <div class="event-description">${event.description}</div>
                            <div class="event-meta">
                                <span class="event-category"><i class="bi bi-tag"></i> ${event.category}</span>
                                <span class="event-capacity"><i class="bi bi-people"></i> Capacity: ${event.max_capacity}</span>
                            </div>
                            <div class="event-details">
                                <i class="bi bi-calendar"></i> ${formatDateRange(startDate, endDate)}
                                <br>
                                <i class="bi bi-geo-alt"></i> ${event.venue}
                            </div>
                            <div class="event-actions">
                                <button class="btn btn-sm btn-primary register-btn" data-event-id="${event.id}">Register</button>
                                <button class="btn btn-sm btn-outline-primary calendar-btn" data-event-id="${event.id}">Add to Calendar</button>
                            </div>
                        </div>
                    `;
                    eventsContainer.append(eventHtml);
                });
            }

            function formatDateRange(startDate, endDate) {
                const options = { year: 'numeric', month: 'long', day: 'numeric', hour: 'numeric', minute: '2-digit', hour12: true };
                if (startDate.toDateString() === endDate.toDateString()) {
                    return `${startDate.toLocaleDateString('en-US', options)} - ${endDate.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true })}`;
                }
                return `${startDate.toLocaleDateString('en-US', options)} - ${endDate.toLocaleDateString('en-US', options)}`;
            }
        });
    </script>
</body>
</html>