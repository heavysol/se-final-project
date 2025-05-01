<?php
session_start();
require_once '../../../db/config.php';
require_once '../../includes/auth.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Function to send JSON response
function sendJsonResponse($data, $statusCode = 200) {
    header('Content-Type: application/json');
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['status' => 'error', 'message' => 'Please log in to submit feedback']);
    exit;
}

// Get user ID
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
                throw new Exception('Invalid rating');
            }

            // Check if event exists and is completed
            $query = "SELECT end_datetime FROM events WHERE event_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $eventId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                throw new Exception('Event not found');
            }

            $event = $result->fetch_assoc();
            if (strtotime($event['end_datetime']) > time()) {
                throw new Exception('Cannot submit feedback for ongoing events');
            }

            // Check if user is registered for the event
            $query = "SELECT attendance_status FROM registrations WHERE event_id = ? AND user_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ii", $eventId, $userId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                throw new Exception('You are not registered for this event');
            }

            // Update attendance status to 'attended' if it's 'pending'
            $registration = $result->fetch_assoc();
            if ($registration['attendance_status'] === 'pending') {
                $updateQuery = "UPDATE registrations SET attendance_status = 'attended' WHERE event_id = ? AND user_id = ?";
                $updateStmt = $conn->prepare($updateQuery);
                $updateStmt->bind_param("ii", $eventId, $userId);
                $updateStmt->execute();
            }

            // Check if feedback already exists
            $query = "SELECT feedback_id FROM feedback WHERE event_id = ? AND user_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ii", $eventId, $userId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                throw new Exception('You have already submitted feedback for this event');
            }

            // Insert feedback
            $query = "INSERT INTO feedback (event_id, user_id, rating, comment, created_at) VALUES (?, ?, ?, ?, NOW())";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("iiis", $eventId, $userId, $rating, $comment);

            if ($stmt->execute()) {
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