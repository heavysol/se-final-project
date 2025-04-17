<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Registrations - Campus Events</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="../../../assets/css/general-styles.css">
    <link rel="stylesheet" href="../../../assets/css/dashboard-styles.css">
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
            
            <!-- Events Dropdown -->
            <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="bi bi-calendar-event"></i> Events
                </a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="./events.php">All Events</a></li>
                    <li><a class="dropdown-item" href="./events.php?filter=upcoming">Upcoming Events</a></li>
                    <li><a class="dropdown-item" href="./events.php?filter=past">Past Events</a></li>
                    <li><a class="dropdown-item" href="./events.php?filter=today">Today's Events</a></li>
                </ul>
            </li>
            
            <li><a href="./registrations.php" class="active"><i class="bi bi-journal-check"></i> My Registrations</a></li>
            <li><a href="./favourites.php"><i class="bi bi-star"></i> Favorites</a></li>
            <li><a href="./club-orgs.php"><i class="bi bi-people"></i> Clubs & Organizations</a></li>
            <li><a href="../notifications.php"><i class="bi bi-bell"></i> Notifications</a></li>
            <li><a href="../settings.php"><i class="bi bi-gear"></i> Settings</a></li>
            
            <!-- Logout Button -->
            <li>
                <a href="./log_out_action.php" class="btn btn-danger logout-btn" onclick="return confirm('Are you sure you want to logout?')">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </li>
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
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Event Title</th>
                                        <th>Category</th>
                                        <th>Location</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Registration Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="registeredEventsTable">
                                    <!-- Events will be loaded here dynamically -->
                                </tbody>
                            </table>
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
    
    <!-- Debug info script -->
    <script>
        console.log('Debug Info:');
        console.log('Session user_id: <?php echo $_SESSION['user_id']; ?>');
        console.log('Current URL:', window.location.href);
    </script>

    <script>
        $(document).ready(function() {
            console.log('Page loaded, checking session...');
            console.log('Session user_id:', '<?php echo $_SESSION['user_id']; ?>');

            function loadRegisteredEvents() {
                console.log('Loading registered events...');
                
                // Show loading spinner
                $('#registeredEventsTable').html('<tr><td colspan="8" class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></td></tr>');

                // Load registered events
                $.ajax({
                    url: 'registration_action.php',
                    type: 'POST',
                    data: {
                        action: 'getRegisteredEvents'
                    },
                    dataType: 'json',
                    beforeSend: function() {
                        console.log('Sending request to registration_action.php...');
                    },
                    success: function(response) {
                        console.log('Received response:', response);
                        
                        if (response.status === 'error') {
                            console.error('Server returned error:', response);
                            $('#registeredEventsTable').html(`
                                <tr>
                                    <td colspan="8" class="text-center text-danger">
                                        Error: ${response.message}
                                        ${response.debug ? '<br><small class="text-muted">Debug: ' + JSON.stringify(response.debug) + '</small>' : ''}
                                    </td>
                                </tr>
                            `);
                            $('#noEventsMessage').show();
                            return;
                        }

                        if (!response.events || response.events.length === 0) {
                            console.log('No events found');
                            $('#registeredEventsTable').html('<tr><td colspan="8" class="text-center">You haven\'t registered for any events yet.</td></tr>');
                            $('#noEventsMessage').show();
                            return;
                        }

                        console.log('Found ' + response.events.length + ' events');
                        
                        // Clear existing table content
                        var tableContent = '';

                        // Add each event to the table
                        response.events.forEach(function(event) {
                            tableContent += `
                                <tr>
                                    <td>${event.title || 'N/A'}</td>
                                    <td>${event.category || 'N/A'}</td>
                                    <td>${event.location || 'N/A'}</td>
                                    <td>${event.start_date || 'N/A'}</td>
                                    <td>${event.end_date || 'N/A'}</td>
                                    <td>${event.registration_date || 'N/A'}</td>
                                    <td>
                                        <span class="badge bg-${event.attendance_status === 'pending' ? 'warning' : 'success'}">
                                            ${event.attendance_status || 'pending'}
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
                        console.error('AJAX Error Details:', {
                            status: xhr.status,
                            statusText: xhr.statusText,
                            responseText: xhr.responseText,
                            error: error
                        });

                        let errorMessage = 'Failed to load registered events.';
                        let debugInfo = '';
                        
                        try {
                            const response = JSON.parse(xhr.responseText);
                            if (response.message) {
                                errorMessage = response.message;
                            }
                            if (response.debug) {
                                debugInfo = JSON.stringify(response.debug);
                            }
                        } catch(e) {
                            console.error('Error parsing response:', e);
                            debugInfo = 'Status: ' + xhr.status + ', Error: ' + error;
                        }

                        $('#registeredEventsTable').html(`
                            <tr>
                                <td colspan="8" class="text-center">
                                    <div class="alert alert-danger">
                                        <strong>${errorMessage}</strong>
                                        ${debugInfo ? '<br><small class="text-muted">Technical details: ' + debugInfo + '</small>' : ''}
                                    </div>
                                </td>
                            </tr>
                        `);
                        $('#noEventsMessage').show();
                    }
                });
            }

            // Initial load
            loadRegisteredEvents();

            // Add refresh button
            $('.dashboard-card h4').append(`
                <button class="btn btn-sm btn-outline-primary float-end refresh-events">
                    <i class="bi bi-arrow-clockwise"></i> Refresh
                </button>
            `);

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
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, cancel it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Disable button and show loading state
                        button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Cancelling...');
                        
                        $.ajax({
                            url: 'registration_action.php',
                            type: 'POST',
                            data: {
                                action: 'cancelRegistration',
                                event_id: eventId
                            },
                            dataType: 'json',
                            success: function(data) {
                                if (data.status === 'success') {
                                    Swal.fire(
                                        'Cancelled!',
                                        'Your registration has been cancelled.',
                                        'success'
                                    ).then(() => {
                                        // Reload events after successful cancellation
                                        loadRegisteredEvents();
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: data.message || 'Failed to cancel registration'
                                    });
                                    // Re-enable button
                                    button.prop('disabled', false).text('Cancel Registration');
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error('Cancel registration error:', {
                                    status: status,
                                    error: error,
                                    response: xhr.responseText
                                });
                                
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'Failed to cancel registration. Please try again.'
                                });
                                
                                // Re-enable button
                                button.prop('disabled', false).text('Cancel Registration');
                            }
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>