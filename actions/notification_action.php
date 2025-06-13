<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$response = ['success' => false, 'message' => ''];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'mark_read':
                if (!isset($_POST['notification_id'])) {
                    throw new Exception('Notification ID is required');
                }

                $sql = "UPDATE Notifications SET is_read = 1 WHERE notification_id = ? AND organizer_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ii", $_POST['notification_id'], $_SESSION['user_id']);

                if ($stmt->execute()) {
                    $response['success'] = true;
                    $response['message'] = 'Notification marked as read';
                } else {
                    throw new Exception("Failed to mark notification as read");
                }
                break;

            case 'get_unread_count':
                $sql = "SELECT COUNT(*) as unread_count FROM Notifications WHERE organizer_id = ? AND is_read = 0";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $_SESSION['user_id']);
                $stmt->execute();
                $result = $stmt->get_result();
                $count = $result->fetch_assoc()['unread_count'];

                $response['success'] = true;
                $response['count'] = $count;
                break;

            default:
                throw new Exception('Invalid action');
        }
    } else {
        throw new Exception('Invalid request method or missing action');
    }
} catch (Exception $e) {
    error_log("Error in notification_action.php: " . $e->getMessage());
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
?> 