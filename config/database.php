<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
$dbConfig = [
    'host' => 'localhost',
    'username' => 'root',
    'password' => '',
    'database' => 'campuseventmanagement',
    'charset' => 'utf8mb4'
];

// Create connection
try {
    $conn = new mysqli(
        $dbConfig['host'],
        $dbConfig['username'],
        $dbConfig['password'],
        $dbConfig['database']
    );

    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Set charset
    $conn->set_charset($dbConfig['charset']);

    // Log successful connection
    error_log("Database connection established successfully");

} catch (Exception $e) {
    error_log("Database Error: " . $e->getMessage());
    die("A database error occurred. Please try again later.");
}
?> 