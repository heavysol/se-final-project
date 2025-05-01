<?php
require_once 'db/config.php';

function updateCompletedEventsAttendance($user_id) {
    global $conn;
    
    // Update attendance status for all completed events
    $update_query = "UPDATE Registrations r
                    JOIN Events e ON r.event_id = e.event_id
                    SET r.attendance_status = 'attended'
                    WHERE r.user_id = ? 
                    AND e.end_datetime < NOW() 
                    AND r.attendance_status = 'pending'";
    
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("i", $user_id);
    return $stmt->execute();
}

// If this file is called directly (for testing)
if (isset($_GET['user_id'])) {
    $user_id = (int)$_GET['user_id'];
    if (updateCompletedEventsAttendance($user_id)) {
        echo "Attendance updated successfully for user $user_id";
    } else {
        echo "Error updating attendance";
    }
}
?> 