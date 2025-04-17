<?php
require_once '../../../db/config.php';
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log the request details
error_log("Calendar action request: " . json_encode($_POST));

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['event_id'])) {
    $eventId = (int)$_POST['event_id'];
    $userId = (int)$_SESSION['user_id'];
    $action = isset($_POST['action']) ? $_POST['action'] : 'add';
    
    error_log("Processing calendar action: " . json_encode([
        'action' => $action,
        'eventId' => $eventId,
        'userId' => $userId
    ]));
    
    try {
        // First verify the event exists
        $checkEventQuery = "SELECT event_id FROM Events WHERE event_id = ?";
        $checkEventStmt = $conn->prepare($checkEventQuery);
        $checkEventStmt->bind_param("i", $eventId);
        $checkEventStmt->execute();
        $eventResult = $checkEventStmt->get_result();
        
        if ($eventResult->num_rows === 0) {
            echo json_encode(['status' => 'error', 'message' => 'Event not found']);
            exit;
        }
        
        if ($action === 'add') {
            // Check if event is already in calendar
            $checkQuery = "SELECT calendar_id FROM EventCalendar WHERE user_id = ? AND event_id = ?";
            $checkStmt = $conn->prepare($checkQuery);
            $checkStmt->bind_param("ii", $userId, $eventId);
            $checkStmt->execute();
            $result = $checkStmt->get_result();
            
            if ($result->num_rows > 0) {
                echo json_encode(['status' => 'error', 'message' => 'Event already in calendar']);
                exit;
            }
            
            // Add event to calendar
            $query = "INSERT INTO EventCalendar (user_id, event_id, sync_status) VALUES (?, ?, 'pending')";
            $stmt = $conn->prepare($query);
            
            if (!$stmt) {
                error_log("Database prepare failed: " . $conn->error);
                echo json_encode(['status' => 'error', 'message' => 'Database prepare failed: ' . $conn->error]);
                exit;
            }
            
            $stmt->bind_param("ii", $userId, $eventId);
            
            if ($stmt->execute()) {
                echo json_encode(['status' => 'success', 'message' => 'Event added to calendar']);
            } else {
                error_log("Failed to add event to calendar: " . $stmt->error);
                echo json_encode(['status' => 'error', 'message' => 'Failed to add event to calendar: ' . $stmt->error]);
            }
        } else if ($action === 'remove') {
            // Check if event is in calendar
            $checkQuery = "SELECT calendar_id FROM EventCalendar WHERE user_id = ? AND event_id = ?";
            $checkStmt = $conn->prepare($checkQuery);
            $checkStmt->bind_param("ii", $userId, $eventId);
            $checkStmt->execute();
            $result = $checkStmt->get_result();
            
            if ($result->num_rows === 0) {
                echo json_encode(['status' => 'error', 'message' => 'Event not found in calendar']);
                exit;
            }
            
            // Remove event from calendar
            $query = "DELETE FROM EventCalendar WHERE user_id = ? AND event_id = ?";
            $stmt = $conn->prepare($query);
            
            if (!$stmt) {
                error_log("Database prepare failed: " . $conn->error);
                echo json_encode(['status' => 'error', 'message' => 'Database prepare failed: ' . $conn->error]);
                exit;
            }
            
            $stmt->bind_param("ii", $userId, $eventId);
            
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    echo json_encode(['status' => 'success', 'message' => 'Event removed from calendar']);
                } else {
                    error_log("No rows affected when removing event from calendar");
                    echo json_encode(['status' => 'error', 'message' => 'Failed to remove event from calendar: No rows affected']);
                }
            } else {
                error_log("Failed to remove event from calendar: " . $stmt->error);
                echo json_encode(['status' => 'error', 'message' => 'Failed to remove event from calendar: ' . $stmt->error]);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        }
    } catch (Exception $e) {
        error_log("Calendar action error: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'An error occurred: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
}
?> 