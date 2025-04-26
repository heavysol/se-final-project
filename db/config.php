<?php
// Database connection using MySQLi only

// Get database credentials from environment variables or use defaults
$host = getenv('DB_HOST') ?: 'localhost';
$username = getenv('DB_USERNAME') ?: 'root';
$password = getenv('DB_PASSWORD') ?: 'Nsateve2@';
$database = getenv('DB_NAME') ?: 'CampusEventManagement';

// Enable error reporting in development
if (getenv('APP_ENV') === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    // Log error but don't expose details in production
    error_log("Database connection failed: " . $conn->connect_error);
    if (getenv('APP_ENV') === 'development') {
        die("Connection failed: " . $conn->connect_error);
    } else {
        die("A database error occurred. Please try again later.");
    }
}

// Set charset to utf8mb4
$conn->set_charset("utf8mb4");
?>