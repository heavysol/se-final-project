<?php
$servername = "localhost";
$username = "root";  // your database username
$password = "";      // your database password
$dbname = "your_database_name";  // your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to ensure proper handling of special characters
$conn->set_charset("utf8mb4");
?> 