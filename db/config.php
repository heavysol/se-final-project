<?php
// Database connection using MySQLi only

$host = 'localhost';
$username = 'root';
$password = 'root';
$database = 'CampusEventManagement';

$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>