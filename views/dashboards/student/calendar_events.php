<?php
session_start();
require_once '../../../db/config.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

$userId = $_SESSION['user_id'];

try {
    // Query to get events that the student has registered for or added to their calendar
    $query = "SELECT 
        e.*,
        CASE WHEN r.registration_id IS NOT NULL THEN 1 ELSE 0 END as is_registered,
        CASE WHEN ec.calendar_id IS NOT NULL THEN 1 ELSE 0 END as in_calendar,
        CASE WHEN f.favorite_id IS NOT NULL THEN 1 ELSE 0 END as is_favorite
    FROM events e
    LEFT JOIN registrations r ON e.event_id = r.event_id AND r.user_id = ?
    LEFT JOIN eventcalendar ec ON e.event_id = ec.event_id AND ec.user_id = ?
    LEFT JOIN favorites f ON e.event_id = f.event_id AND f.user_id = ?
    WHERE r.registration_id IS NOT NULL OR ec.calendar_id IS NOT NULL
    ORDER BY e.start_datetime ASC";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("iii", $userId, $userId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    $events = [];
    while ($row = $result->fetch_assoc()) {
        // Format the event for FullCalendar
        $event = [
            'id' => $row['event_id'],
            'title' => $row['title'],
            'start' => $row['start_datetime'],
            'end' => $row['end_datetime'],
            'description' => $row['description'],
            'location' => $row['location'],
            'category' => $row['category'],
            'extendedProps' => [
                'category' => $row['category'],
                'is_registered' => $row['is_registered'],
                'in_calendar' => $row['in_calendar'],
                'is_favorite' => $row['is_favorite'],
                'max_capacity' => $row['max_capacity']
            ]
        ];

        // Add different colors based on registration status
        if ($row['is_registered']) {
            $event['backgroundColor'] = '#28a745'; // Green for registered events
            $event['borderColor'] = '#28a745';
        } else {
            $event['backgroundColor'] = '#17a2b8'; // Blue for calendar-only events
            $event['borderColor'] = '#17a2b8';
        }

        $events[] = $event;
    }

    // Return events as JSON
    header('Content-Type: application/json');
    echo json_encode($events);

} catch (Exception $e) {
    error_log("Error in calendar_events.php: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Failed to fetch events']);
}
?> 