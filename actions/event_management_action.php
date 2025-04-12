<?php
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
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'view') {
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
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add debug logging
    error_log('POST data received: ' . print_r($_POST, true));
    
    if (isset($_POST['action'], $_POST['event_id'])) {
        $event_id = $_POST['event_id'];
        
        switch ($_POST['action']) {
            case 'delete':
                try {
                    // First check if the event exists
                    $check_stmt = $conn->prepare("SELECT event_id FROM events WHERE event_id = ?");
                    $check_stmt->bind_param("i", $event_id);
                    $check_stmt->execute();
                    $result = $check_stmt->get_result();
                    
                    if ($result->num_rows === 0) {
                        throw new Exception("Event not found");
                    }
                    
                    // Delete the event
                    $delete_stmt = $conn->prepare("DELETE FROM events WHERE event_id = ?");
                    $delete_stmt->bind_param("i", $event_id);
                    
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
                try {
                    // First check if the event exists and is pending
                    $check_stmt = $conn->prepare("SELECT status FROM events WHERE event_id = ?");
                    $check_stmt->bind_param("i", $event_id);
                    $check_stmt->execute();
                    $result = $check_stmt->get_result();
                    
                    if ($result->num_rows === 0) {
                        throw new Exception("Event not found");
                    }
                    
                    $event = $result->fetch_assoc();
                    if ($event['status'] !== 'pending') {
                        throw new Exception("Event is not in pending status");
                    }
                    
                    // Update the event status to approved
                    $update_stmt = $conn->prepare("UPDATE events SET status = 'approved' WHERE event_id = ?");
                    $update_stmt->bind_param("i", $event_id);
                    
                    if ($update_stmt->execute()) {
                        $response['success'] = true;
                        $response['message'] = 'Event approved successfully';
                    } else {
                        throw new Exception("Error updating event status: " . $conn->error);
                    }
                    
                    $check_stmt->close();
                    $update_stmt->close();
                } catch (Exception $e) {
                    $response['message'] = $e->getMessage();
                }
                break;

            case 'reject':
                try {
                    // First check if the event exists and is pending
                    $check_stmt = $conn->prepare("SELECT status FROM events WHERE event_id = ?");
                    $check_stmt->bind_param("i", $event_id);
                    $check_stmt->execute();
                    $result = $check_stmt->get_result();
                    
                    if ($result->num_rows === 0) {
                        throw new Exception("Event not found");
                    }
                    
                    $event = $result->fetch_assoc();
                    if ($event['status'] !== 'pending') {
                        throw new Exception("Event is not in pending status");
                    }
                    
                    // Update the event status to rejected
                    $update_stmt = $conn->prepare("UPDATE events SET status = 'rejected' WHERE event_id = ?");
                    $update_stmt->bind_param("i", $event_id);
                    
                    if ($update_stmt->execute()) {
                        $response['success'] = true;
                        $response['message'] = 'Event rejected successfully';
                    } else {
                        throw new Exception("Error updating event status: " . $conn->error);
                    }
                    
                    $check_stmt->close();
                    $update_stmt->close();
                } catch (Exception $e) {
                    $response['message'] = $e->getMessage();
                }
                break;

            default:
                $response['message'] = 'Invalid action';
                break;
        }
    } else {
        $response['message'] = 'Missing required parameters';
    }
    
    // Add debug logging
    error_log('Response: ' . print_r($response, true));
    
    echo json_encode($response);
    exit;
    }

    // Check if it's an approval/rejection request
    elseif (isset($_POST['event_id'], $_POST['action'])) {
        $event_id = intval($_POST['event_id']);
        $action = $_POST['action'];

        if ($action === 'approve') {
            $status = 'approved';
        } elseif ($action === 'reject') {
            $status = 'rejected';
        } else {
        $response['message'] = 'Invalid action.';
        echo json_encode($response);
        exit;
        }

    try {
        $sql = "UPDATE events SET status = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $status, $event_id);
        $stmt->execute();

        $response['success'] = true;
        $response['message'] = "Event successfully $status.";
    } catch (Exception $e) {
        $response['message'] = 'An error occurred: ' . $e->getMessage();
    }
        } else {
    $response['message'] = 'Invalid input data.';
        }

echo json_encode($response);
?>
