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
    case 'create':
        if (isset($_POST['title']) && isset($_POST['description']) && isset($_POST['start_datetime']) && 
            isset($_POST['end_datetime']) && isset($_POST['location']) && isset($_POST['category']) && 
            isset($_POST['max_capacity'])) {
            
            $title = $_POST['title'];
            $description = $_POST['description'];
            $start_datetime = $_POST['start_datetime'];
            $end_datetime = $_POST['end_datetime'];
            $location = $_POST['location'];
            $category = $_POST['category'];
            $max_capacity = $_POST['max_capacity'];
            $organizer_id = $_SESSION['user_id'];

            $stmt = $conn->prepare("INSERT INTO events (title, description, start_datetime, end_datetime, 
                                  location, category, max_capacity, organizer_id, status) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
            
            $stmt->bind_param("ssssssii", 
                $title, 
                $description, 
                $start_datetime, 
                $end_datetime, 
                $location, 
                $category, 
                $max_capacity, 
                $organizer_id
            );

            if ($stmt->execute()) {
                $response = ['success' => true, 'message' => 'Event created successfully'];
            } else {
                $response = ['success' => false, 'message' => 'Failed to create event: ' . $stmt->error];
            }
        } else {
            $response = ['success' => false, 'message' => 'Missing required fields'];
        }
        break;

    case 'view':
        if (isset($_POST['event_id'])) {
            $event_id = $_POST['event_id'];
            $stmt = $conn->prepare("SELECT * FROM events WHERE event_id = ? AND organizer_id = ?");
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

            $stmt = $conn->prepare("UPDATE events SET 
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
            $check_stmt = $conn->prepare("SELECT event_id FROM events WHERE event_id = ? AND organizer_id = ?");
            $check_stmt->bind_param("ii", $event_id, $_SESSION['user_id']);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows > 0) {
                // Start transaction
                $conn->begin_transaction();

                try {
                    // Delete related records first
                    $tables = [
                        'notifications',
                        'registrations',
                        'eventcalendar',
                        'favorites',
                        'feedback',
                        'eventanalytics'
                    ];

                    foreach ($tables as $table) {
                        $delete_related = $conn->prepare("DELETE FROM $table WHERE event_id = ?");
                        $delete_related->bind_param("i", $event_id);
                        
                        if (!$delete_related->execute()) {
                            throw new Exception("Error deleting related records from $table: " . $delete_related->error);
                        }
                        $delete_related->close();
                    }
                    
                    // Then delete the event
                    $delete_stmt = $conn->prepare("DELETE FROM events WHERE event_id = ? AND organizer_id = ?");
                    $delete_stmt->bind_param("ii", $event_id, $_SESSION['user_id']);
                    
                    if (!$delete_stmt->execute()) {
                        throw new Exception("Error deleting event: " . $delete_stmt->error);
                    }

                    // Commit transaction
                    $conn->commit();
                    
                    $response = ['success' => true, 'message' => 'Event deleted successfully'];
                } catch (Exception $e) {
                    // Rollback transaction on error
                    $conn->rollback();
                    $response = ['success' => false, 'message' => $e->getMessage()];
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