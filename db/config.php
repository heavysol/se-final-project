<?php
$servername = "localhost";
$username = "root"; 
$password = "";  
$dbname = "campuseventmanagement"; 

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// enable error reporting (can be disabled in production)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);


// close the connection
$conn->close();
?>
