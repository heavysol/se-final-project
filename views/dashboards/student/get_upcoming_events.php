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
        (SELECT COUNT(*) FROM Registrations WHERE event_id = e.event_id) as current_registrations
        FROM Events e 
        LEFT JOIN Registrations r ON e.event_id = r.event_id AND r.user_id = ?
        LEFT JOIN EventCalendar c ON e.event_id = c.event_id AND c.user_id = ?
        LEFT JOIN Favorites f ON e.event_id = f.event_id AND f.user_id = ?
        WHERE e.start_datetime >= NOW() 
        ORDER BY e.start_datetime ASC 
        LIMIT 5";

try {
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iii", $_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $events = [];
    while ($event = $result->fetch_assoc()) {
        // Format dates
        $event['start_date'] = date('Y-m-d H:i:s', strtotime($event['start_datetime']));
        $event['end_date'] = date('Y-m-d H:i:s', strtotime($event['end_datetime']));
        // Ensure both id and event_id are set for consistency
        $event['id'] = $event['event_id'];
        $events[] = $event;
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