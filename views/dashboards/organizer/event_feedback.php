<?php
session_start();
require_once '../../../db/config.php';

// Check if user is logged in and is an organizer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'organizer') {
    header('Location: ../../login.php');
    exit();
}

$organizerId = $_SESSION['user_id'];

// Get all events with feedback for this organizer
$query = "SELECT 
            e.event_id,
            e.title,
            e.start_datetime,
            e.end_datetime,
            COUNT(f.feedback_id) as total_feedback,
            AVG(f.rating) as average_rating
          FROM events e
          LEFT JOIN feedback f ON e.event_id = f.event_id
          WHERE e.organizer_id = ?
          GROUP BY e.event_id
          ORDER BY e.start_datetime DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $organizerId);
$stmt->execute();
$result = $stmt->get_result();
$events = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Feedback - Organizer Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="../../../assets/css/general-styles.css">
    <link rel="stylesheet" href="../../../assets/css/dashboard-styles.css">
    <style>
        .logo-container {
            padding: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
        }
        .logo-container h2 {
            color: var(--text-light);
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
            background: var(--primary-color);
            color: var(--text-light);
            width: 250px;
            position: fixed;
            height: 100vh;
            padding-top: 0;
            box-shadow: 4px 0 10px var(--shadow-color);
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
            transition: margin-left 0.3s ease;
        }
        .nav-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .nav-list li a {
            color: var(--text-light);
            text-decoration: none;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            transition: background-color 0.3s ease;
        }
        .nav-list li a:hover {
            background-color: var(--hover-color);
        }
        .nav-list li a.active {
            background-color: var(--active-color);
        }
        .nav-list li a i {
            margin-right: 10px;
            font-size: 1.2rem;
        }
        .logout-divider {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            margin-top: 20px;
        }
        .feedback-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
            margin-bottom: 20px;
        }
        .feedback-card:hover {
            transform: translateY(-5px);
        }
        .rating {
            color: #ffc107;
            font-size: 1.2rem;
        }
        .feedback-comment {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-top: 10px;
        }
        .event-details {
            color: #6c757d;
            font-size: 0.9rem;
        }
        .no-feedback {
            text-align: center;
            padding: 40px;
            color: #6c757d;
        }
        .feedback-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .average-rating {
            font-size: 1.5rem;
            font-weight: bold;
            color: #ffc107;
        }
        .feedback-count {
            color: #6c757d;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo-container">
            <h2>Campus Events</h2>
            <p class="subtitle">Organizer Dashboard</p>
        </div>
        <ul class="nav-list">
            <li>
                <a href="../../../index.php">
                    <i class="bi bi-house-door"></i>
                    <span class="link-name">Home</span>
                </a>
            </li>
            <li>
                <a href="organizer_dashboard.php">
                    <i class="bi bi-speedometer2"></i>
                    <span class="link-name">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="events-organiser.php">
                    <i class="bi bi-calendar-event"></i>
                    <span class="link-name">My Events</span>
                </a>
            </li>
            <li>
                <a href="analytics.php">
                    <i class="bi bi-graph-up"></i>
                    <span class="link-name">Analytics</span>
                </a>
            </li>
            <li>
                <a href="event_feedback.php" class="active">
                    <i class="bi bi-chat-square-text"></i>
                    <span class="link-name">Event Feedback</span>
                </a>
            </li>
            <li class="logout-divider">
                <a href="../../logout.php">
                    <i class="bi bi-box-arrow-right"></i>
                    <span class="link-name">Logout</span>
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
                        <h4>Event Feedback</h4>
                        <?php if (empty($events)): ?>
                            <div class="no-feedback">
                                <i class="bi bi-chat-square-text" style="font-size: 3rem;"></i>
                                <h5>No Events with Feedback Yet</h5>
                                <p>Feedback will appear here once your events receive responses from attendees.</p>
                            </div>
                        <?php else: ?>
                            <div class="row">
                                <?php foreach ($events as $event): ?>
                                    <div class="col-md-6 col-lg-4">
                                        <div class="card feedback-card">
                                            <div class="card-body">
                                                <h5 class="card-title"><?php echo htmlspecialchars($event['title']); ?></h5>
                                                <div class="event-details">
                                                    <p>
                                                        <i class="bi bi-calendar"></i> 
                                                        <?php echo date('M d, Y', strtotime($event['start_datetime'])); ?>
                                                    </p>
                                                </div>
                                                <div class="feedback-header">
                                                    <div>
                                                        <span class="average-rating">
                                                            <?php echo number_format($event['average_rating'] ?? 0, 1); ?>
                                                            <i class="bi bi-star-fill"></i>
                                                        </span>
                                                        <span class="feedback-count">
                                                            (<?php echo $event['total_feedback']; ?> feedback)
                                                        </span>
                                                    </div>
                                                    <button class="btn btn-sm btn-primary view-feedback" 
                                                            data-event-id="<?php echo $event['event_id']; ?>"
                                                            data-event-title="<?php echo htmlspecialchars($event['title']); ?>">
                                                        View Details
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Feedback Details Modal -->
    <div class="modal fade" id="feedbackDetailsModal" tabindex="-1" role="dialog" aria-labelledby="feedbackModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="feedbackModalLabel">Feedback Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h6 id="modalEventTitle" class="mb-4"></h6>
                    <div id="feedbackList"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>

    <script>
    $(document).ready(function() {
        const feedbackModal = new bootstrap.Modal(document.getElementById('feedbackDetailsModal'));

        $('.view-feedback').click(function() {
            const eventId = $(this).data('event-id');
            const eventTitle = $(this).data('event-title');
            
            $('#modalEventTitle').text(eventTitle);
            $('#feedbackList').html('<div class="text-center"><div class="spinner-border" role="status"></div></div>');
            
            // Fetch feedback details
            $.ajax({
                url: 'actions/get_feedback.php',
                type: 'POST',
                data: {
                    action: 'getEventFeedback',
                    event_id: eventId
                },
                dataType: 'json',
                success: function(response) {
                    console.log('Response:', response); // Debug log
                    if (response.status === 'success') {
                        let feedbackHtml = '';
                        if (response.feedback.length === 0) {
                            feedbackHtml = '<div class="text-center text-muted">No feedback available for this event.</div>';
                        } else {
                            response.feedback.forEach(function(feedback) {
                                feedbackHtml += `
                                    <div class="feedback-comment mb-3">
                                        <div class="rating mb-2">
                                            ${'★'.repeat(feedback.rating)}${'☆'.repeat(5 - feedback.rating)}
                                        </div>
                                        <p class="mb-2">${feedback.comments}</p>
                                        <small class="text-muted">
                                            Posted on ${new Date(feedback.created_at).toLocaleDateString()}
                                        </small>
                                    </div>
                                `;
                            });
                        }
                        $('#feedbackList').html(feedbackHtml);
                    } else {
                        $('#feedbackList').html('<div class="text-danger">Error: ' + (response.message || 'Unknown error') + '</div>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', {xhr, status, error}); // Debug log
                    $('#feedbackList').html('<div class="text-danger">Error loading feedback. Please check the console for details.</div>');
                }
            });
            
            feedbackModal.show();
        });
    });
    </script>
</body>
</html> 