<?php

class FavoriteService {
    private $conn;
    private $userId;

    public function __construct(mysqli $conn, int $userId) {
        $this->conn = $conn;
        $this->userId = $userId;
    }

    public function addFavorite(int $eventId): array {
        if (!$this->eventExists($eventId)) {
            return ['status' => 'error', 'message' => 'Event not found'];
        }

        if ($this->isFavorited($eventId)) {
            return ['status' => 'error', 'message' => 'Event is already in favorites'];
        }

        $stmt = $this->conn->prepare("INSERT INTO favorites (user_id, event_id) VALUES (?, ?)");
        if (!$stmt || !$stmt->bind_param("ii", $this->userId, $eventId) || !$stmt->execute()) {
            return ['status' => 'error', 'message' => 'Failed to add favorite'];
        }

        return ['status' => 'success', 'message' => 'Event added to favorites'];
    }

    public function removeFavorite(int $eventId): array {
        if (!$this->isFavorited($eventId)) {
            return ['status' => 'error', 'message' => 'Event is not in favorites'];
        }

        $stmt = $this->conn->prepare("DELETE FROM favorites WHERE user_id = ? AND event_id = ?");
        if (!$stmt || !$stmt->bind_param("ii", $this->userId, $eventId) || !$stmt->execute()) {
            return ['status' => 'error', 'message' => 'Failed to remove favorite'];
        }

        if ($stmt->affected_rows > 0) {
            return ['status' => 'success', 'message' => 'Event removed from favorites'];
        }

        return ['status' => 'error', 'message' => 'No rows affected'];
    }

    protected function eventExists(int $eventId): bool {
        $stmt = $this->conn->prepare("SELECT event_id FROM events WHERE event_id = ?");
        $stmt->bind_param("i", $eventId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }

    protected function isFavorited(int $eventId): bool {
        $stmt = $this->conn->prepare("SELECT favorite_id FROM favorites WHERE user_id = ? AND event_id = ?");
        $stmt->bind_param("ii", $this->userId, $eventId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }
}
?>