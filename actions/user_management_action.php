<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json");

// Include the config from db folder
include_once("../db/config.php");

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
        $firstName = $_POST['firstName'];
        $lastName = $_POST['lastName'];
        $email = $_POST['email'];
        $role = $_POST['role'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password, role, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
        $stmt->bind_param("sssss", $firstName, $lastName, $email, $password, $role);

        if ($stmt->execute()) {
            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "error", "message" => $conn->error]);
        }

        $stmt->close();
        exit;
    }

    // Delete User
    if ($action === 'delete') {
        $user_id = $_POST['user_id'];

        $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);

        if ($stmt->execute()) {
            echo json_encode(["status" => "deleted"]);
        } else {
            echo json_encode(["status" => "error", "message" => $conn->error]);
        }

        $stmt->close();
        exit;
    }

    // Update User
    if ($action === 'update') {
        $userId = $_POST['user_id'];
        $firstName = $_POST['firstName'];
        $lastName = $_POST['lastName'];
        $email = $_POST['email'];
        $role = $_POST['role'];

        $stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, role = ?, updated_at = NOW() WHERE user_id = ?");
        $stmt->bind_param("ssssi", $firstName, $lastName, $email, $role, $userId);

        if ($stmt->execute()) {
            echo json_encode(["status" => "updated"]);
        } else {
            echo json_encode(["status" => "error", "message" => $conn->error]);
        }

        $stmt->close();
        exit;
    }

    echo json_encode(["status" => "error", "message" => "Unknown action"]);
}

?>
