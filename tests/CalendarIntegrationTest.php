<?php

namespace Tests\Calendar;

use PHPUnit\Framework\TestCase;
use Mockery;

class CalendarIntegrationTest extends TestCase
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
            organizer_id INT NOT NULL,
            FOREIGN KEY (organizer_id) REFERENCES Users(user_id)
        )";
        $this->conn->query($sql);

        // Create Calendar table
        $sql = "CREATE TABLE IF NOT EXISTS Calendar (
            calendar_id INT AUTO_INCREMENT PRIMARY KEY,
            event_id INT NOT NULL,
            user_id INT NOT NULL,
            reminder_time DATETIME,
            status ENUM('scheduled', 'cancelled', 'completed') DEFAULT 'scheduled',
            FOREIGN KEY (event_id) REFERENCES Events(event_id),
            FOREIGN KEY (user_id) REFERENCES Users(user_id)
        )";
        $this->conn->query($sql);
    }

    public function testAddEventToCalendar()
    {
        // Insert test data
        $this->insertTestData();

        // Add event to calendar
        $sql = "INSERT INTO Calendar (event_id, user_id, reminder_time) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $eventId = 1;
        $userId = 1;
        $reminderTime = '2024-05-01 09:00:00';
        $stmt->bind_param("iis", $eventId, $userId, $reminderTime);
        $result = $stmt->execute();

        $this->assertTrue($result);

        // Verify calendar entry
        $sql = "SELECT * FROM Calendar WHERE event_id = ? AND user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $eventId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $calendarEntry = $result->fetch_assoc();

        $this->assertEquals($eventId, $calendarEntry['event_id']);
        $this->assertEquals($userId, $calendarEntry['user_id']);
        $this->assertEquals($reminderTime, $calendarEntry['reminder_time']);
        $this->assertEquals('scheduled', $calendarEntry['status']);
    }

    public function testGetUserCalendar()
    {
        // Insert test data
        $this->insertTestData();

        // Add multiple events to calendar
        $this->addTestCalendarEntries();

        // Get user's calendar
        $sql = "SELECT c.*, e.title, e.start_datetime, e.end_datetime, e.location 
                FROM Calendar c 
                JOIN Events e ON c.event_id = e.event_id 
                WHERE c.user_id = ? 
                ORDER BY e.start_datetime";
        $stmt = $this->conn->prepare($sql);
        $userId = 1;
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        $this->assertEquals(2, $result->num_rows);

        $events = $result->fetch_all(MYSQLI_ASSOC);
        $this->assertEquals('Test Event 1', $events[0]['title']);
        $this->assertEquals('Test Event 2', $events[1]['title']);
    }

    public function testUpdateCalendarEntry()
    {
        // Insert test data
        $this->insertTestData();

        // Add event to calendar
        $this->addTestCalendarEntries();

        // Update calendar entry
        $sql = "UPDATE Calendar SET reminder_time = ?, status = ? WHERE event_id = ? AND user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $newReminderTime = '2024-05-01 08:30:00';
        $newStatus = 'cancelled';
        $eventId = 1;
        $userId = 1;
        $stmt->bind_param("ssii", $newReminderTime, $newStatus, $eventId, $userId);
        $result = $stmt->execute();

        $this->assertTrue($result);

        // Verify update
        $sql = "SELECT * FROM Calendar WHERE event_id = ? AND user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $eventId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $calendarEntry = $result->fetch_assoc();

        $this->assertEquals($newReminderTime, $calendarEntry['reminder_time']);
        $this->assertEquals($newStatus, $calendarEntry['status']);
    }

    public function testRemoveFromCalendar()
    {
        // Insert test data
        $this->insertTestData();

        // Add event to calendar
        $this->addTestCalendarEntries();

        // Remove from calendar
        $sql = "DELETE FROM Calendar WHERE event_id = ? AND user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $eventId = 1;
        $userId = 1;
        $stmt->bind_param("ii", $eventId, $userId);
        $result = $stmt->execute();

        $this->assertTrue($result);

        // Verify removal
        $sql = "SELECT * FROM Calendar WHERE event_id = ? AND user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $eventId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        $this->assertEquals(0, $result->num_rows);
    }

    public function testCalendarConflict()
    {
        // Insert test data
        $this->insertTestData();

        // Add first event to calendar
        $sql = "INSERT INTO Calendar (event_id, user_id) VALUES (?, ?)";
        $stmt = $this->conn->prepare($sql);
        $eventId1 = 1;
        $userId = 1;
        $stmt->bind_param("ii", $eventId1, $userId);
        $stmt->execute();

        // Attempt to add conflicting event
        $sql = "SELECT COUNT(*) as count FROM Calendar c 
                JOIN Events e1 ON c.event_id = e1.event_id 
                JOIN Events e2 ON e2.event_id = ? 
                WHERE c.user_id = ? 
                AND (
                    (e2.start_datetime BETWEEN e1.start_datetime AND e1.end_datetime) 
                    OR (e2.end_datetime BETWEEN e1.start_datetime AND e1.end_datetime)
                )";
        $stmt = $this->conn->prepare($sql);
        $eventId2 = 2;
        $stmt->bind_param("ii", $eventId2, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $conflict = $result->fetch_assoc();

        $this->assertEquals(1, $conflict['count']);
    }

    private function insertTestData()
    {
        // Insert test user
        $sql = "INSERT INTO Users (first_name, last_name, email, password, role) 
                VALUES ('Test', 'User', 'test@example.com', 'password', 'student')";
        $this->conn->query($sql);

        // Insert test events with overlapping times
        $sql = "INSERT INTO Events (title, description, start_datetime, end_datetime, 
                location, category, max_capacity, organizer_id) 
                VALUES 
                ('Test Event 1', 'Description 1', '2024-05-01 10:00:00', 
                '2024-05-01 12:00:00', 'Location 1', 'Category 1', 100, 1),
                ('Test Event 2', 'Description 2', '2024-05-01 11:00:00', 
                '2024-05-01 13:00:00', 'Location 2', 'Category 2', 100, 1)";
        $this->conn->query($sql);
    }

    private function addTestCalendarEntries()
    {
        $sql = "INSERT INTO Calendar (event_id, user_id) VALUES (?, ?)";
        $stmt = $this->conn->prepare($sql);
        
        // Add first event
        $eventId1 = 1;
        $userId = 1;
        $stmt->bind_param("ii", $eventId1, $userId);
        $stmt->execute();

        // Add second event
        $eventId2 = 2;
        $stmt->bind_param("ii", $eventId2, $userId);
        $stmt->execute();
    }
} 