<?php
include('../../../db/config.php'); // Ensure the path is correct

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['title'], $_POST['description'], $_POST['start_datetime'], $_POST['end_datetime'], $_POST['location'], $_POST['category'], $_POST['max_capacity'])) {
        $title = $_POST['title'];
        $description = $_POST['description'];
        $start_datetime = $_POST['start_datetime'];
        $end_datetime = $_POST['end_datetime'];
        $location = $_POST['location'];
        $category = $_POST['category'];
        $max_capacity = $_POST['max_capacity'];
        $organizer_id = $_SESSION['user_id']; // Assuming the organizer is the logged-in user
        $is_public = 0; // Default to not public
        $status = 'pending'; // Default status

        try {
            $sql = "INSERT INTO events (title, description, start_datetime, end_datetime, location, organizer_id, category, max_capacity, is_public, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssissis", $title, $description, $start_datetime, $end_datetime, $location, $organizer_id, $category, $max_capacity, $is_public, $status);
            $stmt->execute();

            $response['success'] = true;
            $response['message'] = 'Event created successfully!';
        } catch (Exception $e) {
            $response['message'] = 'An error occurred while creating the event: ' . $e->getMessage();
        }
    } else {
        $response['message'] = 'Invalid input data.';
    }
}

echo json_encode($response);
?> 