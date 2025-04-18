<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Debug: Log session data
error_log('Session data: ' . print_r($_SESSION, true));

// Fix the path to database.php
require_once __DIR__ . '/../config/database.php';

// Verify database connection
if (!$conn) {
    error_log("Database connection is not available");
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

header('Content-Type: application/json');

// Check if user is logged in and is an organizer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'organizer') {
    error_log('Unauthorized access attempt - Session data: ' . print_r($_SESSION, true));
    echo json_encode(['success' => false, 'message' => 'Unauthorized access - Please log in as an organizer']);
    exit;
}

$response = ['success' => false, 'message' => ''];

try {
    // Debug: Log POST data
    error_log('POST data: ' . print_r($_POST, true));

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                // Validate required fields
                if (empty($_POST['title'])) {
                    throw new Exception('Task title is required');
                }

                // Get organizer_id from session
                $organizer_id = $_SESSION['user_id'];
                error_log('Using organizer_id: ' . $organizer_id);

                // Prepare the SQL statement
                $sql = "INSERT INTO Tasks (organizer_id, title, description, due_date, status) VALUES (?, ?, ?, ?, 'pending')";
                $stmt = $conn->prepare($sql);
                
                if (!$stmt) {
                    throw new Exception("Prepare failed: " . $conn->error);
                }

                // Bind parameters
                $title = trim($_POST['title']);
                $description = isset($_POST['description']) ? trim($_POST['description']) : '';
                $due_date = !empty($_POST['due_date']) ? $_POST['due_date'] : null;
                
                $stmt->bind_param("isss", 
                    $organizer_id,
                    $title,
                    $description,
                    $due_date
                );

                // Execute the statement
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