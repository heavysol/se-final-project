<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set session timeout (30 minutes)
$timeout = 1800; // 30 minutes in seconds
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
    // Last request was more than 30 minutes ago
    session_unset();     // Unset $_SESSION variable
    session_destroy();   // Destroy session data
    header("Location: ../views/login.php?timeout=1");
    exit();
}
$_SESSION['last_activity'] = time(); // Update last activity time

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['role']);
}

// Function to check if user has specific role
function hasRole($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

// Function to require login
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: ../views/login.php");
        exit();
    }
}

// Function to require specific role
function requireRole($role) {
    if (!hasRole($role)) {
        header("Location: ../views/login.php");
        exit();
    }
}

// Function to get user data
function getUserData() {
    if (!isLoggedIn()) {
        return null;
    }
    
    global $conn;
    $userId = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Function to set flash message
function setFlashMessage($type, $message) {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
}

// Function to get and clear flash message
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

// Function to display flash message
function displayFlashMessage() {
    $message = getFlashMessage();
    if ($message) {
        $type = $message['type'];
        $text = $message['message'];
        echo "<div class='alert alert-$type'>$text</div>";
    }
}
?> 