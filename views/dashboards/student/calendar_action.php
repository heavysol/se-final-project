<?php
require_once '../../../db/config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Please login to add events to calendar']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['event_id'])) {
    $event_id = $_POST['event_id'];
    $user_id = $_SESSION['user_id'];
    
    // Get event details
    $query = "SELECT * FROM Events WHERE event_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $event = $stmt->get_result()->fetch_assoc();
    
    if ($event) {
        // Add to EventCalendar table if not already added
        $checkQuery = "SELECT * FROM EventCalendar WHERE user_id = ? AND event_id = ?";
        $stmt = $conn->prepare($checkQuery);
        $stmt->bind_param("ii", $user_id, $event_id);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows === 0) {
            $insertQuery = "INSERT INTO EventCalendar (user_id, event_id, sync_status) VALUES (?, ?, 'synced')";
            $stmt = $conn->prepare($insertQuery);
            $stmt->bind_param("ii", $user_id, $event_id);
            $stmt->execute();
        }
        
        // Create ICS file content
        $ics_content = "BEGIN:VCALENDAR\r\n";
        $ics_content .= "VERSION:2.0\r\n";
        $ics_content .= "PRODID:-//Ashesi University//Campus Events//EN\r\n";
        $ics_content .= "CALSCALE:GREGORIAN\r\n";
        $ics_content .= "METHOD:PUBLISH\r\n";
        $ics_content .= "BEGIN:VEVENT\r\n";
        $ics_content .= "UID:" . uniqid() . "\r\n";
        $ics_content .= "DTSTAMP:" . gmdate('Ymd\THis\Z') . "\r\n";
        $ics_content .= "DTSTART:" . date('Ymd\THis', strtotime($event['start_datetime'])) . "\r\n";
        $ics_content .= "DTEND:" . date('Ymd\THis', strtotime($event['end_datetime'])) . "\r\n";
        $ics_content .= "SUMMARY:" . str_replace(",", "\\,", $event['title']) . "\r\n";
        $ics_content .= "DESCRIPTION:" . str_replace(",", "\\,", $event['description']) . "\r\n";
        $ics_content .= "LOCATION:" . str_replace(",", "\\,", $event['location']) . "\r\n";
        $ics_content .= "END:VEVENT\r\n";
        $ics_content .= "END:VCALENDAR\r\n";
        
        // Set headers for file download
        header('Content-Type: text/calendar; charset=utf-8');
        header('Content-Disposition: attachment; filename="event.ics"');
        
        echo $ics_content;
        exit;
    }
}

echo json_encode(['status' => 'error', 'message' => 'Event not found']);
exit;
?> 