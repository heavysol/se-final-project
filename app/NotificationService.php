<?php

class NotificationService {
    private $conn;
    private $userId;

    public function __construct(mysqli $conn, int $userId) {
        $this->conn = $conn;
        $this->userId = $userId;
    }

    public function createNotification(array $notificationData): array {
        $requiredFields = ['user_id', 'type', 'message'];
        foreach ($requiredFields as $field) {
            if (!isset($notificationData[$field]) || empty($notificationData[$field])) {
                return ['status' => 'error', 'message' => "Missing required field: $field"];
            }
        }

        // Validate notification type
        if (!in_array($notificationData['type'], ['event_reminder', 'registration', 'cancellation', 'update'])) {
            return ['status' => 'error', 'message' => 'Invalid notification type'];
        }

        $sql = "INSERT INTO Notifications (user_id, type, message, created_at) VALUES (?, ?, ?, NOW())";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return ['status' => 'error', 'message' => 'Failed to prepare statement'];
        }

        $stmt->bind_param("iss", 
            $notificationData['user_id'],
            $notificationData['type'],
            $notificationData['message']
        );

        if (!$stmt->execute()) {
            return ['status' => 'error', 'message' => 'Failed to create notification'];
        }

        return [
            'status' => 'success',
            'message' => 'Notification created successfully',
            'notification_id' => $stmt->insert_id
        ];
    }

    public function getUserNotifications(bool $unreadOnly = false): array {
        $sql = "SELECT * FROM Notifications WHERE user_id = ?";
        if ($unreadOnly) {
            $sql .= " AND is_read = 0";
        }
        $sql .= " ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            return ['status' => 'error', 'message' => 'Failed to prepare statement'];
        }

        $stmt->bind_param("i", $this->userId);
        if (!$stmt->execute()) {
            return ['status' => 'error', 'message' => 'Failed to fetch notifications'];
        }

        $result = $stmt->get_result();
        $notifications = [];
        while ($row = $result->fetch_assoc()) {
            $notifications[] = [
                'notification_id' => $row['notification_id'],
                'type' => $row['type'],
                'message' => $row['message'],
                'is_read' => (bool)$row['is_read'],
                'created_at' => $row['created_at']
            ];
        }

        return [
            'status' => 'success',
            'message' => 'Notifications retrieved successfully',
            'notifications' => $notifications
        ];
    }

    public function markAsRead(int $notificationId): array {
        // Verify the notification belongs to the user
        $stmt = $this->conn->prepare("SELECT user_id FROM Notifications WHERE notification_id = ?");
        $stmt->bind_param("i", $notificationId);
        $stmt->execute();
        $result = $stmt->get_result();
        $notification = $result->fetch_assoc();

        if (!$notification) {
            return ['status' => 'error', 'message' => 'Notification not found'];
        }

        if ($notification['user_id'] != $this->userId) {
            return ['status' => 'error', 'message' => 'Not authorized to update this notification'];
        }

        $stmt = $this->conn->prepare("UPDATE Notifications SET is_read = 1 WHERE notification_id = ?");
        if (!$stmt) {
            return ['status' => 'error', 'message' => 'Failed to prepare statement'];
        }

        $stmt->bind_param("i", $notificationId);
        if (!$stmt->execute()) {
            return ['status' => 'error', 'message' => 'Failed to update notification'];
        }

        return ['status' => 'success', 'message' => 'Notification marked as read'];
    }

    public function markAllAsRead(): array {
        $stmt = $this->conn->prepare("UPDATE Notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0");
        if (!$stmt) {
            return ['status' => 'error', 'message' => 'Failed to prepare statement'];
        }

        $stmt->bind_param("i", $this->userId);
        if (!$stmt->execute()) {
            return ['status' => 'error', 'message' => 'Failed to update notifications'];
        }

        return [
            'status' => 'success',
            'message' => 'All notifications marked as read',
            'updated_count' => $stmt->affected_rows
        ];
    }

    public function deleteNotification(int $notificationId): array {
        // Verify the notification belongs to the user
        $stmt = $this->conn->prepare("SELECT user_id FROM Notifications WHERE notification_id = ?");
        $stmt->bind_param("i", $notificationId);
        $stmt->execute();
        $result = $stmt->get_result();
        $notification = $result->fetch_assoc();

        if (!$notification) {
            return ['status' => 'error', 'message' => 'Notification not found'];
        }

        if ($notification['user_id'] != $this->userId) {
            return ['status' => 'error', 'message' => 'Not authorized to delete this notification'];
        }

        $stmt = $this->conn->prepare("DELETE FROM Notifications WHERE notification_id = ?");
        if (!$stmt) {
            return ['status' => 'error', 'message' => 'Failed to prepare statement'];
        }

        $stmt->bind_param("i", $notificationId);
        if (!$stmt->execute()) {
            return ['status' => 'error', 'message' => 'Failed to delete notification'];
        }

        return ['status' => 'success', 'message' => 'Notification deleted successfully'];
    }
} 