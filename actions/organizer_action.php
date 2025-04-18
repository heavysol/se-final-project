<?php
session_start();
require_once '../db/config.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in and is an organizer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'organizer') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Get the action from POST request
$action = isset($_POST['action']) ? $_POST['action'] : '';

// Initialize response array
$response = ['success' => false, 'message' => 'Invalid action'];

switch ($action) {
    case 'view':
        if (isset($_POST['event_id'])) {
            $event_id = $_POST['event_id'];
            $stmt = $conn->prepare("SELECT * FROM Events WHERE event_id = ? AND organizer_id = ?");
            $stmt->bind_param("ii", $event_id, $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $event = $result->fetch_assoc();
                $response = [
                    'success' => true,
                    'event' => $event
                ];
            } else {
                $response = ['success' => false, 'message' => 'Event not found'];
            }
        } else {
            $response = ['success' => false, 'message' => 'Event ID is required'];
        }
        break;

    case 'edit':
        if (isset($_POST['event_id'])) {
            $event_id = $_POST['event_id'];
            $title = $_POST['title'];
            $description = $_POST['description'];
            $location = $_POST['location'];
            $category = $_POST['category'];
            $max_capacity = $_POST['max_capacity'];
            $start_datetime = $_POST['start_datetime'];
            $end_datetime = $_POST['end_datetime'];

            $stmt = $conn->prepare("UPDATE Events SET 
                title = ?, 
                description = ?, 
                location = ?, 
                category = ?, 
                max_capacity = ?, 
                start_datetime = ?, 
                end_datetime = ? 
                WHERE event_id = ? AND organizer_id = ?");
            
            $stmt->bind_param("ssssissii", 
                $title, 
                $description, 
                $location, 
                $category, 
                $max_capacity, 
                $start_datetime, 
                $end_datetime, 
                $event_id, 
                $_SESSION['user_id']
            );

            if ($stmt->execute()) {
                $response = ['success' => true, 'message' => 'Event updated successfully'];
            } else {
                $response = ['success' => false, 'message' => 'Failed to update event: ' . $stmt->error];
            }
        } else {
            $response = ['success' => false, 'message' => 'Event ID is required'];
        }
        break;

    case 'delete':
        if (isset($_POST['event_id'])) {
            $event_id = $_POST['event_id'];
            
            // First check if the event belongs to the organizer
            $check_stmt = $conn->prepare("SELECT event_id FROM Events WHERE event_id = ? AND organizer_id = ?");
            $check_stmt->bind_param("ii", $event_id, $_SESSION['user_id']);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows > 0) {
                // Delete the event
                $delete_stmt = $conn->prepare("DELETE FROM Events WHERE event_id = ? AND organizer_id = ?");
                $delete_stmt->bind_param("ii", $event_id, $_SESSION['user_id']);
                
                if ($delete_stmt->execute()) {
                    $response = ['success' => true, 'message' => 'Event deleted successfully'];
                } else {
                    $response = ['success' => false, 'message' => 'Failed to delete event: ' . $delete_stmt->error];
                }
            } else {
                $response = ['success' => false, 'message' => 'Event not found or unauthorized'];
            }
        } else {
            $response = ['success' => false, 'message' => 'Event ID is required'];
        }
        break;

    default:
        $response = ['success' => false, 'message' => 'Invalid action specified'];
        break;
}

// Set header and return JSON response
header('Content-Type: application/json');
echo json_encode($response);
exit();
?> 