<?php
// Database connection using MySQLi only

// Get database credentials from environment variables or use defaults
$host = getenv('DB_HOST') ?: 'localhost';
$username = getenv('DB_USERNAME') ?: 'root';
$password = getenv('DB_PASSWORD') ?: 'root';
$database = getenv('DB_NAME') ?: 'CampusEventManagement';

// Enable error reporting in development
if (getenv('APP_ENV') === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// First connect without database
$conn = new mysqli($host, $username, $password);

// Check connection
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    if (getenv('APP_ENV') === 'development') {
        die("Connection failed: " . $conn->connect_error);
    } else {
        die("A database error occurred. Please try again later.");
    }
}

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS $database";
if ($conn->query($sql) === TRUE) {
    // Select the database
    $conn->select_db($database);
} else {
    error_log("Error creating database: " . $conn->error);
    die("Error creating database. Please check the logs.");
}

// Set charset to utf8mb4
$conn->set_charset("utf8mb4");

// Import the schema if tables don't exist
$result = $conn->query("SHOW TABLES LIKE 'users'");
if ($result->num_rows == 0) {
    // Read and execute the schema file
    $schema = file_get_contents(__DIR__ . '/CampusEventManagement.sql');
    if ($schema === false) {
        error_log("Error reading schema file");
        die("Error reading schema file. Please check the logs.");
    }
    
    // Execute each statement
    $statements = array_filter(array_map('trim', explode(';', $schema)));
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            if (!$conn->query($statement)) {
                error_log("Error executing statement: " . $conn->error);
                die("Error setting up database. Please check the logs.");
            }
        }
    }
}
?>