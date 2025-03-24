<?php
session_start();
require_once 'db_connection.php';

// Check if user is logged in and has admin privileges
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Function to get all events
function getAllEvents($conn) {
    $sql = "SELECT e.*, o.name as organizer_name 
            FROM events e 
            LEFT JOIN organizations o ON e.organizer_id = o.id 
            ORDER BY e.event_date DESC";
    $result = $conn->query($sql);
    
    $events = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $events[] = $row;
        }
    }
    return $events;
}

// Function to get event details by ID
function getEventById($conn, $eventId) {
    $sql = "SELECT e.*, o.name as organizer_name 
            FROM events e 
            LEFT JOIN organizations o ON e.organizer_id = o.id 
            WHERE e.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $eventId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    return null;
}

// Function to create a new event
function createEvent($conn, $data) {
    $sql = "INSERT INTO events (title, description, event_date, event_time, location, 
            max_attendees, organizer_id, category, image_url, registration_deadline) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssiisss", 
        $data['title'], 
        $data['description'], 
        $data['event_date'], 
        $data['event_time'], 
        $data['location'], 
        $data['max_attendees'], 
        $data['organizer_id'], 
        $data['category'], 
        $data['image_url'], 
        $data['registration_deadline']
    );
    
    if ($stmt->execute()) {
        return $conn->insert_id;
    }
    return false;
}

// Function to update an existing event
function updateEvent($conn, $data, $eventId) {
    $sql = "UPDATE events SET 
            title = ?, 
            description = ?, 
            event_date = ?, 
            event_time = ?, 
            location = ?, 
            max_attendees = ?, 
            organizer_id = ?, 
            category = ?, 
            image_url = ?, 
            registration_deadline = ? 
            WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssiisssi", 
        $data['title'], 
        $data['description'], 
        $data['event_date'], 
        $data['event_time'], 
        $data['location'], 
        $data['max_attendees'], 
        $data['organizer_id'], 
        $data['category'], 
        $data['image_url'], 
        $data['registration_deadline'],
        $eventId
    );
    
    return $stmt->execute();
}

// Function to delete an event
function deleteEvent($conn, $eventId) {
    // First delete all registrations for this event
    $sql = "DELETE FROM registrations WHERE event_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $eventId);
    $stmt->execute();
    
    // Then delete the event
    $sql = "DELETE FROM events WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $eventId);
    return $stmt->execute();
}

// Function to get event registrations
function getEventRegistrations($conn, $eventId) {
    $sql = "SELECT r.*, u.email, u.first_name, u.last_name 
            FROM registrations r 
            JOIN users u ON r.user_id = u.id 
            WHERE r.event_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $eventId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $registrations = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $registrations[] = $row;
        }
    }
    return $registrations;
}

// Function to send reminder emails
function sendEventReminders($conn, $eventId) {
    $sql = "SELECT e.title, e.event_date, e.event_time, e.location, u.email, u.first_name 
            FROM registrations r 
            JOIN events e ON r.event_id = e.id 
            JOIN users u ON r.user_id = u.id 
            WHERE r.event_id = ? AND r.attendance_status = 'registered'";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $eventId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $successful = 0;
    $failed = 0;
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $to = $row['email'];
            $subject = "Reminder: " . $row['title'] . " is Tomorrow!";
            $message = "Hello " . $row['first_name'] . ",\n\n";
            $message .= "This is a reminder that you've registered for " . $row['title'] . ".\n";
            $message .= "Date: " . $row['event_date'] . "\n";
            $message .= "Time: " . $row['event_time'] . "\n";
            $message .= "Location: " . $row['location'] . "\n\n";
            $message .= "We look forward to seeing you there!\n\n";
            $message .= "Best regards,\nAshesi Events Team";
            
            $headers = "From: noreply@ashesi.edu.gh";
            
            if (mail($to, $subject, $message, $headers)) {
                $successful++;
            } else {
                $failed++;
            }
        }
    }
    
    return ["successful" => $successful, "failed" => $failed];
}

// Function to generate QR code for event check-in
function generateEventQRCode($eventId) {
    // Using Google Charts API to generate QR code
    $url = "https://ashesi-events.com/check-in.php?event=" . $eventId;
    $qrCodeUrl = "https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=" . urlencode($url);
    
    return $qrCodeUrl;
}

// Function to check in attendees
function checkInAttendee($conn, $registrationId) {
    $sql = "UPDATE registrations SET 
            attendance_status = 'attended', 
            check_in_time = NOW() 
            WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $registrationId);
    
    return $stmt->execute();
}

// Function to get events by category
function getEventsByCategory($conn, $category) {
    $sql = "SELECT e.*, o.name as organizer_name 
            FROM events e 
            LEFT JOIN organizations o ON e.organizer_id = o.id 
            WHERE e.category = ? 
            ORDER BY e.event_date DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $category);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $events = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $events[] = $row;
        }
    }
    return $events;
}

// Function to get upcoming events
function getUpcomingEvents($conn) {
    $today = date("Y-m-d");
    $sql = "SELECT e.*, o.name as organizer_name 
            FROM events e 
            LEFT JOIN organizations o ON e.organizer_id = o.id 
            WHERE e.event_date >= ? 
            ORDER BY e.event_date ASC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $today);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $events = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $events[] = $row;
        }
    }
    return $events;
}

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        // Create event
        if ($_POST['action'] === 'create') {
            $eventData = [
                'title' => $_POST['title'],
                'description' => $_POST['description'],
                'event_date' => $_POST['event_date'],
                'event_time' => $_POST['event_time'],
                'location' => $_POST['location'],
                'max_attendees' => $_POST['max_attendees'],
                'organizer_id' => $_POST['organizer_id'],
                'category' => $_POST['category'],
                'image_url' => $_POST['image_url'],
                'registration_deadline' => $_POST['registration_deadline']
            ];
            
            if (createEvent($conn, $eventData)) {
                $_SESSION['success_message'] = "Event created successfully!";
            } else {
                $_SESSION['error_message'] = "Failed to create event. Please try again.";
            }
            header('Location: admin_events.php');
            exit();
        }
        
        // Update event
        if ($_POST['action'] === 'update' && isset($_POST['event_id'])) {
            $eventData = [
                'title' => $_POST['title'],
                'description' => $_POST['description'],
                'event_date' => $_POST['event_date'],
                'event_time' => $_POST['event_time'],
                'location' => $_POST['location'],
                'max_attendees' => $_POST['max_attendees'],
                'organizer_id' => $_POST['organizer_id'],
                'category' => $_POST['category'],
                'image_url' => $_POST['image_url'],
                'registration_deadline' => $_POST['registration_deadline']
            ];
            
            if (updateEvent($conn, $eventData, $_POST['event_id'])) {
                $_SESSION['success_message'] = "Event updated successfully!";
            } else {
                $_SESSION['error_message'] = "Failed to update event. Please try again.";
            }
            header('Location: admin_events.php');
            exit();
        }
        
        // Delete event
        if ($_POST['action'] === 'delete' && isset($_POST['event_id'])) {
            if (deleteEvent($conn, $_POST['event_id'])) {
                $_SESSION['success_message'] = "Event deleted successfully!";
            } else {
                $_SESSION['error_message'] = "Failed to delete event. Please try again.";
            }
            header('Location: admin_events.php');
            exit();
        }
        
        // Send reminders
        if ($_POST['action'] === 'send_reminders' && isset($_POST['event_id'])) {
            $result = sendEventReminders($conn, $_POST['event_id']);
            $_SESSION['success_message'] = "Reminders sent: " . $result['successful'] . " successful, " . $result['failed'] . " failed.";
            header('Location: admin_event_details.php?id=' . $_POST['event_id']);
            exit();
        }
    }
}

// Handle GET requests
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Generate QR code
    if (isset($_GET['action']) && $_GET['action'] === 'generate_qr' && isset($_GET['event_id'])) {
        $qrCodeUrl = generateEventQRCode($_GET['event_id']);
        echo json_encode(['qr_code_url' => $qrCodeUrl]);
        exit();
    }
    
    // Check in attendee
    if (isset($_GET['action']) && $_GET['action'] === 'check_in' && isset($_GET['registration_id'])) {
        if (checkInAttendee($conn, $_GET['registration_id'])) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to check in attendee.']);
        }
        exit();
    }
}
?>