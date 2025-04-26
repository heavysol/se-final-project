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
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    sendJsonResponse(['status' => 'error', 'message' => 'Database connection failed']);
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    error_log("User not logged in");
    sendJsonResponse(['status' => 'error', 'message' => 'User not logged in']);
}

$userId = $_SESSION['user_id'];
$response = ['status' => 'error', 'message' => 'Invalid request'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['event_id'])) {
        $eventId = intval($_POST['event_id']);
        $action = $_POST['action'];

        // Log the request
        error_log("Favorites action request - Action: " . $action . ", Event ID: " . $eventId . ", User ID: " . $userId);

        try {
            // Validate event exists
            $checkEvent = $conn->prepare("SELECT event_id FROM events WHERE event_id = ?");
            if (!$checkEvent) {
                throw new Exception("Failed to prepare event check query: " . $conn->error);
            }
            
            $checkEvent->bind_param("i", $eventId);
            if (!$checkEvent->execute()) {
                throw new Exception("Failed to execute event check query: " . $checkEvent->error);
            }
            
            $eventResult = $checkEvent->get_result();
            if ($eventResult->num_rows === 0) {
                throw new Exception("Event not found");
            }

            // Check if already favorited
            $checkFav = $conn->prepare("SELECT favorite_id FROM favorites WHERE user_id = ? AND event_id = ?");
            if (!$checkFav) {
                throw new Exception("Failed to prepare favorites check query: " . $conn->error);
            }
            
            $checkFav->bind_param("ii", $userId, $eventId);
            if (!$checkFav->execute()) {
                throw new Exception("Failed to execute favorites check query: " . $checkFav->error);
            }
            
            $favResult = $checkFav->get_result();
            $isFavorited = $favResult->num_rows > 0;

            if ($action === 'addFavorite') {
                if ($isFavorited) {
                    $response = ['status' => 'error', 'message' => 'Event is already in favorites'];
                } else {
                    $stmt = $conn->prepare("INSERT INTO Favorites (user_id, event_id) VALUES (?, ?)");
                    if (!$stmt) {
                        throw new Exception("Failed to prepare insert query: " . $conn->error);
                    }
                    
                    $stmt->bind_param("ii", $userId, $eventId);
                    if (!$stmt->execute()) {
                        throw new Exception("Failed to add favorite: " . $stmt->error);
                    }
                    
                    $response = ['status' => 'success', 'message' => 'Event added to favorites'];
                    error_log("Successfully added event $eventId to favorites for user $userId");
                }
            } 
            elseif ($action === 'removeFavorite') {
                if (!$isFavorited) {
                    $response = ['status' => 'error', 'message' => 'Event is not in favorites'];
                } else {
                    $stmt = $conn->prepare("DELETE FROM favorites WHERE user_id = ? AND event_id = ?");
                    if (!$stmt) {
                        throw new Exception("Failed to prepare delete query: " . $conn->error);
                    }
                    
                    $stmt->bind_param("ii", $userId, $eventId);
                    if (!$stmt->execute()) {
                        throw new Exception("Failed to remove favorite: " . $stmt->error);
                    }
                    
                    if ($stmt->affected_rows > 0) {
                        $response = ['status' => 'success', 'message' => 'Event removed from favorites'];
                        error_log("Successfully removed event $eventId from favorites for user $userId");
                    } else {
                        throw new Exception("No rows affected when removing favorite");
                    }
                }
            }
        } catch (Exception $e) {
            error_log("Error in favorites action: " . $e->getMessage());
            $response = ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
} 
elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'getFavorites') {
    $query = "SELECT e.*, 
              CASE WHEN r.registration_id IS NOT NULL THEN 1 ELSE 0 END as is_registered,
              1 as is_favorite
              FROM events e 
              INNER JOIN favorites f ON e.event_id = f.event_id 
              LEFT JOIN registrations r ON e.event_id = r.event_id AND r.user_id = ?
              WHERE f.user_id = ?
              ORDER BY e.start_datetime ASC";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $userId, $userId);
    
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $favorites = [];
        while ($row = $result->fetch_assoc()) {
            $favorites[] = $row;
        }
        
        $response = [
            'status' => 'success',
            'favorites' => $favorites
        ];
    } else {
        $response = ['status' => 'error', 'message' => 'Failed to fetch favorites'];
    }
}

// Send the final response
sendJsonResponse($response);
?> 