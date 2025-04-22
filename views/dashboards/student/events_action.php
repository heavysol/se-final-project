<?php
require_once '../../../db/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize database connection
if (!isset($conn) || $conn->connect_error) {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => 'Database connection failed'
    ]);
    exit;
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
              CASE WHEN c.calendar_id IS NOT NULL THEN 1 ELSE 0 END as in_calendar,
              CASE WHEN f.favorite_id IS NOT NULL THEN 1 ELSE 0 END as is_favorite,
              (SELECT COUNT(*) FROM registrations WHERE event_id = e.event_id) as current_registrations
              FROM events e 
              LEFT JOIN registrations r ON e.event_id = r.event_id AND r.user_id = ?
              LEFT JOIN eventcalendar c ON e.event_id = c.event_id AND c.user_id = ?
              LEFT JOIN favorites f ON e.event_id = f.event_id AND f.user_id = ?
              WHERE {$whereClause}
              ORDER BY e.start_datetime ASC";
    
    // Add user_id parameters (for the three LEFT JOINs)
    array_unshift($params, $userId, $userId, $userId);
    $types = "iii" . $types;
    
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
              CASE WHEN c.calendar_id IS NOT NULL THEN 1 ELSE 0 END as in_calendar,
              CASE WHEN f.favorite_id IS NOT NULL THEN 1 ELSE 0 END as is_favorite,
              (SELECT COUNT(*) FROM registrations WHERE event_id = e.event_id) as current_registrations
              FROM events e 
              LEFT JOIN registrations r ON e.event_id = r.event_id AND r.user_id = ?
              LEFT JOIN eventcalendar c ON e.event_id = c.event_id AND c.user_id = ?
              LEFT JOIN favorites f ON e.event_id = f.event_id AND f.user_id = ?
              WHERE e.start_datetime >= NOW() 
              ORDER BY e.start_datetime ASC 
              LIMIT 10";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iii", $userId, $userId, $userId);
    $stmt->execute();
    return $stmt->get_result();
}

// Get all events without any restrictions
function getAllUpcomingEvents($userId) {
    global $conn;
    
    $query = "SELECT e.*, 
              CASE WHEN r.registration_id IS NOT NULL THEN 1 ELSE 0 END as is_registered,
              CASE WHEN c.calendar_id IS NOT NULL THEN 1 ELSE 0 END as in_calendar,
              CASE WHEN f.favorite_id IS NOT NULL THEN 1 ELSE 0 END as is_favorite,
              (SELECT COUNT(*) FROM registrations WHERE event_id = e.event_id) as current_registrations
              FROM events e 
              LEFT JOIN registrations r ON e.event_id = r.event_id AND r.user_id = ?
              LEFT JOIN eventcalendar c ON e.event_id = c.event_id AND c.user_id = ?
              LEFT JOIN favorites f ON e.event_id = f.event_id AND f.user_id = ?
              ORDER BY e.start_datetime ASC";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iii", $userId, $userId, $userId);
    $stmt->execute();
    return $stmt->get_result();
}

// Handle AJAX requests
if (isset($_POST['action'])) {
    $userId = $_SESSION['user_id'] ?? 0;
    
    if ($_POST['action'] === 'search' && isset($_POST['search_term'])) {
        header('Content-Type: application/json');
        
        if (empty($_POST['search_term'])) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Please enter a search term'
            ]);
            exit;
        }
        
        try {
            $results = searchEvents($_POST['search_term'], $userId);
            
            if ($results === false) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'An error occurred while searching for events'
                ]);
                exit;
            }
            
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
                    'current_registrations' => $row['current_registrations'],
                    'is_registered' => (bool)$row['is_registered'],
                    'in_calendar' => (bool)$row['in_calendar'],
                    'is_favorite' => (bool)$row['is_favorite']
                );
            }
            
            if (empty($events)) {
                echo json_encode([
                    'status' => 'no_results',
                    'message' => 'No events found matching your search'
                ]);
                exit;
            }
            
            echo json_encode([
                'status' => 'success',
                'events' => $events,
                'message' => count($events) . ' event(s) found'
            ]);
            exit;
            
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => 'An error occurred while searching'
            ]);
            exit;
        }
    }
    elseif ($_POST['action'] === 'getEvents') {
        $results = getUpcomingEvents($userId);
        $events = array();
        
        while ($row = $results->fetch_assoc()) {
            $events[] = array(
                'event_id' => $row['event_id'],
                'title' => $row['title'],
                'description' => $row['description'],
                'start_datetime' => $row['start_datetime'],
                'end_datetime' => $row['end_datetime'],
                'location' => $row['location'],
                'category' => $row['category'],
                'max_capacity' => $row['max_capacity'],
                'current_registrations' => $row['current_registrations'],
                'is_registered' => (bool)$row['is_registered'],
                'in_calendar' => (bool)$row['in_calendar'],
                'is_favorite' => (bool)$row['is_favorite']
            );
        }
        
        header('Content-Type: application/json');
        echo json_encode(array(
            'status' => 'success',
            'events' => $events
        ));
        exit;
    }
    elseif ($_POST['action'] === 'getAllEvents') {
        $results = getAllUpcomingEvents($userId);
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
                'current_registrations' => $row['current_registrations'],
                'is_registered' => (bool)$row['is_registered'],
                'in_calendar' => (bool)$row['in_calendar'],
                'is_favorite' => (bool)$row['is_favorite']
            );
        }
        
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'events' => $events
        ]);
        exit;
    }
}
?> 
