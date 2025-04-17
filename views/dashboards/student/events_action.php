<?php
require_once '../../../db/config.php';

// Initialize database connection
if (!isset($conn) || $conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle search functionality
function searchEvents($searchTerm, $userId) {
    global $conn;
    
    // Split search term into keywords
    $keywords = explode(' ', trim($searchTerm));
    $conditions = [];
    $params = [];
    $types = '';
    
    // Create conditions for each keyword
    foreach ($keywords as $keyword) {
        $keyword = '%' . $conn->real_escape_string($keyword) . '%';
        $conditions[] = "(e.title LIKE ? OR e.description LIKE ? OR e.location LIKE ? OR e.category LIKE ?)";
        $params = array_merge($params, [$keyword, $keyword, $keyword, $keyword]);
        $types .= "ssss";
    }
    
    // Combine all conditions with AND
    $whereClause = implode(' AND ', $conditions);
    
    $query = "SELECT e.*, 
              CASE WHEN r.registration_id IS NOT NULL THEN 1 ELSE 0 END as is_registered,
              CASE WHEN c.calendar_id IS NOT NULL THEN 1 ELSE 0 END as in_calendar
              FROM Events e 
              LEFT JOIN Registrations r ON e.event_id = r.event_id AND r.user_id = ?
              LEFT JOIN EventCalendar c ON e.event_id = c.event_id AND c.user_id = ?
              WHERE {$whereClause}
              AND e.start_datetime >= NOW()
              ORDER BY 
                CASE 
                    WHEN e.title LIKE ? THEN 1
                    WHEN e.category LIKE ? THEN 2
                    WHEN e.location LIKE ? THEN 3
                    ELSE 4
                END,
                e.start_datetime ASC";
    
    // Add user_id parameters
    array_unshift($params, $userId, $userId);
    $types = "ii" . $types;
    
    // Add parameters for ORDER BY clause
    $searchTerm = '%' . $searchTerm . '%';
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
    $types .= "sss";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    
    return $stmt->get_result();
}

// Get upcoming events
function getUpcomingEvents($userId) {
    global $conn;
    
    $query = "SELECT e.*, 
              CASE WHEN r.registration_id IS NOT NULL THEN 1 ELSE 0 END as is_registered,
              CASE WHEN c.calendar_id IS NOT NULL THEN 1 ELSE 0 END as in_calendar
              FROM Events e 
              LEFT JOIN Registrations r ON e.event_id = r.event_id AND r.user_id = ?
              LEFT JOIN EventCalendar c ON e.event_id = c.event_id AND c.user_id = ?
              WHERE e.start_datetime >= NOW() 
              ORDER BY e.start_datetime ASC 
              LIMIT 10";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $userId, $userId);
    $stmt->execute();
    return $stmt->get_result();
}

// Handle AJAX requests
if (isset($_POST['action'])) {
    $response = array();
    
    if ($_POST['action'] === 'search' && isset($_POST['search_term'])) {
        if (!isset($_SESSION['user_id'])) {
            session_start();
        }
        $userId = $_SESSION['user_id'] ?? 0;
        $results = searchEvents($_POST['search_term'], $userId);
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
                'max_capacity' => $row['max_capacity'],
                'is_registered' => (bool)$row['is_registered'],
                'in_calendar' => (bool)$row['in_calendar']
            );
        }
        
        $response['status'] = 'success';
        $response['events'] = $events;
        
        echo json_encode($response);
        exit;
    }
}
?> 