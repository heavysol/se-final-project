<?php

class CalendarService {
    private $conn;
    private $userId;

    public function __construct(mysqli $conn, int $userId) {
        $this->conn = $conn;
        $this->userId = $userId;
    }

    public function getMonthlyEvents(string $year_month): array {
        if (!preg_match('/^\d{4}-\d{2}$/', $year_month)) {
            return ['status' => 'error', 'message' => 'Invalid date format. Use YYYY-MM'];
        }

        $start_date = $year_month . '-01';
        $end_date = date('Y-m-t', strtotime($start_date));

        $sql = "SELECT e.*, u.first_name, u.last_name 
                FROM Events e 
                JOIN Users u ON e.organizer_id = u.user_id 
                WHERE DATE(e.start_datetime) BETWEEN ? AND ?
                ORDER BY e.start_datetime";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return ['status' => 'error', 'message' => 'Failed to prepare statement'];
        }

        $stmt->bind_param("ss", $start_date, $end_date);
        if (!$stmt->execute()) {
            return ['status' => 'error', 'message' => 'Failed to fetch events'];
        }

        $result = $stmt->get_result();
        $events = [];
        while ($row = $result->fetch_assoc()) {
            $events[] = [
                'event_id' => $row['event_id'],
                'title' => $row['title'],
                'description' => $row['description'],
                'start_datetime' => $row['start_datetime'],
                'end_datetime' => $row['end_datetime'],
                'location' => $row['location'],
                'category' => $row['category'],
                'max_capacity' => $row['max_capacity'],
                'organizer' => $row['first_name'] . ' ' . $row['last_name']
            ];
        }

        return [
            'status' => 'success',
            'message' => 'Events retrieved successfully',
            'events' => $events
        ];
    }

    public function getDailyEvents(string $date): array {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return ['status' => 'error', 'message' => 'Invalid date format. Use YYYY-MM-DD'];
        }

        $sql = "SELECT e.*, u.first_name, u.last_name,
                (SELECT COUNT(*) FROM EventRegistrations WHERE event_id = e.event_id) as registered_count
                FROM Events e 
                JOIN Users u ON e.organizer_id = u.user_id 
                WHERE DATE(e.start_datetime) = ?
                ORDER BY e.start_datetime";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return ['status' => 'error', 'message' => 'Failed to prepare statement'];
        }

        $stmt->bind_param("s", $date);
        if (!$stmt->execute()) {
            return ['status' => 'error', 'message' => 'Failed to fetch events'];
        }

        $result = $stmt->get_result();
        $events = [];
        while ($row = $result->fetch_assoc()) {
            $events[] = [
                'event_id' => $row['event_id'],
                'title' => $row['title'],
                'description' => $row['description'],
                'start_datetime' => $row['start_datetime'],
                'end_datetime' => $row['end_datetime'],
                'location' => $row['location'],
                'category' => $row['category'],
                'max_capacity' => $row['max_capacity'],
                'registered_count' => $row['registered_count'],
                'organizer' => $row['first_name'] . ' ' . $row['last_name'],
                'is_full' => $row['registered_count'] >= $row['max_capacity']
            ];
        }

        return [
            'status' => 'success',
            'message' => 'Events retrieved successfully',
            'events' => $events
        ];
    }

    public function getUserEvents(): array {
        $sql = "SELECT e.*, 
                (SELECT COUNT(*) FROM EventRegistrations WHERE event_id = e.event_id) as registered_count
                FROM Events e 
                WHERE e.organizer_id = ? OR 
                e.event_id IN (SELECT event_id FROM EventRegistrations WHERE user_id = ?)
                ORDER BY e.start_datetime";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return ['status' => 'error', 'message' => 'Failed to prepare statement'];
        }

        $stmt->bind_param("ii", $this->userId, $this->userId);
        if (!$stmt->execute()) {
            return ['status' => 'error', 'message' => 'Failed to fetch events'];
        }

        $result = $stmt->get_result();
        $events = [
            'organizing' => [],
            'registered' => []
        ];

        while ($row = $result->fetch_assoc()) {
            $event = [
                'event_id' => $row['event_id'],
                'title' => $row['title'],
                'description' => $row['description'],
                'start_datetime' => $row['start_datetime'],
                'end_datetime' => $row['end_datetime'],
                'location' => $row['location'],
                'category' => $row['category'],
                'max_capacity' => $row['max_capacity'],
                'registered_count' => $row['registered_count'],
                'is_full' => $row['registered_count'] >= $row['max_capacity']
            ];

            if ($row['organizer_id'] == $this->userId) {
                $events['organizing'][] = $event;
            } else {
                $events['registered'][] = $event;
            }
        }

        return [
            'status' => 'success',
            'message' => 'Events retrieved successfully',
            'events' => $events
        ];
    }
} 