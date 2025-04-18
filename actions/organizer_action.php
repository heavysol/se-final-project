// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set JSON header
header('Content-Type: application/json');

// Start session
session_start();

// Include database connection
require_once '../config/database.php';

// Function to validate date format
function validateDateTime($dateTime) {
    $d = DateTime::createFromFormat('Y-m-d\TH:i', $dateTime);
    return $d && $d->format('Y-m-d\TH:i') === $dateTime;
}

// Check if user is logged in and is an organizer
function checkAuth() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        throw new Exception('Not logged in');
    }
    if ($_SESSION['role'] !== 'organizer') {
        throw new Exception('Unauthorized access');
    }
}

try {
    // Verify database connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Handle get_my_events action
    if (isset($_GET['action']) && $_GET['action'] === 'get_my_events') {
        // Fetch all events for the current organizer
        $sql = "SELECT 
                    id, title, description, max_capacity, 
                    category, status 
                FROM events 
                WHERE organizer_id = ? 
                ORDER BY created_at DESC";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("i", $_SESSION['user_id']);

        if (!$stmt->execute()) {
            throw new Exception("Failed to fetch events: " . $stmt->error);
        }

        $result = $stmt->get_result();
        $events = [];

        while ($row = $result->fetch_assoc()) {
            $events[] = [
                'id' => $row['id'],
                'title' => htmlspecialchars($row['title']),
                'description' => htmlspecialchars($row['description']),
                'max_capacity' => $row['max_capacity'],
                'category' => htmlspecialchars($row['category']),
                'status' => $row['status']
            ];
        }

        // Return data in DataTables expected format
        echo json_encode([
            'draw' => isset($_GET['draw']) ? intval($_GET['draw']) : 1,
            'recordsTotal' => count($events),
            'recordsFiltered' => count($events),
            'data' => $events
        ]);
        exit;
    }

    // Handle POST actions (create, update, delete)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        checkAuth();

        switch ($_POST['action']) {
            case 'create':
                // Validate required fields
                $required_fields = ['title', 'description', 'start_datetime', 
                                  'end_datetime', 'location', 'category', 'max_capacity'];
                
                foreach ($required_fields as $field) {
                    if (!isset($_POST[$field]) || empty($_POST[$field])) {
                        throw new Exception("$field is required");
                    }
                }

                // Validate dates
                if (!validateDateTime($_POST['start_datetime']) || 
                    !validateDateTime($_POST['end_datetime'])) {
                    throw new Exception("Invalid date format");
                }

                // Validate max_capacity is a positive number
                if (!is_numeric($_POST['max_capacity']) || $_POST['max_capacity'] <= 0) {
                    throw new Exception("Max capacity must be a positive number");
                }

                // Prepare and execute insert query
                $sql = "INSERT INTO events (
                    title, description, start_datetime, end_datetime,
                    location, category, max_capacity, organizer_id, status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')";

                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    throw new Exception("Prepare failed: " . $conn->error);
                }

                $stmt->bind_param("ssssssis",
                    $_POST['title'],
                    $_POST['description'],
                    $_POST['start_datetime'],
                    $_POST['end_datetime'],
                    $_POST['location'],
                    $_POST['category'],
                    $_POST['max_capacity'],
                    $_SESSION['user_id']
                );

                if (!$stmt->execute()) {
                    throw new Exception("Failed to create event: " . $stmt->error);
                }

                echo json_encode([
                    'success' => true,
                    'message' => 'Event created successfully',
                    'event_id' => $conn->insert_id
                ]);
                break;

            case 'update':
                if (!isset($_POST['event_id'])) {
                    throw new Exception("Event ID is required");
                }

                $sql = "UPDATE events SET 
                        title = ?, 
                        description = ?, 
                        start_datetime = ?, 
                        end_datetime = ?, 
                        location = ?, 
                        category = ?, 
                        max_capacity = ?
                        WHERE event_id = ? AND organizer_id = ?";

                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssssssiis",
                    $_POST['title'],
                    $_POST['description'],
                    $_POST['start_datetime'],
                    $_POST['end_datetime'],
                    $_POST['location'],
                    $_POST['category'],
                    $_POST['max_capacity'],
                    $_POST['event_id'],
                    $_SESSION['user_id']
                );

                if (!$stmt->execute()) {
                    throw new Exception("Failed to update event: " . $stmt->error);
                }

                echo json_encode([
                    'success' => true,
                    'message' => 'Event updated successfully'
                ]);
                break;

            case 'delete':
                if (!isset($_POST['event_id'])) {
                    throw new Exception("Event ID is required");
                }

                $sql = "DELETE FROM events WHERE event_id = ? AND organizer_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ii", $_POST['event_id'], $_SESSION['user_id']);

                if (!$stmt->execute()) {
                    throw new Exception("Failed to delete event: " . $stmt->error);
                }

                echo json_encode([
                    'success' => true,
                    'message' => 'Event deleted successfully'
                ]);
                break;

            case 'get_event':
                if (!isset($_GET['event_id'])) {
                    throw new Exception("Event ID is required");
                }

                // Debug: Log the received event_id
                error_log("Fetching event ID: " . $_GET['event_id']);

                $sql = "SELECT * FROM events WHERE event_id = ? AND organizer_id = ?";
                $stmt = $conn->prepare($sql);
                
                if (!$stmt) {
                    throw new Exception("Prepare failed: " . $conn->error);
                }

                $stmt->bind_param("ii", $_GET['event_id'], $_SESSION['user_id']);
                
                if (!$stmt->execute()) {
                    throw new Exception("Execute failed: " . $stmt->error);
                }

                $result = $stmt->get_result();
                $event = $result->fetch_assoc();

                if (!$event) {
                    throw new Exception("Event not found");
                }

                // Debug: Log the found event
                error_log("Found event: " . json_encode($event));

                echo json_encode([
                    'success' => true,
                    'event' => $event
                ]);
                break;

            default:
                throw new Exception("Invalid action");
        }
    }
    // Handle GET requests for retrieving events
    else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        checkAuth();
        
        // Default to fetching all events for the organizer
        $sql = "SELECT 
                    id, title, description, start_datetime, end_datetime,
                    location, category, max_capacity, status, created_at
                FROM events 
                WHERE organizer_id = ?
                ORDER BY created_at DESC";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("i", $_SESSION['user_id']);

        if (!$stmt->execute()) {
            throw new Exception("Failed to fetch events: " . $stmt->error);
        }

        $result = $stmt->get_result();
        $events = [];

        while ($row = $result->fetch_assoc()) {
            // Format dates for display
            $row['start_datetime'] = date('Y-m-d H:i', strtotime($row['start_datetime']));
            $row['end_datetime'] = date('Y-m-d H:i', strtotime($row['end_datetime']));
            $row['created_at'] = date('Y-m-d H:i', strtotime($row['created_at']));
            $events[] = $row;
        }

        echo json_encode([
            'success' => true,
            'events' => $events
        ]);
    }
    else {
        throw new Exception("Invalid request method");
    }

} catch (Exception $e) {
    // Debug: Log the error
    error_log("Error in organizer_action.php: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

// Close database connection
if (isset($stmt)) {
    $stmt->close();
}
if (isset($conn)) {
    $conn->close();
}
?> 