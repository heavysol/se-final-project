<?php
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1); // Temporarily enable error display for debugging
ini_set('log_errors', 1);

// Set JSON header
header('Content-Type: application/json');

// Log the current directory and request data
error_log("Current directory: " . __DIR__);
error_log("POST data: " . print_r($_POST, true));
error_log("SESSION data: " . print_r($_SESSION, true));

// Try to load the config file
$configPath = __DIR__ . '/../../../../db/config.php';
error_log("Attempting to load config from: " . $configPath);

if (!file_exists($configPath)) {
    error_log("Config file not found at: " . $configPath);
    echo json_encode(['status' => 'error', 'message' => 'Configuration file not found']);
    exit();
}

require_once $configPath;

// Check if database connection is established
if (!isset($conn) || !($conn instanceof mysqli)) {
    error_log("Database connection not established");
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit();
}

// Check if user is logged in and is an organizer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'organizer') {
    error_log("Unauthorized access attempt. User ID: " . ($_SESSION['user_id'] ?? 'not set') . ", Role: " . ($_SESSION['role'] ?? 'not set'));
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

// Check if event_id and action are provided
if (!isset($_POST['event_id']) || !isset($_POST['action'])) {
    error_log("Invalid request parameters. POST data: " . print_r($_POST, true));
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit();
}

$eventId = $_POST['event_id'];
$organizerId = $_SESSION['user_id'];

try {
    // Verify that the event belongs to the organizer
    $verifyQuery = "SELECT event_id FROM Events WHERE event_id = ? AND organizer_id = ?";
    $verifyStmt = $conn->prepare($verifyQuery);
    if (!$verifyStmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    $verifyStmt->bind_param("ii", $eventId, $organizerId);
    if (!$verifyStmt->execute()) {
        throw new Exception("Execute failed: " . $verifyStmt->error);
    }
    $verifyResult = $verifyStmt->get_result();
    
    if ($verifyResult->num_rows === 0) {
        error_log("Event not found or unauthorized: event_id=" . $eventId . ", organizer_id=" . $organizerId);
        echo json_encode(['status' => 'error', 'message' => 'Event not found or unauthorized']);
        exit();
    }

    // Get feedback for the event
    $query = "SELECT 
                f.rating,
                f.comments,
                f.created_at,
                u.first_name,
                u.last_name
              FROM Feedback f
              JOIN Users u ON f.user_id = u.user_id
              WHERE f.event_id = ?
              ORDER BY f.created_at DESC";
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("i", $eventId);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    $result = $stmt->get_result();
    
    $feedback = [];
    while ($row = $result->fetch_assoc()) {
        $feedback[] = [
            'rating' => (int)$row['rating'],
            'comments' => htmlspecialchars($row['comments']),
            'created_at' => $row['created_at'],
            'username' => htmlspecialchars($row['first_name'] . ' ' . $row['last_name'])
        ];
    }
    
    echo json_encode([
        'status' => 'success',
        'feedback' => $feedback
    ]);

} catch (Exception $e) {
    error_log("Error in get_feedback.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred while fetching feedback: ' . $e->getMessage()
    ]);
}

if (isset($conn)) {
    $conn->close();
}
?> 