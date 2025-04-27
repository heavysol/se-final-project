<?php
// Prevent any output before headers
ob_start();

session_start();
require_once '../../../db/config.php';

// Enable error reporting but log to file instead of output
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '../../../logs/php_error.log');

// Function to send JSON response
function sendJsonResponse($data) {
    ob_clean();
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}

// Check database connection
if (!$conn) {
    error_log("Database connection failed: " . mysqli_connect_error());
    sendJsonResponse(['status' => 'error', 'message' => 'Database connection failed']);
}

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

try {
    $userId = $_SESSION['user_id'];
    
    // Get registered events count
    $stmt = $conn->prepare("SELECT COUNT(*) as registered_events FROM registrations WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $registeredCount = $stmt->get_result()->fetch_assoc()['registered_events'];
    
    // Get favorite events count
    $stmt = $conn->prepare("SELECT COUNT(*) as favorite_events FROM favorites WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $favoriteCount = $stmt->get_result()->fetch_assoc()['favorite_events'];
    
    // Return the stats
    echo json_encode([
        'status' => 'success',
        'registered_events' => (int)$registeredCount,
        'favorite_events' => (int)$favoriteCount
    ]);
    
} catch (Exception $e) {
    error_log("Error getting dashboard stats: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to get dashboard stats'
    ]);
} 