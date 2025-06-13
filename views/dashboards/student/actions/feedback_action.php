<?php
// Start output buffering
ob_start();

// Enable detailed error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/debug.log');

// Start session
session_start();

// Function to send JSON response
function sendResponse($status, $message, $data = null) {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => $status,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

// Function to log errors
function logError($message, $data = null) {
    $logMessage = date('Y-m-d H:i:s') . " - " . $message;
    if ($data) {
        $logMessage .= " - Data: " . json_encode($data);
    }
    error_log($logMessage . "\n", 3, __DIR__ . '/../../../../logs/error.log');
}

try {
    // Log the attempt
    error_log("Starting feedback_action.php");
    error_log("Session data: " . print_r($_SESSION, true));
    error_log("POST data: " . print_r($_POST, true));

    // Include database configuration
    $configPath = __DIR__ . '/../../../../db/config.php';
    if (!file_exists($configPath)) {
        error_log("Config file not found at: " . $configPath);
        sendResponse('error', 'Database configuration not found');
    }
    require_once $configPath;

    // Verify database connection
    if (!isset($conn)) {
        error_log("Database connection variable not set in config.php");
        sendResponse('error', 'Database connection not established');
    }

    if (!($conn instanceof mysqli)) {
        error_log("Invalid database connection type: " . gettype($conn));
        sendResponse('error', 'Invalid database connection');
    }

    // Test database connection
    if (!$conn->ping()) {
        error_log("Database connection failed: " . $conn->error);
        sendResponse('error', 'Database connection failed: ' . $conn->error);
    }

    // Verify user session
    if (!isset($_SESSION['user_id'])) {
        sendResponse('error', 'You must be logged in to submit feedback');
    }

    // Check if it's a POST request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendResponse('error', 'Invalid request method');
    }

    // Get and validate action
    $action = $_POST['action'] ?? '';
    if ($action !== 'submitFeedback') {
        sendResponse('error', 'Invalid action');
    }

    // Get and validate required fields
    $eventId = $_POST['event_id'] ?? '';
    $rating = $_POST['rating'] ?? '';
    $comment = $_POST['comment'] ?? '';

    error_log("Received feedback data: " . json_encode([
        'event_id' => $eventId,
        'rating' => $rating,
        'comment' => $comment,
        'user_id' => $_SESSION['user_id'] ?? 'not set'
    ]));

    if (empty($eventId) || empty($rating) || empty($comment)) {
        error_log("Missing required fields: " . json_encode([
            'event_id_empty' => empty($eventId),
            'rating_empty' => empty($rating),
            'comment_empty' => empty($comment)
        ]));
        sendResponse('error', 'All fields are required');
    }

    // Validate rating
    if (!is_numeric($rating) || $rating < 1 || $rating > 5) {
        sendResponse('error', 'Rating must be between 1 and 5');
    }

    $userId = $_SESSION['user_id'];

    // Start transaction
    $conn->begin_transaction();

    try {
        // Check if event exists and is completed
        $checkEventQuery = "SELECT end_datetime FROM events WHERE event_id = ?";
        $stmt = $conn->prepare($checkEventQuery);
        if (!$stmt) {
            throw new Exception("Error preparing event check query: " . $conn->error);
        }
        $stmt->bind_param("i", $eventId);
        if (!$stmt->execute()) {
            throw new Exception("Error executing event check query: " . $stmt->error);
        }
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Event not found");
        }
        
        $event = $result->fetch_assoc();
        if (strtotime($event['end_datetime']) > time()) {
            throw new Exception("Cannot submit feedback for ongoing or upcoming events");
        }
        
        // Check if user is registered for the event
        $checkRegistrationQuery = "SELECT registration_id FROM registrations WHERE event_id = ? AND user_id = ?";
        $stmt = $conn->prepare($checkRegistrationQuery);
        if (!$stmt) {
            throw new Exception("Error preparing registration check query: " . $conn->error);
        }
        $stmt->bind_param("ii", $eventId, $userId);
        if (!$stmt->execute()) {
            throw new Exception("Error executing registration check query: " . $stmt->error);
        }
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("You are not registered for this event");
        }
        
        // Check if feedback already exists
        $checkFeedbackQuery = "SELECT feedback_id FROM feedback WHERE event_id = ? AND user_id = ?";
        $stmt = $conn->prepare($checkFeedbackQuery);
        if (!$stmt) {
            throw new Exception("Error preparing feedback check query: " . $conn->error);
        }
        $stmt->bind_param("ii", $eventId, $userId);
        if (!$stmt->execute()) {
            throw new Exception("Error executing feedback check query: " . $stmt->error);
        }
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            throw new Exception("You have already submitted feedback for this event");
        }
        
        // Insert feedback
        $insertQuery = "INSERT INTO feedback (event_id, user_id, rating, comments, created_at) VALUES (?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($insertQuery);
        if (!$stmt) {
            throw new Exception("Error preparing feedback insert query: " . $conn->error);
        }
        $stmt->bind_param("iiis", $eventId, $userId, $rating, $comment);
        if (!$stmt->execute()) {
            throw new Exception("Error executing feedback insert query: " . $stmt->error);
        }
        
        // Update attendance status to 'attended'
        $updateAttendanceQuery = "UPDATE registrations SET attendance_status = 'attended' WHERE event_id = ? AND user_id = ?";
        $stmt = $conn->prepare($updateAttendanceQuery);
        if (!$stmt) {
            throw new Exception("Error preparing attendance update query: " . $conn->error);
        }
        $stmt->bind_param("ii", $eventId, $userId);
        if (!$stmt->execute()) {
            throw new Exception("Error executing attendance update query: " . $stmt->error);
        }
        
        // Commit transaction
        $conn->commit();
        
        sendResponse('success', 'Feedback submitted successfully');
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        logError($e->getMessage(), [
            'user_id' => $userId,
            'event_id' => $eventId,
            'rating' => $rating,
            'comment' => $comment,
            'error' => $e->getMessage(),
            'sql_error' => $conn->error
        ]);
        sendResponse('error', $e->getMessage());
    }

} catch (Exception $e) {
    error_log("Critical error in feedback_action.php: " . $e->getMessage());
    sendResponse('error', 'An unexpected error occurred: ' . $e->getMessage());
}

// Close database connection
if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}
?> 