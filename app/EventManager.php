<?php
class EventManager {
    private $conn;

    public function __construct(mysqli $conn) {
        $this->conn = $conn;
    }

    public function getEventsByOrganizer($organizerId) {
        $query = "SELECT * FROM events WHERE organizer_id = ? ORDER BY start_datetime";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $organizerId);
        $stmt->execute();
        $result = $stmt->get_result();

        $events = [];
        while ($event = $result->fetch_assoc()) {
            $event['registration_count'] = $this->getRegistrationCount($event['event_id']);
            $events[] = $event;
        }

        return $events;
    }

    private function getRegistrationCount($eventId) {
        $query = "SELECT COUNT(*) as count FROM event_registrations WHERE event_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $eventId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc()['count'] ?? 0;
    }

    public function calculateProgress($registered, $capacity) {
        if ($capacity == 0) return 0;
        return ($registered / $capacity) * 100;
    }

    public function getProgressClass($percentage) {
        if ($percentage >= 80) return 'bg-success';
        if ($percentage >= 50) return 'bg-warning';
        return 'bg-info';
    }
}

?>
