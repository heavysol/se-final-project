<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include('../db/config.php');

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $response['message'] = 'Unauthorized access';
    echo json_encode($response);
    exit;
}

// Handle GET requests (View)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['action']) && $_GET['action'] === 'view') {
        $event_id = $_GET['event_id'];
        
        $stmt = $conn->prepare("SELECT * FROM events WHERE event_id = ?");
        $stmt->bind_param("i", $event_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($event = $result->fetch_assoc()) {
            $response['success'] = true;
            $response['event'] = $event;
        } else {
            $response['message'] = 'Event not found';
        }
        
        echo json_encode($response);
        exit;
    } else {
        // Fetch all events with organizer information
        $query = "SELECT e.*, u.first_name, u.last_name 
                 FROM events e 
                 JOIN users u ON e.organizer_id = u.user_id 
                 ORDER BY e.start_datetime DESC";
        $result = $conn->query($query);
        
        $events = [];
        while ($row = $result->fetch_assoc()) {
            $events[] = $row;
        }
        
        echo json_encode($events);
        exit;
    }
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add debug logging
    error_log('POST data received: ' . print_r($_POST, true));
    
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                try {
                    // Validate required fields
                    $required_fields = ['title', 'description', 'start_datetime', 'end_datetime', 'location', 'category', 'max_capacity', 'organizer_id'];
                    foreach ($required_fields as $field) {
                        if (!isset($_POST[$field]) || empty($_POST[$field])) {
                            throw new Exception("Missing required field: " . $field);
                        }
                    }

                    // Prepare the SQL statement
                    $sql = "INSERT INTO events (title, description, start_datetime, end_datetime, location, category, max_capacity, organizer_id, status, created_at, updated_at) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW(), NOW())";
                    
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ssssssii", 
                        $_POST['title'],
                        $_POST['description'],
                        $_POST['start_datetime'],
                        $_POST['end_datetime'],
                        $_POST['location'],
                        $_POST['category'],
                        $_POST['max_capacity'],
                        $_POST['organizer_id']
                    );
                    
                    if ($stmt->execute()) {
                        $response['success'] = true;
                        $response['message'] = 'Event created successfully';
                    } else {
                        throw new Exception("Error creating event: " . $stmt->error);
                    }
                    
                    $stmt->close();
                } catch (Exception $e) {
                    $response['message'] = $e->getMessage();
                }
                break;

            case 'delete':
                if (!isset($_POST['event_id'])) {
                    $response['message'] = 'Missing event_id';
                    break;
                }
                try {
                    // First check if the event exists
                    $check_stmt = $conn->prepare("SELECT event_id FROM events WHERE event_id = ?");
                    $check_stmt->bind_param("i", $_POST['event_id']);
                    $check_stmt->execute();
                    $result = $check_stmt->get_result();
                    
                    if ($result->num_rows === 0) {
                        throw new Exception("Event not found");
                    }
                    
                    // Delete the event
                    $delete_stmt = $conn->prepare("DELETE FROM events WHERE event_id = ?");
                    $delete_stmt->bind_param("i", $_POST['event_id']);
                    
                    if ($delete_stmt->execute()) {
                        $response['success'] = true;
                        $response['message'] = 'Event deleted successfully';
                    } else {
                        throw new Exception("Error deleting event: " . $conn->error);
                    }
                    
                    $check_stmt->close();
                    $delete_stmt->close();
                } catch (Exception $e) {
                    $response['message'] = $e->getMessage();
                }
                break;

            case 'approve':
                if (!isset($_POST['event_id'])) {
                    $response['message'] = 'Missing event_id';
                    break;
                }
                try {
                    // First check if the event exists and is pending
                    $check_stmt = $conn->prepare("SELECT status FROM events WHERE event_id = ?");
                    $check_stmt->bind_param("i", $_POST['event_id']);
                    $check_stmt->execute();
                    $result = $check_stmt->get_result();
                    
                    if ($result->num_rows === 0) {
                        throw new Exception("Event not found");
                    }
                    
                    $event = $result->fetch_assoc();
                    if ($event['status'] !== 'pending') {
                        throw new Exception("Event is not in pending status");
                    }
                    
                    // Start transaction
                    $conn->begin_transaction();

                    try {
                        // Update event status
                        $sql = "UPDATE events SET status = 'approved' WHERE event_id = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $_POST['event_id']);
                        
                        if (!$stmt->execute()) {
                            throw new Exception("Failed to update event status");
                        }

                        // Get event details for notification
                        $sql = "SELECT organizer_id, title FROM events WHERE event_id = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $_POST['event_id']);
                        $stmt->execute();
                        $event = $stmt->get_result()->fetch_assoc();

                        // Create notification
                        $sql = "INSERT INTO Notifications (organizer_id, event_id, title, message) 
                                VALUES (?, ?, 'Event Approved', ?)";
                        $stmt = $conn->prepare($sql);
                        $message = "Your event '{$event['title']}' has been approved and is now live.";
                        $stmt->bind_param("iis", $event['organizer_id'], $_POST['event_id'], $message);
                        
                        if (!$stmt->execute()) {
                            throw new Exception("Failed to create notification");
                        }

                        // Commit transaction
                        $conn->commit();

                        $response['success'] = true;
                        $response['message'] = 'Event approved successfully';
                    } catch (Exception $e) {
                        // Rollback transaction on error
                        $conn->rollback();
                        throw $e;
                    }
                } catch (Exception $e) {
                    $response['message'] = $e->getMessage();
                }
                break;

            case 'reject':
                if (!isset($_POST['event_id'])) {
                    $response['message'] = 'Missing event_id';
                    break;
                }
                try {
                    // First check if the event exists and is pending
                    $check_stmt = $conn->prepare("SELECT status FROM events WHERE event_id = ?");
                    $check_stmt->bind_param("i", $_POST['event_id']);
                    $check_stmt->execute();
                    $result = $check_stmt->get_result();
                    
                    if ($result->num_rows === 0) {
                        throw new Exception("Event not found");
                    }
                    
                    $event = $result->fetch_assoc();
                    if ($event['status'] !== 'pending') {
                        throw new Exception("Event is not in pending status");
                    }
                    
                    // Start transaction
                    $conn->begin_transaction();

                    try {
                        // Update event status
                        $sql = "UPDATE events SET status = 'rejected' WHERE event_id = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $_POST['event_id']);
                        
                        if (!$stmt->execute()) {
                            throw new Exception("Failed to update event status");
                        }

                        // Get event details for notification
                        $sql = "SELECT organizer_id, title FROM events WHERE event_id = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $_POST['event_id']);
                        $stmt->execute();
                        $event = $stmt->get_result()->fetch_assoc();

                        // Create notification
                        $sql = "INSERT INTO Notifications (organizer_id, event_id, title, message) 
                                VALUES (?, ?, 'Event Rejected', ?)";
                        $stmt = $conn->prepare($sql);
                        $message = "Your event '{$event['title']}' has been rejected. Please review the event details and submit again.";
                        $stmt->bind_param("iis", $event['organizer_id'], $_POST['event_id'], $message);
                        
                        if (!$stmt->execute()) {
                            throw new Exception("Failed to create notification");
                        }

                        // Commit transaction
                        $conn->commit();

                        $response['success'] = true;
                        $response['message'] = 'Event rejected successfully';
                    } catch (Exception $e) {
                        // Rollback transaction on error
                        $conn->rollback();
                        throw $e;
                    }
                } catch (Exception $e) {
                    $response['message'] = $e->getMessage();
                }
                break;

            default:
                $response['message'] = 'Invalid action';
                break;
        }
    } else {
        $response['message'] = 'Missing action parameter';
    }
    
    // Add debug logging
    error_log('Response: ' . print_r($response, true));
    
    echo json_encode($response);
    exit;
}

echo json_encode($response);
?>
