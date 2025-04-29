<?php
session_start();
require_once '../../../db/config.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

try {
    $userId = $_SESSION['user_id'];
    $notifications = [];

    // Get recommended events (events with at least 3 registrations that user hasn't registered for)
    $recommendedQuery = "SELECT e.*, 
                        (SELECT COUNT(*) FROM registrations WHERE event_id = e.event_id) as current_registrations,
                        ROUND((SELECT COUNT(*) FROM registrations WHERE event_id = e.event_id) * 100.0 / e.max_capacity, 2) as registration_percentage
                        FROM events e
                        LEFT JOIN registrations r ON e.event_id = r.event_id AND r.user_id = ?
                        WHERE e.end_datetime >= NOW() 
                        AND e.status = 'approved'
                        AND r.registration_id IS NULL
                        AND (SELECT COUNT(*) FROM registrations WHERE event_id = e.event_id) >= 3
                        ORDER BY current_registrations DESC
                        LIMIT 5";

    $stmt = $conn->prepare($recommendedQuery);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $recommendedResult = $stmt->get_result();

    while ($event = $recommendedResult->fetch_assoc()) {
        $notifications[] = [
            'type' => 'recommendation',
            'title' => 'Event Recommendation',
            'message' => "{$event['title']} has {$event['current_registrations']} registrations. Join your peers and register now!",
            'event_id' => $event['event_id'],
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }

    // Get events happening today or tomorrow that user has registered for
    $upcomingQuery = "SELECT e.*, 
                     CASE 
                         WHEN DATE(e.start_datetime) = CURDATE() THEN 'today'
                         WHEN DATE(e.start_datetime) = DATE_ADD(CURDATE(), INTERVAL 1 DAY) THEN 'tomorrow'
                         WHEN DATE(e.end_datetime) = CURDATE() THEN 'today'
                         WHEN DATE(e.end_datetime) = DATE_ADD(CURDATE(), INTERVAL 1 DAY) THEN 'tomorrow'
                     END as event_day
                     FROM events e
                     JOIN registrations r ON e.event_id = r.event_id
                     WHERE r.user_id = ?
                     AND e.status = 'approved'
                     AND (
                         DATE(e.start_datetime) = CURDATE() 
                         OR DATE(e.start_datetime) = DATE_ADD(CURDATE(), INTERVAL 1 DAY)
                         OR DATE(e.end_datetime) = CURDATE()
                         OR DATE(e.end_datetime) = DATE_ADD(CURDATE(), INTERVAL 1 DAY)
                     )
                     ORDER BY e.start_datetime ASC";

    $stmt = $conn->prepare($upcomingQuery);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $upcomingResult = $stmt->get_result();

    while ($event = $upcomingResult->fetch_assoc()) {
        $startTime = new DateTime($event['start_datetime']);
        $endTime = new DateTime($event['end_datetime']);
        
        // Determine if the event is starting or ending today/tomorrow
        $isStartingToday = $startTime->format('Y-m-d') === date('Y-m-d');
        $isStartingTomorrow = $startTime->format('Y-m-d') === date('Y-m-d', strtotime('+1 day'));
        $isEndingToday = $endTime->format('Y-m-d') === date('Y-m-d');
        $isEndingTomorrow = $endTime->format('Y-m-d') === date('Y-m-d', strtotime('+1 day'));

        $timeMessage = '';
        if ($isStartingToday || $isStartingTomorrow) {
            $timeMessage = "starts at " . $startTime->format('g:i A');
        } else if ($isEndingToday || $isEndingTomorrow) {
            $timeMessage = "ends at " . $endTime->format('g:i A');
        }

        $notifications[] = [
            'type' => 'reminder',
            'title' => $event['event_day'] === 'today' ? 'Event Today!' : 'Event Tomorrow!',
            'message' => "{$event['title']} {$timeMessage}",
            'event_id' => $event['event_id'],
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }

    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'success',
        'notifications' => $notifications
    ]);

} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to fetch notifications: ' . $e->getMessage()
    ]);
} 