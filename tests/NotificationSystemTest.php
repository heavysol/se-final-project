<?php

namespace Tests\Notifications;

use PHPUnit\Framework\TestCase;
use Mockery;

class NotificationSystemTest extends TestCase
{
    protected function setUp(): void
    {
        // Set up test database
        $this->conn = setupTestDatabase();
        
        // Create test tables
        $this->createTestTables();
    }

    protected function tearDown(): void
    {
        // Clean up test database
        cleanupTestDatabase($this->conn);
        Mockery::close();
    }

    private function createTestTables()
    {
        // Create Users table
        $sql = "CREATE TABLE IF NOT EXISTS Users (
            user_id INT AUTO_INCREMENT PRIMARY KEY,
            first_name VARCHAR(100) NOT NULL,
            last_name VARCHAR(100) NOT NULL,
            email VARCHAR(255) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            role ENUM('student', 'organizer', 'admin') NOT NULL
        )";
        $this->conn->query($sql);

        // Create Events table
        $sql = "CREATE TABLE IF NOT EXISTS Events (
            event_id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            start_datetime DATETIME NOT NULL,
            end_datetime DATETIME NOT NULL,
            location VARCHAR(255) NOT NULL,
            category VARCHAR(100) NOT NULL,
            max_capacity INT NOT NULL,
            status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
            organizer_id INT NOT NULL
        )";
        $this->conn->query($sql);

        // Create Notifications table with updated ENUM types
        $sql = "CREATE TABLE IF NOT EXISTS Notifications (
            notification_id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            event_id INT,
            type ENUM('event_reminder', 'event_update', 'registration_confirmation', 
                     'event_cancellation', 'feedback_request') NOT NULL,
            message TEXT NOT NULL,
            is_read BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES Users(user_id),
            FOREIGN KEY (event_id) REFERENCES Events(event_id)
        )";
        $this->conn->query($sql);
    }

    public function testCreateNotification()
    {
        // Insert test data
        $this->insertTestData();

        // Create notification
        $sql = "INSERT INTO Notifications (user_id, event_id, type, message) 
                VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $userId = 1;
        $eventId = 1;
        $type = 'event_reminder';
        $message = 'Event reminder: Test Event starting in 1 hour';
        $stmt->bind_param("iiss", $userId, $eventId, $type, $message);
        $result = $stmt->execute();

        $this->assertTrue($result);

        // Verify notification
        $sql = "SELECT * FROM Notifications WHERE user_id = ? AND event_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $userId, $eventId);
        $stmt->execute();
        $result = $stmt->get_result();
        $notification = $result->fetch_assoc();

        $this->assertEquals($type, $notification['type']);
        $this->assertEquals($message, $notification['message']);
        $this->assertEquals(0, $notification['is_read']);
    }

    public function testGetUserNotifications()
    {
        // Insert test data
        $this->insertTestData();

        // Create multiple notifications
        $this->createTestNotifications();

        // Get user notifications
        $sql = "SELECT n.*, e.title as event_title 
                FROM Notifications n 
                LEFT JOIN Events e ON n.event_id = e.event_id 
                WHERE n.user_id = ? 
                ORDER BY n.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $userId = 1;
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        $this->assertEquals(3, $result->num_rows);

        $notifications = $result->fetch_all(MYSQLI_ASSOC);
        $this->assertEquals('event_reminder', $notifications[0]['type']);
        $this->assertEquals('event_update', $notifications[1]['type']);
        $this->assertEquals('registration_confirmation', $notifications[2]['type']);
    }

    public function testMarkNotificationAsRead()
    {
        // Insert test data
        $this->insertTestData();

        // Create notification
        $this->createTestNotifications();

        // Mark notification as read
        $sql = "UPDATE Notifications SET is_read = 1 WHERE notification_id = ?";
        $stmt = $this->conn->prepare($sql);
        $notificationId = 1;
        $stmt->bind_param("i", $notificationId);
        $result = $stmt->execute();

        $this->assertEquals(true, $result);

        // Verify update
        $sql = "SELECT is_read FROM Notifications WHERE notification_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $notificationId);
        $stmt->execute();
        $result = $stmt->get_result();
        $notification = $result->fetch_assoc();

        $this->assertEquals(1, $notification['is_read']);
    }

    public function testDeleteNotification()
    {
        // Insert test data
        $this->insertTestData();

        // Create notification
        $this->createTestNotifications();

        // Delete notification
        $sql = "DELETE FROM Notifications WHERE notification_id = ?";
        $stmt = $this->conn->prepare($sql);
        $notificationId = 1;
        $stmt->bind_param("i", $notificationId);
        $result = $stmt->execute();

        $this->assertTrue($result);

        // Verify deletion
        $sql = "SELECT * FROM Notifications WHERE notification_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $notificationId);
        $stmt->execute();
        $result = $stmt->get_result();

        $this->assertEquals(0, $result->num_rows);
    }

    public function testNotificationTypes()
    {
        // Insert test data
        $this->insertTestData();

        // Test different notification types
        $types = [
            'event_reminder' => 'Event reminder: Test Event starting soon',
            'event_update' => 'Event details have been updated',
            'registration_confirmation' => 'Your registration has been confirmed',
            'event_cancellation' => 'Event has been cancelled',
            'feedback_request' => 'Please provide feedback for the event'
        ];

        $insertedTypes = [];
        foreach ($types as $type => $message) {
            $sql = "INSERT INTO Notifications (user_id, event_id, type, message) 
                    VALUES (?, ?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            $userId = 1;
            $eventId = 1;
            $stmt->bind_param("iiss", $userId, $eventId, $type, $message);
            $result = $stmt->execute();
            if ($result) {
                $insertedTypes[] = $type;
            }
        }

        // Verify all types were created
        $sql = "SELECT DISTINCT type FROM Notifications WHERE user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $userId = 1;
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $createdTypes = array_column($result->fetch_all(MYSQLI_ASSOC), 'type');

        // Sort both arrays to ensure consistent comparison
        sort($insertedTypes);
        sort($createdTypes);
        $this->assertEquals($insertedTypes, $createdTypes);
    }

    private function insertTestData()
    {
        // Insert test user
        $sql = "INSERT INTO Users (first_name, last_name, email, password, role) 
                VALUES ('Test', 'User', 'test@example.com', 'password', 'student')";
        $this->conn->query($sql);

        // Insert test event
        $sql = "INSERT INTO Events (title, description, start_datetime, end_datetime, 
                location, category, max_capacity, organizer_id) 
                VALUES ('Test Event', 'Description', '2024-05-01 10:00:00', 
                '2024-05-01 12:00:00', 'Location', 'Category', 100, 1)";
        $this->conn->query($sql);
    }

    private function createTestNotifications()
    {
        $sql = "INSERT INTO Notifications (user_id, event_id, type, message) 
                VALUES 
                (1, 1, 'event_reminder', 'Event reminder: Test Event starting in 1 hour'),
                (1, 1, 'event_update', 'Event details have been updated'),
                (1, 1, 'registration_confirmation', 'Your registration has been confirmed')";
        $this->conn->query($sql);
    }
} 