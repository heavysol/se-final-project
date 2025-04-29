<?php
session_start();
require_once '../../../db/config.php';

// Function to send JSON response
function sendJsonResponse($data, $statusCode = 200) {
    header('Content-Type: application/json');
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    sendJsonResponse([
        'status' => 'error',
        'message' => 'Unauthorized access'
    ], 401);
}

$userId = $_SESSION['user_id'];

// Check for action parameter
if (!isset($_POST['action'])) {
    sendJsonResponse([
        'status' => 'error',
        'message' => 'No action specified'
    ], 400);
}

switch ($_POST['action']) {
    case 'getCompletedEvents':
        try {
            // Get completed events that the student has registered for
            $query = "SELECT e.event_id, e.title, e.end_datetime, 
                     DATE_FORMAT(e.end_datetime, '%M %d, %Y') as end_date
                     FROM events e
                     INNER JOIN registrations r ON e.event_id = r.event_id
                     WHERE r.user_id = ?
                     AND e.end_datetime < NOW()
                     AND r.attendance_status = 'attended'
                     AND NOT EXISTS (
                         SELECT 1 FROM feedback f 
                         WHERE f.event_id = e.event_id 
                         AND f.user_id = ?
                     )
                     ORDER BY e.end_datetime DESC";
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ii", $userId, $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $events = [];
            while ($row = $result->fetch_assoc()) {
                $events[] = $row;
            }
            
            sendJsonResponse([
                'status' => 'success',
                'events' => $events
            ]);
        } catch (Exception $e) {
            sendJsonResponse([
                'status' => 'error',
                'message' => 'Failed to fetch completed events: ' . $e->getMessage()
            ], 500);
        }
        break;

    case 'submitFeedback':
        try {
            // Validate input
            if (!isset($_POST['event_id']) || !isset($_POST['rating']) || !isset($_POST['comment'])) {
                throw new Exception('Missing required fields');
            }

            $eventId = (int)$_POST['event_id'];
            $rating = (int)$_POST['rating'];
            $comment = trim($_POST['comment']);

            // Validate rating
            if ($rating < 1 || $rating > 5) {
                throw new Exception('Invalid rating value');
            }

            // Check if the event is completed and user was registered
            $checkQuery = "SELECT 1 FROM registrations r
                          INNER JOIN events e ON r.event_id = e.event_id
                          WHERE r.user_id = ? AND r.event_id = ?
                          AND r.attendance_status = 'complete'";
            
            $checkStmt = $conn->prepare($checkQuery);
            $checkStmt->bind_param("ii", $userId, $eventId);
            $checkStmt->execute();
            
            if ($checkStmt->get_result()->num_rows === 0) {
                throw new Exception('Invalid event or event not completed');
            }

            // Check if feedback already exists
            $checkFeedbackQuery = "SELECT 1 FROM feedback WHERE user_id = ? AND event_id = ?";
            $checkFeedbackStmt = $conn->prepare($checkFeedbackQuery);
            $checkFeedbackStmt->bind_param("ii", $userId, $eventId);
            $checkFeedbackStmt->execute();
            
            if ($checkFeedbackStmt->get_result()->num_rows > 0) {
                throw new Exception('Feedback already submitted for this event');
            }

            // Insert feedback
            $insertQuery = "INSERT INTO feedback (user_id, event_id, rating, comment) VALUES (?, ?, ?, ?)";
            $insertStmt = $conn->prepare($insertQuery);
            $insertStmt->bind_param("iiis", $userId, $eventId, $rating, $comment);
            
            if ($insertStmt->execute()) {
                sendJsonResponse([
                    'status' => 'success',
                    'message' => 'Feedback submitted successfully'
                ]);
            } else {
                throw new Exception('Failed to submit feedback');
            }
        } catch (Exception $e) {
            sendJsonResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
        break;

    default:
        sendJsonResponse([
            'status' => 'error',
            'message' => 'Invalid action'
        ], 400);
} 