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
    $conn->set_charset("utf8mb4");

} catch (Exception $e) {
    error_log("Database Error: " . $e->getMessage());
    die("A database error occurred. Please try again later.");
}

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