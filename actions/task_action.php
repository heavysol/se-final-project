<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Debug: Log session data
error_log('Session data: ' . print_r($_SESSION, true));

require_once '../db/config.php';

header('Content-Type: application/json');

// Check if user is logged in and is an organizer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'organizer') {
    error_log('Unauthorized access attempt - Session data: ' . print_r($_SESSION, true));
    echo json_encode(['success' => false, 'message' => 'Unauthorized access - Please log in as an organizer']);
    exit;
}

$response = ['success' => false, 'message' => ''];

try {
    // Verify database connection
    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    // Debug: Log POST data
    error_log('POST data: ' . print_r($_POST, true));

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                if (!isset($_POST['title']) || empty($_POST['title'])) {
                    throw new Exception('Task title is required');
                }

                // Debug: Log the user_id being used
                error_log('Using user_id: ' . $_SESSION['user_id']);

                $sql = "INSERT INTO Tasks (organizer_id, title, description, due_date, status) VALUES (?, ?, ?, ?, 'pending')";
                $stmt = $conn->prepare($sql);
                
                if (!$stmt) {
                    throw new Exception("Prepare failed: " . $conn->error);
                }

                // Ensure we're using the correct user_id
                $organizer_id = $_SESSION['user_id'];
                
                $stmt->bind_param("isss", 
                    $organizer_id,
                    $_POST['title'],
                    $_POST['description'] ?? '',
                    $_POST['due_date'] ?? null
                );

                if ($stmt->execute()) {
                    $response['success'] = true;
                    $response['message'] = 'Task added successfully';
                    error_log('Task added successfully for organizer_id: ' . $organizer_id);
                } else {
                    throw new Exception("Execute failed: " . $stmt->error);
                }
                break;

            case 'update_status':
                if (!isset($_POST['task_id']) || !isset($_POST['status'])) {
                    throw new Exception('Task ID and status are required');
                }

                $sql = "UPDATE Tasks SET status = ? WHERE task_id = ? AND organizer_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sii", 
                    $_POST['status'],
                    $_POST['task_id'],
                    $_SESSION['user_id']
                );

                if ($stmt->execute()) {
                    $response['success'] = true;
                    $response['message'] = 'Task status updated successfully';
                } else {
                    throw new Exception("Execute failed: " . $stmt->error);
                }
                break;

            case 'delete':
                if (!isset($_POST['task_id'])) {
                    throw new Exception('Task ID is required');
                }

                $sql = "DELETE FROM Tasks WHERE task_id = ? AND organizer_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ii", 
                    $_POST['task_id'],
                    $_SESSION['user_id']
                );

                if ($stmt->execute()) {
                    $response['success'] = true;
                    $response['message'] = 'Task deleted successfully';
                } else {
                    throw new Exception("Execute failed: " . $stmt->error);
                }
                break;

            default:
                throw new Exception('Invalid action');
        }
    } else {
        throw new Exception('Invalid request method or missing action');
    }
} catch (Exception $e) {
    error_log("Error in task_action.php: " . $e->getMessage());
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?> 