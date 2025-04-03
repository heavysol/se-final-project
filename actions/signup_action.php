<?php
// Debugging: Check if config.php is included
if (!file_exists("../config.php")) {
    die("Error: config.php not found! Check the file path.");
}
include("../config.php");

echo "Config file included successfully.";


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullName = trim($_POST['fullName']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirmPassword']);
    $role = trim($_POST['role']);

    // Basic validation
    if (empty($fullName) || empty($email) || empty($password) || empty($confirmPassword) || empty($role)) {
        echo "All fields are required.";
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Invalid email format.";
        exit();
    }

    if ($password !== $confirmPassword) {
        echo "Passwords do not match.";
        exit();
    }

    try {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM Users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            echo "Email already registered.";
            $stmt->close();
            $conn->close();
            exit();
        }
        $stmt->close();

        // Hash password securely
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user
        $stmt = $conn->prepare("INSERT INTO Users (fullName, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $fullName, $email, $hashedPassword, $role);
        
        if ($stmt->execute()) {
            echo "success"; // This response is checked in JavaScript
        } else {
            echo "Error: " . $conn->error;
        }
        $stmt->close();
        $conn->close();
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>
