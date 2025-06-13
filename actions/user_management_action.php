<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json");

// Include the config from db folder
include_once("../db/config.php");

// Initialize response array
$response = ['success' => false, 'message' => ''];

// Handle GET (fetch users)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Check if we're fetching a single user by ID
    if (isset($_GET['user_id'])) {
        $user_id = $_GET['user_id'];
        $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        echo json_encode($user);
        $stmt->close();
        exit;
    }

    // Fetch all users if no specific user is requested
    $result = $conn->query("SELECT * FROM users ORDER BY created_at DESC");

    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    echo json_encode($users);
    exit;
}

// Handle POST (create, delete, update)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Create User
    if ($action === 'create') {
        try {
            // Validate required fields
            $required_fields = ['firstName', 'lastName', 'email', 'role', 'password'];
            foreach ($required_fields as $field) {
                if (!isset($_POST[$field]) || empty($_POST[$field])) {
                    throw new Exception("Missing required field: " . $field);
                }
            }

            $firstName = trim($_POST['firstName']);
            $lastName = trim($_POST['lastName']);
            $email = trim($_POST['email']);
            $role = trim($_POST['role']);
            $password = $_POST['password'];

            // Validate email format
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Invalid email format");
            }

            // Check if email already exists
            $check_stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
            $check_stmt->bind_param("s", $email);
            $check_stmt->execute();
            if ($check_stmt->get_result()->num_rows > 0) {
                throw new Exception("Email already exists");
            }
            $check_stmt->close();

            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insert new user
            $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password, role, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
            $stmt->bind_param("sssss", $firstName, $lastName, $email, $hashedPassword, $role);

            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'User created successfully';
            } else {
                throw new Exception("Error creating user: " . $conn->error);
            }

            $stmt->close();
        } catch (Exception $e) {
            $response['message'] = $e->getMessage();
        }
    }

    // Delete User
    else if ($action === 'delete') {
        try {
            if (!isset($_POST['user_id'])) {
                throw new Exception("Missing user_id");
            }

            $user_id = $_POST['user_id'];

            // Check if user exists
            $check_stmt = $conn->prepare("SELECT user_id FROM users WHERE user_id = ?");
            $check_stmt->bind_param("i", $user_id);
            $check_stmt->execute();
            if ($check_stmt->get_result()->num_rows === 0) {
                throw new Exception("User not found");
            }
            $check_stmt->close();

            // Delete user
            $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);

            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'User deleted successfully';
            } else {
                throw new Exception("Error deleting user: " . $conn->error);
            }

            $stmt->close();
        } catch (Exception $e) {
            $response['message'] = $e->getMessage();
        }
    }

    // Update User
    else if ($action === 'update') {
        try {
            // Validate required fields
            $required_fields = ['user_id', 'firstName', 'lastName', 'email', 'role'];
            foreach ($required_fields as $field) {
                if (!isset($_POST[$field]) || empty($_POST[$field])) {
                    throw new Exception("Missing required field: " . $field);
                }
            }

            $userId = $_POST['user_id'];
            $firstName = trim($_POST['firstName']);
            $lastName = trim($_POST['lastName']);
            $email = trim($_POST['email']);
            $role = trim($_POST['role']);

            // Validate email format
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Invalid email format");
            }

            // Check if email already exists for another user
            $check_stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ? AND user_id != ?");
            $check_stmt->bind_param("si", $email, $userId);
            $check_stmt->execute();
            if ($check_stmt->get_result()->num_rows > 0) {
                throw new Exception("Email already exists");
            }
            $check_stmt->close();

            // Update user
            $stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, role = ?, updated_at = NOW() WHERE user_id = ?");
            $stmt->bind_param("ssssi", $firstName, $lastName, $email, $role, $userId);

            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'User updated successfully';
            } else {
                throw new Exception("Error updating user: " . $conn->error);
            }

            $stmt->close();
        } catch (Exception $e) {
            $response['message'] = $e->getMessage();
        }
    }

    else {
        $response['message'] = "Invalid action";
    }

    echo json_encode($response);
    exit;
}

echo json_encode($response);
?>
