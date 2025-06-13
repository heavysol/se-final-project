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

try {
    // Check if user is logged in and is a student
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
        sendJsonResponse([
            'status' => 'error',
            'message' => 'Unauthorized access'
        ], 401);
    }

    $userId = $_SESSION['user_id'];

    if (!isset($_GET['action'])) {
        sendJsonResponse([
            'status' => 'error',
            'message' => 'No action specified'
        ], 400);
    }

    switch ($_GET['action']) {
        case 'getDashboardStats':
            // Get registered events count
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM registrations WHERE user_id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $registeredCount = $stmt->get_result()->fetch_assoc()['count'];

            // Get upcoming events count
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM events WHERE start_datetime > NOW()");
            $stmt->execute();
            $upcomingCount = $stmt->get_result()->fetch_assoc()['count'];

            // Get favorite events count
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM favorites WHERE user_id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $favoritesCount = $stmt->get_result()->fetch_assoc()['count'];

            // Get clubs joined count
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM clubmembers WHERE user_id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $clubsCount = $stmt->get_result()->fetch_assoc()['count'];

            sendJsonResponse([
                'status' => 'success',
                'stats' => [
                    'registered' => $registeredCount,
                    'upcoming' => $upcomingCount,
                    'favorites' => $favoritesCount,
                    'clubs' => $clubsCount
                ]
            ]);
            break;

        default:
            sendJsonResponse([
                'status' => 'error',
                'message' => 'Invalid action specified'
            ], 400);
    }

} catch (Exception $e) {
    error_log("Error in dashboard_action.php: " . $e->getMessage());
    sendJsonResponse([
        'status' => 'error',
        'message' => 'An error occurred while processing your request'
    ], 500);
}
?> 