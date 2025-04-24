<?php
session_start();

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header('Location: ../../../login.php');
    exit();
}

require_once '../../../db/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Favorites - Campus Events</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../../assets/css/general-styles.css">
    <link rel="stylesheet" href="../../../assets/css/dashboard-styles.css">
    <style>
        /* Sidebar Styles */
        .sidebar {
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            min-height: 100vh;
            width: 250px;
            position: fixed;
            left: 0;
            top: 0;
            padding: 1.5rem 0;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
            z-index: 1000;
        }

        .sidebar-header {
            padding: 0 1.5rem;
            margin-bottom: 2rem;
            text-align: center;
        }

        .sidebar-header h3 {
            color: white;
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
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 1rem;
        }

        .sidebar-menu a:hover {
            background: rgba(255,255,255,0.1);
            color: white;
            padding-left: 2rem;
        }

        .sidebar-menu a.active {
            background: rgba(255,255,255,0.2);
            color: white;
            border-left: 4px solid #ffd700;
        }

        .sidebar-menu i {
            margin-right: 0.75rem;
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }

        .sidebar-menu a.active i {
            color: #ffd700;
        }

        .event-card {
            background: #fff;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-left: 4px solid #ffd700;
        }
        .event-title {
            font-size: 1.3em;
            margin-bottom: 10px;
            color: #2c3e50;
        }
        .event-meta {
            display: flex;
            gap: 15px;
            margin-bottom: 10px;
            color: #666;
            font-size: 0.9em;
        }
        .event-description {
            color: #666;
            margin-bottom: 15px;
        }
        .event-actions {
            display: flex;
            gap: 10px;
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
        .favorite-btn .bi-star-fill {
            color: #ffd700;
        }
        .favorite-btn span {
            margin-left: 4px;
            font-size: 0.9em;
        }
        .no-favorites {
            text-align: center;
            padding: 40px;
            background: #f8f9fa;
            border-radius: 8px;
            color: #6c757d;
        }
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
            <li><a href="./student_dashboard.php" class="active"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
            <li><a href="./events.php"><i class="bi bi-calendar-event"></i> Events</a></li>
            <li><a href="./registrations.php"><i class="bi bi-journal-check"></i> My Registrations Events</a></li>
            <li><a href="./favorites.php"><i class="bi bi-star"></i> My Favorites Events</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <div class="row mb-4">
                <div class="col-12">
                    <h1>My Favorite Events</h1>
                    <p class="text-muted">Events you've marked as favorites</p>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="dashboard-card">
                        <div id="favoriteEventsList">
                            <?php
                            $userId = $_SESSION['user_id'];
                            
                            // Get favorite events
                            $query = "SELECT e.*, 
                                    CASE WHEN r.registration_id IS NOT NULL THEN 1 ELSE 0 END as is_registered,
                                    CASE WHEN c.calendar_id IS NOT NULL THEN 1 ELSE 0 END as in_calendar,
                                    1 as is_favorite,
                                    (SELECT COUNT(*) FROM Registrations WHERE event_id = e.event_id) as current_registrations
                                    FROM Events e 
                                    INNER JOIN Favorites f ON e.event_id = f.event_id 
                                    LEFT JOIN Registrations r ON e.event_id = r.event_id AND r.user_id = ?
                                    LEFT JOIN EventCalendar c ON e.event_id = c.event_id AND c.user_id = ?
                                    WHERE f.user_id = ? 
                                    ORDER BY e.start_datetime ASC";
                            
                            $stmt = $conn->prepare($query);
                            $stmt->bind_param("iii", $userId, $userId, $userId);
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
                                    <div class="event-card">
                                        <h5 class="event-title"><?php echo htmlspecialchars($event['title']); ?></h5>
                                        <div class="event-meta">
                                            <span><i class="bi bi-calendar"></i> <?php echo $startDate->format('F j, Y g:i A'); ?></span>
                                            <span><i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($event['location']); ?></span>
                                            <span><i class="bi bi-people"></i> <?php echo $event['current_registrations']; ?>/<?php echo $event['max_capacity']; ?> registered</span>
                                        </div>
                                        <div class="event-description">
                                            <?php echo htmlspecialchars($event['description']); ?>
                                        </div>
                                        <div class="event-actions">
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
                                                    data-favorite="true">
                                                <i class="bi bi-star-fill"></i>
                                                <span>Remove from Favorites</span>
                                            </button>
                                        </div>
                                    </div>
                                    <?php
                                }
                            } else {
                                ?>
                                <div class="no-favorites">
                                    <i class="bi bi-star" style="font-size: 2em;"></i>
                                    <h4 class="mt-3">No Favorite Events</h4>
                                    <p class="text-muted">You haven't added any events to your favorites yet.</p>
                                    <a href="./events.php" class="btn btn-primary">Browse Events</a>
                                </div>
                                <?php
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
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

            // Handle Calendar button clicks
            $(document).on('click', '.calendar-btn, .btn-outline-danger', function() {
                const eventId = $(this).data('event-id');
                const button = $(this);
                const isRemove = button.hasClass('btn-outline-danger');
                
                button.prop('disabled', true);
                
                $.ajax({
                    url: 'calendar_action.php',
                    type: 'POST',
                    data: {
                        event_id: eventId,
                        action: isRemove ? 'remove' : 'add'
                    },
                    success: function(response) {
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
        });

        function toggleFavorite(button, eventId) {
            const isFavorite = button.getAttribute('data-favorite') === 'true';
            
            // Disable the button while processing
            button.disabled = true;
            
            // Log the request details
            console.log('Sending favorite request:', {
                action: 'removeFavorite',
                eventId: eventId
            });
            
            $.ajax({
                url: 'favorites_action.php',
                type: 'POST',
                dataType: 'json', // Specify that we expect JSON response
                data: {
                    action: 'removeFavorite',  // Since we're on the favorites page, we only remove
                    event_id: eventId
                },
                success: function(data) {
                    console.log('Server response:', data);
                    
                    if (data.status === 'success') {
                        // Remove the event card from the list with animation
                        $(button).closest('.event-card').fadeOut(300, function() {
                            $(this).remove();
                            // Check if there are any events left
                            if ($('.event-card').length === 0) {
                                $('#favoriteEventsList').html(`
                                    <div class="no-favorites">
                                        <i class="bi bi-star" style="font-size: 2em;"></i>
                                        <h4 class="mt-3">No Favorite Events</h4>
                                        <p class="text-muted">You haven't added any events to your favorites yet.</p>
                                        <a href="./events.php" class="btn btn-primary">Browse Events</a>
                                    </div>
                                `);
                            }
                        });
                        
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: data.message || 'Event removed from favorites',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });
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
                    
                    let errorMessage = 'Failed to connect to the server';
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