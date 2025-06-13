<?php
session_start();

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

// Include database configuration
require_once '../../../db/config.php';

// Get upcoming events with registration, calendar, and favorite status
$query = "SELECT e.*, 
        CASE WHEN r.registration_id IS NOT NULL THEN 1 ELSE 0 END as is_registered,
        CASE WHEN c.calendar_id IS NOT NULL THEN 1 ELSE 0 END as in_calendar,
        CASE WHEN f.favorite_id IS NOT NULL THEN 1 ELSE 0 END as is_favorite,
        (SELECT COUNT(*) FROM registrations WHERE event_id = e.event_id) as current_registrations
        FROM events e 
        LEFT JOIN registrations r ON e.event_id = r.event_id AND r.user_id = ?
        LEFT JOIN eventcalendar c ON e.event_id = c.event_id AND c.user_id = ?
        LEFT JOIN favorites f ON e.event_id = f.event_id AND f.user_id = ?
        WHERE e.end_datetime >= NOW() 
        AND e.status = 'approved'
        ORDER BY e.start_datetime ASC 
        LIMIT 5";

try {
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iii", $_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $events = [];
    while ($row = $result->fetch_assoc()) {
        $startDate = new DateTime($row['start_datetime']);
        $endDate = new DateTime($row['end_datetime']);
        
        $events[] = [
            'id' => $row['event_id'],
            'title' => $row['title'],
            'start_date' => $startDate->format('Y-m-d H:i:s'),
            'end_date' => $endDate->format('Y-m-d H:i:s'),
            'formatted_start_date' => $startDate->format('F j, Y'),
            'formatted_start_time' => $startDate->format('g:i A'),
            'formatted_end_date' => $endDate->format('F j, Y'),
            'formatted_end_time' => $endDate->format('g:i A'),
            'location' => $row['location'],
            'category' => $row['category'],
            'max_capacity' => $row['max_capacity'],
            'current_registrations' => $row['current_registrations'],
            'is_registered' => (bool)$row['is_registered'],
            'in_calendar' => (bool)$row['in_calendar'],
            'is_favorite' => (bool)$row['is_favorite']
        ];
    }
    
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'success',
        'events' => $events
    ]);
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to fetch upcoming events: ' . $e->getMessage()
    ]);
} 