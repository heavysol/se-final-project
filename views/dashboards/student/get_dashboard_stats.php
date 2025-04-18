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
    error_log("Unauthorized access attempt - User ID: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'not set') . 
              ", Role: " . (isset($_SESSION['role']) ? $_SESSION['role'] : 'not set'));
    sendJsonResponse(['status' => 'error', 'message' => 'Unauthorized access']);
}

try {
    // Check if required tables exist
    $tables = ['Registrations', 'Events', 'Favorites', 'ClubMembers'];
    foreach ($tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result->num_rows === 0) {
            error_log("Table '$table' does not exist in the database");
            sendJsonResponse(['status' => 'error', 'message' => "Required table '$table' not found"]);
        }
    }

    $stats = [];
    
    // Get registered events count
    $stmt = $conn->prepare("SELECT COUNT(*) as registered_events FROM Registrations WHERE user_id = ?");
    if (!$stmt) {
        throw new Exception("Failed to prepare Registrations query: " . $conn->error);
    }
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $stats['registered_events'] = (int)$stmt->get_result()->fetch_assoc()['registered_events'];
    error_log("Registered events count fetched: " . $stats['registered_events']);

    // Get upcoming events count
    $stmt = $conn->prepare("SELECT COUNT(*) as upcoming_events FROM Events WHERE start_datetime >= NOW()");
    if (!$stmt) {
        throw new Exception("Failed to prepare Events query: " . $conn->error);
    }
    $stmt->execute();
    $stats['upcoming_events'] = (int)$stmt->get_result()->fetch_assoc()['upcoming_events'];
    error_log("Upcoming events count fetched: " . $stats['upcoming_events']);

    // Get favorite events count
    $stmt = $conn->prepare("SELECT COUNT(*) as favorite_events FROM Favorites WHERE user_id = ?");
    if (!$stmt) {
        throw new Exception("Failed to prepare Favorites query: " . $conn->error);
    }
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $stats['favorite_events'] = (int)$stmt->get_result()->fetch_assoc()['favorite_events'];
    error_log("Favorite events count fetched: " . $stats['favorite_events']);

    // Get clubs joined count
    $stmt = $conn->prepare("SELECT COUNT(*) as clubs_joined FROM ClubMembers WHERE user_id = ? AND status = 'active'");
    if (!$stmt) {
        throw new Exception("Failed to prepare ClubMembers query: " . $conn->error);
    }
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $stats['clubs_joined'] = (int)$stmt->get_result()->fetch_assoc()['clubs_joined'];
    error_log("Clubs joined count fetched: " . $stats['clubs_joined']);

    // Log successful stats collection
    error_log("Successfully collected all stats: " . json_encode($stats));

    // Send success response with stats
    sendJsonResponse([
        'status' => 'success',
        'stats' => $stats
    ]);

} catch (Exception $e) {
    error_log("Error in get_dashboard_stats.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    sendJsonResponse([
        'status' => 'error',
        'message' => 'Failed to fetch dashboard stats: ' . $e->getMessage()
    ]);
} 