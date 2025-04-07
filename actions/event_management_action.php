<?php
include('db/config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if it's an event submission
    if (isset($_POST['title'], $_POST['description'], $_POST['start_datetime'])) {
        // Handle event submission logic
        // Insert into DB with 'pending' status
    }

    // Check if it's an approval/rejection request
    elseif (isset($_POST['event_id'], $_POST['action'])) {
        $event_id = intval($_POST['event_id']);
        $action = $_POST['action'];

        if ($action === 'approve') {
            $status = 'approved';
        } elseif ($action === 'reject') {
            $status = 'rejected';
        } else {
            die("Invalid action.");
        }

        $sql = "UPDATE events SET status = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "si", $status, $event_id);
        
        if (mysqli_stmt_execute($stmt)) {
            header("Location: approval_queue.php?msg=Event successfully $status");
            exit();
        } else {
            echo "Error updating event status.";
        }
    }
}
?>
