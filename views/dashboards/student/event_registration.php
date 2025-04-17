<?php
require_once '../../../db/config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Please login to register for events']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = array();
    
    if (isset($_POST['event_id'])) {
        $event_id = $_POST['event_id'];
        $user_id = $_SESSION['user_id'];
        
        // Check if already registered
        $checkQuery = "SELECT * FROM Registrations 
                      WHERE event_id = ? AND user_id = ?";
        $stmt = $conn->prepare($checkQuery);
        $stmt->bind_param("ii", $event_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $response = [
                'status' => 'error',
                'message' => 'You are already registered for this event'
            ];
        } else {
            // Check event capacity
            $capacityQuery = "SELECT e.max_capacity, COUNT(r.registration_id) as current_registrations 
                            FROM Events e 
                            LEFT JOIN Registrations r ON e.event_id = r.event_id 
                            WHERE e.event_id = ? 
                            GROUP BY e.event_id";
            $stmt = $conn->prepare($capacityQuery);
            $stmt->bind_param("i", $event_id);
            $stmt->execute();
            $capacityResult = $stmt->get_result()->fetch_assoc();
            
            if ($capacityResult && $capacityResult['current_registrations'] < $capacityResult['max_capacity']) {
                // Register the user
                $registerQuery = "INSERT INTO Registrations (user_id, event_id, registration_date, attendance_status) 
                                VALUES (?, ?, NOW(), 'pending')";
                $stmt = $conn->prepare($registerQuery);
                $stmt->bind_param("ii", $user_id, $event_id);
                
                if ($stmt->execute()) {
                    $response = [
                        'status' => 'success',
                        'message' => 'Successfully registered for the event'
                    ];
                } else {
                    $response = [
                        'status' => 'error',
                        'message' => 'Error registering for the event'
                    ];
                }
            } else {
                $response = [
                    'status' => 'error',
                    'message' => 'Event has reached maximum capacity'
                ];
            }
        }
    } else {
        $response = [
            'status' => 'error',
            'message' => 'Event ID not provided'
        ];
    }
    
    echo json_encode($response);
    exit;
}
?> 