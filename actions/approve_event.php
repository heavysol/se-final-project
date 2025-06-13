<?php
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $event_id = $_POST['event_id'];
    $action = $_POST['action'];

    $status = $action === 'approve' ? 'approved' : 'rejected';

    $stmt = $conn->prepare("UPDATE events SET status = ? WHERE event_id = ?");
    $stmt->bind_param("si", $status, $event_id);

    if ($stmt->execute()) {
        header("Location: approval_queue.php?msg=Event $status");
    } else {
        echo "Error updating status.";
    }
}
?>
