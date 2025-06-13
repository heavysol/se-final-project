<?php
// Start output buffering
ob_start();

// Enable detailed error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/debug.log');

// Start session
session_start();

// Function to send JSON response
function sendJsonResponse($data, $statusCode = 200) {
    if (ob_get_length()) ob_clean();
    header('Content-Type: application/json');
    http_response_code($statusCode);
    echo json_encode($data);
    exit;
}

try {
    // Log the attempt
    error_log("Starting registration_action.php");
    error_log("Session data: " . print_r($_SESSION, true));
    error_log("POST data: " . print_r($_POST, true));

    // Include database configuration
    $configPath = __DIR__ . '/../../../../db/config.php';
    if (!file_exists($configPath)) {
        throw new Exception("Config file not found at: " . $configPath);
    }
    require_once $configPath;

    // Verify database connection
    if (!isset($conn)) {
        throw new Exception("Database connection variable not set in config.php");
    }

    if (!($conn instanceof mysqli)) {
        throw new Exception("Invalid database connection type: " . gettype($conn));
    }

    // Test database connection
    if (!$conn->ping()) {
        throw new Exception("Database connection failed: " . $conn->error);
    }

    // Verify required tables exist
    $tables = ['users', 'events', 'registrations', 'feedback'];
    foreach ($tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if (!$result || $result->num_rows === 0) {
            throw new Exception("Required table '$table' does not exist");
        }
    }

    // Verify user session
    if (!isset($_SESSION['user_id'])) {
        sendJsonResponse([
            'status' => 'error',
            'message' => 'Please log in to continue'
        ], 401);
    }

    // Check for action parameter
    if (!isset($_POST['action'])) {
        sendJsonResponse([
            'status' => 'error',
            'message' => 'No action specified'
        ], 400);
    }

    $action = $_POST['action'];
    $userId = $_SESSION['user_id'];

    switch ($action) {
        case 'getRegisteredEvents':
            try {
                // Get all registered events with their status and feedback info
                $query = "SELECT 
                            e.event_id,
                            e.title,
                            e.category,
                            e.location,
                            DATE_FORMAT(e.start_datetime, '%Y-%m-%d %H:%i') as start_datetime,
                            DATE_FORMAT(e.end_datetime, '%Y-%m-%d %H:%i') as end_datetime,
                            DATE_FORMAT(r.registration_date, '%Y-%m-%d %H:%i') as registration_date,
                            r.attendance_status,
                            f.rating,
                            f.comments,
                            CASE 
                                WHEN e.end_datetime < NOW() THEN 'completed'
                                WHEN e.start_datetime > NOW() THEN 'upcoming'
                                ELSE 'ongoing'
                            END as event_status
                        FROM registrations r
                        INNER JOIN events e ON r.event_id = e.event_id
                        LEFT JOIN feedback f ON e.event_id = f.event_id AND f.user_id = r.user_id
                        WHERE r.user_id = ?
                        ORDER BY e.start_datetime DESC";

                error_log("Executing query: " . $query);
                error_log("With user_id: " . $userId);

                $stmt = $conn->prepare($query);
                if (!$stmt) {
                    throw new Exception("Failed to prepare query: " . $conn->error);
                }

                $stmt->bind_param("i", $userId);
                if (!$stmt->execute()) {
                    throw new Exception("Failed to execute query: " . $stmt->error);
                }

                $result = $stmt->get_result();
                $events = [];

                while ($row = $result->fetch_assoc()) {
                    // Add feedback button status
                    $row['can_give_feedback'] = ($row['event_status'] === 'completed' && !$row['rating']);
                    
                    // Format dates for display
                    $row['start_date'] = date('F j, Y', strtotime($row['start_datetime']));
                    $row['start_time'] = date('g:i A', strtotime($row['start_datetime']));
                    $row['end_date'] = date('F j, Y', strtotime($row['end_datetime']));
                    $row['end_time'] = date('g:i A', strtotime($row['end_datetime']));
                    
                    $events[] = $row;
                }

                $stmt->close();

                error_log("Found " . count($events) . " events");

                sendJsonResponse([
                    'status' => 'success',
                    'events' => $events
                ]);
            } catch (Exception $e) {
                error_log("Error in getRegisteredEvents: " . $e->getMessage());
                sendJsonResponse([
                    'status' => 'error',
                    'message' => 'Failed to fetch registered events: ' . $e->getMessage()
                ], 500);
            }
            break;

        case 'cancelRegistration':
            try {
                if (!isset($_POST['event_id'])) {
                    throw new Exception('Event ID is required');
                }

                $eventId = (int)$_POST['event_id'];

                // Check if the event exists and user is registered
                $checkQuery = "SELECT 1 FROM registrations WHERE event_id = ? AND user_id = ?";
                $checkStmt = $conn->prepare($checkQuery);
                if (!$checkStmt) {
                    throw new Exception("Failed to prepare check query: " . $conn->error);
                }

                $checkStmt->bind_param("ii", $eventId, $userId);
                if (!$checkStmt->execute()) {
                    throw new Exception("Failed to execute check query: " . $checkStmt->error);
                }
                
                if ($checkStmt->get_result()->num_rows === 0) {
                    throw new Exception('You are not registered for this event');
                }

                // Delete the registration
                $deleteQuery = "DELETE FROM registrations WHERE event_id = ? AND user_id = ?";
                $deleteStmt = $conn->prepare($deleteQuery);
                if (!$deleteStmt) {
                    throw new Exception("Failed to prepare delete query: " . $conn->error);
                }

                $deleteStmt->bind_param("ii", $eventId, $userId);
                if (!$deleteStmt->execute()) {
                    throw new Exception("Failed to execute delete query: " . $deleteStmt->error);
                }
                
                sendJsonResponse([
                    'status' => 'success',
                    'message' => 'Registration cancelled successfully'
                ]);
            } catch (Exception $e) {
                error_log("Error in cancelRegistration: " . $e->getMessage());
                sendJsonResponse([
                    'status' => 'error',
                    'message' => $e->getMessage()
                ], 400);
            }
            break;

        default:
            sendJsonResponse([
                'status' => 'error',
                'message' => 'Invalid action specified'
            ], 400);
    }

} catch (Exception $e) {
    error_log("Critical error in registration_action.php: " . $e->getMessage());
    sendJsonResponse([
        'status' => 'error',
        'message' => 'An unexpected error occurred'
    ], 500);
}

// Close database connection
if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}
?> 