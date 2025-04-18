<?php
require_once '../../../db/config.php';

echo "<h2>Database Connection Test</h2>";

// Test database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else {
    echo " Database connection successful<br><br>";
}

// Function to check if table exists and show its structure
function checkTable($conn, $tableName) {
    echo "<h3>Checking table: $tableName</h3>";
    
    // Check if table exists
    $result = $conn->query("SHOW TABLES LIKE '$tableName'");
    if ($result->num_rows > 0) {
        echo " Table exists<br>";
        
        // Get table structure
        $structure = $conn->query("DESCRIBE $tableName");
        echo "<strong>Table structure:</strong><br>";
        echo "<pre>";
        while ($row = $structure->fetch_assoc()) {
            print_r($row);
        }
        echo "</pre>";
        
        // Get row count
        $count = $conn->query("SELECT COUNT(*) as count FROM $tableName");
        $count = $count->fetch_assoc()['count'];
        echo "Row count: $count<br><br>";
    } else {
        echo " Table does not exist<br><br>";
    }
}

// Check required tables
$tables = ['Events', 'Registrations', 'Favorites', 'Users'];
foreach ($tables as $table) {
    checkTable($conn, $table);
}

// Test the getUpcomingEvents query directly
echo "<h3>Testing getUpcomingEvents query</h3>";
$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1; // Use 1 as default for testing

$query = "SELECT e.*, 
        CASE WHEN r.registration_id IS NOT NULL THEN 1 ELSE 0 END as is_registered,
        CASE WHEN f.favorite_id IS NOT NULL THEN 1 ELSE 0 END as is_favorite,
        COALESCE((SELECT COUNT(*) FROM Registrations WHERE event_id = e.event_id), 0) as current_registrations
        FROM Events e 
        LEFT JOIN Registrations r ON e.event_id = r.event_id AND r.user_id = ?
        LEFT JOIN Favorites f ON e.event_id = f.event_id AND f.user_id = ?
        WHERE e.start_datetime >= NOW() 
        ORDER BY e.start_datetime ASC";

echo "Query to execute:<br>";
echo "<pre>" . htmlspecialchars($query) . "</pre>";
echo "User ID: $userId<br><br>";

$stmt = $conn->prepare($query);
if ($stmt === false) {
    echo " Prepare failed: " . $conn->error . "<br>";
} else {
    if (!$stmt->bind_param("ii", $userId, $userId)) {
        echo "Binding parameters failed: " . $stmt->error . "<br>";
    } else {
        if (!$stmt->execute()) {
            echo " Execute failed: " . $stmt->error . "<br>";
        } else {
            $result = $stmt->get_result();
            echo " Query executed successfully<br>";
            echo "Number of rows returned: " . $result->num_rows . "<br><br>";
            
            if ($result->num_rows > 0) {
                echo "<strong>Sample data:</strong><br>";
                echo "<pre>";
                print_r($result->fetch_assoc());
                echo "</pre>";
            }
        }
    }
}
?> 