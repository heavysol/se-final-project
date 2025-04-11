<?php
include '../db/config.php';
$query = "SELECT * FROM events WHERE status = 'pending'";
$result = $pdo->query($query);

while ($row = $result->fetch_assoc()) {
    echo "<div class='card mb-2'>
            <div class='card-body'>
                <h5>{$row['title']}</h5>
                <p>{$row['description']}</p>
                <p><strong>Category:</strong> {$row['category']}</p>
                <p><strong>Start:</strong> {$row['start_datetime']} | <strong>End:</strong> {$row['end_datetime']}</p>
                <form method='post' action='event_management_action.php'>
                    <input type='hidden' name='event_id' value='{$row['event_id']}'>
                    <button class='btn btn-success btn-sm' name='action' value='approve'>Approve</button>
                    <button class='btn btn-danger btn-sm' name='action' value='reject'>Reject</button>
                </form>
            </div>
          </div>";
}
?> 
