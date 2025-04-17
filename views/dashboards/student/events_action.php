<?php
require_once '../../../db/config.php';

// We already have $conn from config.php, so we don't need to create a new connection
if (!isset($conn) || $conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle search functionality
function searchEvents($searchTerm) {
    global $conn;
    $searchTerm = '%' . $conn->real_escape_string($searchTerm) . '%';
    
    $query = "SELECT * FROM events 
              WHERE (title LIKE ? 
              OR description LIKE ? 
              OR location LIKE ?)
              AND is_public = 1 
              AND start_datetime >= NOW()
              ORDER BY start_datetime ASC";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
    $stmt->execute();
    
    return $stmt->get_result();
}

// Get upcoming events
function getUpcomingEvents() {
    global $conn;
    
    $query = "SELECT * FROM events 
              WHERE start_datetime >= NOW() 
              AND is_public = 1
              ORDER BY start_datetime ASC 
              LIMIT 10";
              
    $result = $conn->query($query);
    return $result;
}

// Handle AJAX requests
if (isset($_POST['action'])) {
    $response = array();
    
    if ($_POST['action'] === 'search' && isset($_POST['search_term'])) {
        $results = searchEvents($_POST['search_term']);
        $events = array();
        
        while ($row = $results->fetch_assoc()) {
            $events[] = array(
                'id' => $row['event_id'],
                'title' => $row['title'],
                'description' => $row['description'],
                'start_date' => $row['start_datetime'],
                'end_date' => $row['end_datetime'],
                'venue' => $row['location'],
                'category' => $row['category'],
                'max_capacity' => $row['max_capacity']
            );
        }
        
        $response['status'] = 'success';
        $response['events'] = $events;
    }
    
    echo json_encode($response);
    exit;
}
?> 