<?php
// Debugging: Check if config.php is included
if (!file_exists("./db/config.php")) {
    die("Error: config.php not found! Check the file path.");
}
include("./db/config.php");

echo "Config file included successfully.";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstName = trim($_POST['first_name']);
    $lastName = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirmPassword']);

    // Basic validation
    if (empty($firstName) || empty($lastName) || empty($email) || empty($password) || empty($confirmPassword)) {
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

    // Determine user role based on the email address
    $role = 'student'; // Default role is student
    if (strpos($email, 'admin') !== false) {
        $role = 'admin';
    } elseif (strpos($email, 'organizer') !== false) {
        $role = 'organizer';
    }

    try {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
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
        $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password, role) 
                                VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $firstName, $lastName, $email, $hashedPassword, $role);

        if ($stmt->execute()) {
            echo "Account created successfully.";
            header("Location: login.php");
            exit();
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
