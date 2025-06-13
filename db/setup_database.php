<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
$dbConfig = [
    'host' => 'localhost',
    'username' => 'root',
    'password' => '',
    'database' => 'campuseventmanagement'
];

// Create connection without database
$conn = new mysqli($dbConfig['host'], $dbConfig['username'], $dbConfig['password']);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS `{$dbConfig['database']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci";
if ($conn->query($sql) === TRUE) {
    echo "Database created successfully or already exists\n";
} else {
    die("Error creating database: " . $conn->error);
}

// Select the database
$conn->select_db($dbConfig['database']);

// Import schema
$schema = file_get_contents(__DIR__ . '/CampusEventManagement.sql');
if ($schema === false) {
    die("Error reading schema file");
}

// Execute each statement
$statements = array_filter(array_map('trim', explode(';', $schema)));
foreach ($statements as $statement) {
    if (!empty($statement)) {
        if (!$conn->query($statement)) {
            die("Error executing statement: " . $conn->error);
        }
    }
}

echo "Database setup completed successfully\n";
$conn->close(); 