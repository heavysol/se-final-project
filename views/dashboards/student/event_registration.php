<?php
require_once '../../../db/config.php';
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log the request details
error_log("Registration request: " . json_encode($_POST));

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['event_id'])) {
    $eventId = (int)$_POST['event_id'];
    $userId = (int)$_SESSION['user_id'];
    $action = isset($_POST['action']) ? $_POST['action'] : 'register';
    
    error_log("Processing registration action: " . json_encode([
        'action' => $action,
        'eventId' => $eventId,
        'userId' => $userId
    ]));
    
    try {
        // First verify the event exists and check capacity
        $checkEventQuery = "SELECT event_id, max_capacity, 
            (SELECT COUNT(*) FROM registrations WHERE event_id = events.event_id) as current_registrations 
            FROM events WHERE event_id = ?";
        $checkEventStmt = $conn->prepare($checkEventQuery);
        $checkEventStmt->bind_param("i", $eventId);
        $checkEventStmt->execute();
        $eventResult = $checkEventStmt->get_result();
        
        if ($eventResult->num_rows === 0) {
            echo json_encode(['status' => 'error', 'message' => 'Event not found']);
            exit;
        }
        
        $eventData = $eventResult->fetch_assoc();
        
        // Check if user is already registered
        $checkRegQuery = "SELECT registration_id FROM registrations WHERE user_id = ? AND event_id = ?";
        $checkRegStmt = $conn->prepare($checkRegQuery);
        $checkRegStmt->bind_param("ii", $userId, $eventId);
        $checkRegStmt->execute();
        $regResult = $checkRegStmt->get_result();
        $isRegistered = $regResult->num_rows > 0;
        
        if ($action === 'register') {
            if ($isRegistered) {
                echo json_encode(['status' => 'error', 'message' => 'You are already registered for this event']);
                exit;
            }
            
            // Check if event is full
            if ($eventData['current_registrations'] >= $eventData['max_capacity']) {
                echo json_encode(['status' => 'error', 'message' => 'Event has reached maximum capacity']);
                exit;
            }
            
            // Register user for event
            $query = "INSERT INTO registrations (user_id, event_id, registration_date) VALUES (?, ?, NOW())";
            $stmt = $conn->prepare($query);
            
            if (!$stmt) {
                error_log("Database prepare failed: " . $conn->error);
                echo json_encode(['status' => 'error', 'message' => 'Database prepare failed: ' . $conn->error]);
                exit;
            }
            
            $stmt->bind_param("ii", $userId, $eventId);
            
            if ($stmt->execute()) {
                // If registering, also add to calendar
                $calendarQuery = "INSERT INTO eventcalendar (user_id, event_id, sync_status) VALUES (?, ?, 'pending')";
                $calendarStmt = $conn->prepare($calendarQuery);
                $calendarStmt->bind_param("ii", $userId, $eventId);
                $calendarStmt->execute();
                
                // Get updated registered events count
                $countStmt = $conn->prepare("SELECT COUNT(*) as count FROM registrations WHERE user_id = ?");
                $countStmt->bind_param("i", $userId);
                $countStmt->execute();
                $registeredCount = $countStmt->get_result()->fetch_assoc()['count'];
                
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Successfully registered for the event',
                    'is_registered' => true,
                    'registered_count' => $registeredCount
                ]);
            } else {
                error_log("Failed to register: " . $stmt->error);
                echo json_encode(['status' => 'error', 'message' => 'Failed to register for event: ' . $stmt->error]);
            }
        } else if ($action === 'unregister') {
            if (!$isRegistered) {
                echo json_encode(['status' => 'error', 'message' => 'You are not registered for this event']);
                exit;
            }
            
            // Unregister user from event
            $query = "DELETE FROM registrations WHERE user_id = ? AND event_id = ?";
            $stmt = $conn->prepare($query);
            
            if (!$stmt) {
                error_log("Database prepare failed: " . $conn->error);
                echo json_encode(['status' => 'error', 'message' => 'Database prepare failed: ' . $conn->error]);
                exit;
            }
            
            $stmt->bind_param("ii", $userId, $eventId);
            
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    // If unregistering, also remove from calendar
                    $calendarQuery = "DELETE FROM eventcalendar WHERE user_id = ? AND event_id = ?";
                    $calendarStmt = $conn->prepare($calendarQuery);
                    $calendarStmt->bind_param("ii", $userId, $eventId);
                    $calendarStmt->execute();
                    
                    // Get updated registered events count
                    $countStmt = $conn->prepare("SELECT COUNT(*) as count FROM registrations WHERE user_id = ?");
                    $countStmt->bind_param("i", $userId);
                    $countStmt->execute();
                    $registeredCount = $countStmt->get_result()->fetch_assoc()['count'];
                    
                    echo json_encode([
                        'status' => 'success',
                        'message' => 'Successfully unregistered from the event',
                        'is_registered' => false,
                        'registered_count' => $registeredCount
                    ]);
                } else {
                    error_log("No rows affected when unregistering");
                    echo json_encode(['status' => 'error', 'message' => 'Failed to unregister: No rows affected']);
                }
            } else {
                error_log("Failed to unregister: " . $stmt->error);
                echo json_encode(['status' => 'error', 'message' => 'Failed to unregister from event: ' . $stmt->error]);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        }
    } catch (Exception $e) {
        error_log("Registration error: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'An error occurred: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
?> 