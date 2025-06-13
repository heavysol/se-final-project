<?php

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define test environment
define('TEST_ENV', true);

// Mock session functions if they don't exist
if (!function_exists('session_start')) {
    function session_start() {
        return true;
    }
}

// Load Composer's autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Set up test database connection
$GLOBALS['config'] = [
    'host' => 'localhost',
    'username' => 'root',
    'password' => '',
    'database' => 'CampusEventManagement'
];

// Create test database if it doesn't exist
function setupTestDatabase() {
    global $config;
    $conn = new mysqli(
        $config['host'],
        $config['username'],
        $config['password']
    );
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    $sql = "CREATE DATABASE IF NOT EXISTS " . $config['database'];
    if ($conn->query($sql) === TRUE) {
        $conn->select_db($config['database']);
        return $conn;
    } else {
        die("Error creating database: " . $conn->error);
    }
}

// Clean up test database
function cleanupTestDatabase($conn) {
    $sql = "DROP DATABASE IF EXISTS " . $GLOBALS['config']['database'];
    $conn->query($sql);
    $conn->close();
} 