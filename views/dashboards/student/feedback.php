<?php
session_start();
require_once '../../../db/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ../../login.php');
    exit();
}

$userId = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Feedback - Student Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="../../../assets/css/general-styles.css">
    <link href="../../../assets/css/student-dashboard-styles.css" rel="stylesheet">
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
            <li><a href="./student_dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
            <li><a href="./events.php"><i class="bi bi-calendar-event"></i> Events</a></li>
            <li><a href="./registrations.php"><i class="bi bi-journal-check"></i> My Registrations</a></li>
            <li><a href="./feedback.php" class="active"><i class="bi bi-chat-square-text"></i> My Feedback</a></li>
            <li><a href="./calendar.php"><i class="bi bi-calendar3"></i> Calendar</a></li>
            <li><a href="./favorites.php"><i class="bi bi-star"></i> My Favorites</a></li>
            <li><a href="../../../views/logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <div class="row mb-4">
                <div class="col-12">
                    <div class="dashboard-card">
                        <h4>Event Feedback</h4>
                        <div class="feedback-form">
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
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
    <script>
        $(document).ready(function() {
            // Load completed events for feedback
            function loadCompletedEvents() {
                $.ajax({
                    url: 'feedback_action.php',
                    type: 'POST',
                    data: { action: 'getCompletedEvents' },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            const select = $('#eventSelect');
                            select.empty();
                            select.append('<option value="">Choose an event...</option>');
                            
                            response.events.forEach(function(event) {
                                select.append(`<option value="${event.event_id}">${event.title} (${event.end_date})</option>`);
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

            // Initial load of completed events
            loadCompletedEvents();
        });
    </script>
    <style>
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
</body>
</html> 