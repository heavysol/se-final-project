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

    // Get recommended events (events with highest registrations that user hasn't registered for)
    $recommendedQuery = "SELECT e.*, 
                        (SELECT COUNT(*) FROM registrations WHERE event_id = e.event_id) as current_registrations
                        FROM events e
                        LEFT JOIN registrations r ON e.event_id = r.event_id AND r.user_id = ?
                        WHERE e.end_datetime >= NOW() 
                        AND e.status = 'approved'
                        AND r.registration_id IS NULL
                        ORDER BY current_registrations DESC
                        LIMIT 3";

    $stmt = $conn->prepare($recommendedQuery);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $recommendedResult = $stmt->get_result();

    while ($event = $recommendedResult->fetch_assoc()) {
        $notifications[] = [
            'type' => 'recommendation',
            'title' => 'Popular Event Alert!',
            'message' => "{$event['title']} has {$event['current_registrations']} registrations. Don't miss out!",
            'event_id' => $event['event_id'],
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }

    // Get today's events that user has registered for
    $todayQuery = "SELECT e.* 
                   FROM events e
                   JOIN registrations r ON e.event_id = r.event_id
                   WHERE r.user_id = ?
                   AND DATE(e.start_datetime) = CURDATE()
                   AND e.status = 'approved'";

    $stmt = $conn->prepare($todayQuery);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $todayResult = $stmt->get_result();

    while ($event = $todayResult->fetch_assoc()) {
        $startTime = new DateTime($event['start_datetime']);
        $notifications[] = [
            'type' => 'reminder',
            'title' => 'Event Today!',
            'message' => "{$event['title']} is happening today at {$startTime->format('g:i A')}",
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