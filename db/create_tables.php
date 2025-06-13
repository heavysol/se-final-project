<?php
require_once 'config.php';

// Read and execute the SQL file
$sql = file_get_contents('create_favorites_table.sql');

if ($conn->multi_query($sql)) {
    echo "Favorites table created successfully\n";
} else {
    echo "Error creating table: " . $conn->error . "\n";
}

$conn->close();
?> 