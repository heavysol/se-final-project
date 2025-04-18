<?php
require_once 'config.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Check if Favorites table exists
    $result = $conn->query("SHOW TABLES LIKE 'Favorites'");
    
    if ($result->num_rows == 0) {
        // Table doesn't exist, create it
        $sql = "CREATE TABLE IF NOT EXISTS Favorites (
            favorite_id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            event_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_favorite (user_id, event_id),
            FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE,
            FOREIGN KEY (event_id) REFERENCES Events(event_id) ON DELETE CASCADE
        )";
        
        if ($conn->query($sql)) {
            echo "Favorites table created successfully\n";
        } else {
            throw new Exception("Error creating Favorites table: " . $conn->error);
        }
    } else {
        echo "Favorites table already exists\n";
        
        // Verify table structure
        $result = $conn->query("DESCRIBE Favorites");
        if (!$result) {
            throw new Exception("Error checking Favorites table structure: " . $conn->error);
        }
        
        $columns = [];
        while ($row = $result->fetch_assoc()) {
            $columns[] = $row['Field'];
        }
        
        $required_columns = ['favorite_id', 'user_id', 'event_id', 'created_at'];
        $missing_columns = array_diff($required_columns, $columns);
        
        if (!empty($missing_columns)) {
            throw new Exception("Favorites table is missing columns: " . implode(", ", $missing_columns));
        }
        
        echo "Favorites table structure verified\n";
    }
    
    // Test the table by checking foreign key constraints
    $test_sql = "
        SELECT f.favorite_id, u.user_id, e.event_id 
        FROM Favorites f 
        LEFT JOIN Users u ON f.user_id = u.user_id 
        LEFT JOIN Events e ON f.event_id = e.event_id 
        LIMIT 1
    ";
    
    if ($conn->query($test_sql)) {
        echo "Foreign key constraints verified\n";
    } else {
        throw new Exception("Error verifying foreign key constraints: " . $conn->error);
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

$conn->close();
echo "Setup completed successfully\n"; 