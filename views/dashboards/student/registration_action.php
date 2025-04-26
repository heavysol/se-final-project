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
    $configPath = __DIR__ . '/../../../db/config.php';
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
    $tables = ['users', 'events', 'registrations'];
    foreach ($tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if (!$result || $result->num_rows === 0) {
            throw new Exception("Required table '$table' does not exist");
        }
    }

    // Function to get all registered events for a student
    function getRegisteredEvents($conn, $studentId) {
        try {
            // Validate student ID
            if (empty($studentId)) {
                throw new Exception("Student ID is required");
            }

            // First verify the user exists and is a student
            $stmt = $conn->prepare("SELECT user_id, role FROM users WHERE user_id = ?");
            if (!$stmt) {
                throw new Exception("Failed to prepare user check query: " . $conn->error);
            }

            $stmt->bind_param("i", $studentId);
            if (!$stmt->execute()) {
                throw new Exception("Failed to execute user check: " . $stmt->error);
            }

            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $stmt->close();

            if (!$user) {
                throw new Exception("User not found");
            }

            if ($user['role'] !== 'student') {
                throw new Exception("User is not a student");
            }

            // Check if user has any registrations
            $checkStmt = $conn->prepare("SELECT COUNT(*) as count FROM registrations WHERE user_id = ?");
            if (!$checkStmt) {
                throw new Exception("Failed to prepare registration check: " . $conn->error);
            }

            $checkStmt->bind_param("i", $studentId);
            if (!$checkStmt->execute()) {
                throw new Exception("Failed to check registrations: " . $checkStmt->error);
            }

            $checkResult = $checkStmt->get_result();
            $count = $checkResult->fetch_assoc()['count'];
            $checkStmt->close();

            if ($count === 0) {
                return [
                    'status' => 'success',
                    'events' => [],
                    'message' => 'No registered events found'
                ];
            }

            // Get registered events
            $query = "SELECT 
                        e.event_id,
                        e.title,
                        e.description,
                        e.category,
                        e.location,
                        e.start_datetime,
                        e.end_datetime,
                        e.max_capacity,
                        e.status as event_status,
                        r.registration_date,
                        r.attendance_status,
                        u.first_name as organizer_first_name,
                        u.last_name as organizer_last_name
                    FROM events e
                    INNER JOIN registrations r ON e.event_id = r.event_id
                    LEFT JOIN users u ON e.organizer_id = u.user_id
                    WHERE r.user_id = ?
                    ORDER BY r.registration_date DESC";

            $stmt = $conn->prepare($query);
            if (!$stmt) {
                throw new Exception("Failed to prepare events query: " . $conn->error);
            }

            $stmt->bind_param("i", $studentId);
            if (!$stmt->execute()) {
                throw new Exception("Failed to execute events query: " . $stmt->error);
            }

            $result = $stmt->get_result();
            $events = [];

            while ($row = $result->fetch_assoc()) {
                // Format dates
                $row['start_date'] = date('F j, Y g:i A', strtotime($row['start_datetime']));
                $row['end_date'] = date('F j, Y g:i A', strtotime($row['end_datetime']));
                $row['registration_date'] = date('F j, Y g:i A', strtotime($row['registration_date']));
                
                // Add organizer name
                $row['organizer'] = trim($row['organizer_first_name'] . ' ' . $row['organizer_last_name']);
                
                // Clean up response
                unset($row['start_datetime'], $row['end_datetime']);
                unset($row['organizer_first_name'], $row['organizer_last_name']);
                
                $events[] = $row;
            }

            $stmt->close();

            return [
                'status' => 'success',
                'events' => $events,
                'count' => count($events)
            ];

        } catch (Exception $e) {
            error_log("Error in getRegisteredEvents: " . $e->getMessage());
            throw $e;
        }
    }

    // Verify request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendJsonResponse([
            'status' => 'error',
            'message' => 'Invalid request method'
        ], 405);
    }

    // Check for action parameter
    if (!isset($_POST['action'])) {
        sendJsonResponse([
            'status' => 'error',
            'message' => 'No action specified'
        ], 400);
    }

    // Verify user session
    if (!isset($_SESSION['user_id'])) {
        sendJsonResponse([
            'status' => 'error',
            'message' => 'Please log in to continue'
        ], 401);
    }

    $action = $_POST['action'];
    $userId = $_SESSION['user_id'];

    switch ($action) {
        case 'getRegisteredEvents':
            try {
                $result = getRegisteredEvents($conn, $userId);
                sendJsonResponse($result);
            } catch (Exception $e) {
                sendJsonResponse([
                    'status' => 'error',
                    'message' => $e->getMessage()
                ], 500);
            }
            break;

        case 'cancelRegistration':
            try {
                // Validate event_id
                if (!isset($_POST['event_id']) || !is_numeric($_POST['event_id'])) {
                    throw new Exception('Invalid event ID');
                }
                $eventId = (int)$_POST['event_id'];

                // Check if registration exists
                $checkStmt = $conn->prepare("SELECT registration_id FROM registrations WHERE user_id = ? AND event_id = ?");
                if (!$checkStmt) {
                    throw new Exception("Failed to prepare registration check: " . $conn->error);
                }

                $checkStmt->bind_param("ii", $userId, $eventId);
                $checkStmt->execute();
                $result = $checkStmt->get_result();
                
                if ($result->num_rows === 0) {
                    throw new Exception("Registration not found");
                }
                $checkStmt->close();

                // Delete the registration
                $deleteStmt = $conn->prepare("DELETE FROM registrations WHERE user_id = ? AND event_id = ?");
                if (!$deleteStmt) {
                    throw new Exception("Failed to prepare delete query: " . $conn->error);
                }

                $deleteStmt->bind_param("ii", $userId, $eventId);
                if (!$deleteStmt->execute()) {
                    throw new Exception("Failed to cancel registration: " . $deleteStmt->error);
                }

                if ($deleteStmt->affected_rows === 0) {
                    throw new Exception("Failed to cancel registration");
                }

                // Also remove from calendar if it exists
                $calendarStmt = $conn->prepare("DELETE FROM eventcalendar WHERE user_id = ? AND event_id = ?");
                if ($calendarStmt) {
                    $calendarStmt->bind_param("ii", $userId, $eventId);
                    $calendarStmt->execute();
                    $calendarStmt->close();
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
                ], 500);
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