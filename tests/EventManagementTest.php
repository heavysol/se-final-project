<?php

namespace Tests\Events;

use PHPUnit\Framework\TestCase;
use Mockery;

class EventManagementTest extends TestCase
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
        // Create Events table
        $sql = "CREATE TABLE IF NOT EXISTS Events (
            event_id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL CHECK (title <> ''),
            description TEXT,
            start_datetime DATETIME NOT NULL,
            end_datetime DATETIME NOT NULL,
            location VARCHAR(255) NOT NULL,
            category VARCHAR(100) NOT NULL,
            max_capacity INT NOT NULL CHECK (max_capacity > 0),
            status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
            organizer_id INT NOT NULL,
            CONSTRAINT valid_datetime CHECK (end_datetime > start_datetime)
        )";
        $this->conn->query($sql);

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
    }

    public function testCreateEvent()
    {
        // Insert test organizer
        $sql = "INSERT INTO Users (first_name, last_name, email, password, role) 
                VALUES ('Test', 'Organizer', 'organizer@test.com', 'password', 'organizer')";
        $this->conn->query($sql);
        $organizerId = $this->conn->insert_id;

        // Test event data
        $eventData = [
            'title' => 'Test Event',
            'description' => 'Test Description',
            'start_datetime' => '2024-05-01 10:00:00',
            'end_datetime' => '2024-05-01 12:00:00',
            'location' => 'Test Location',
            'category' => 'Test Category',
            'max_capacity' => 100,
            'organizer_id' => $organizerId
        ];

        // Insert event
        $sql = "INSERT INTO Events (title, description, start_datetime, end_datetime, 
                location, category, max_capacity, organizer_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssssssii", 
            $eventData['title'],
            $eventData['description'],
            $eventData['start_datetime'],
            $eventData['end_datetime'],
            $eventData['location'],
            $eventData['category'],
            $eventData['max_capacity'],
            $eventData['organizer_id']
        );
        $result = $stmt->execute();

        $this->assertTrue($result);
        
        // Verify event was created
        $sql = "SELECT * FROM Events WHERE title = 'Test Event'";
        $result = $this->conn->query($sql);
        $event = $result->fetch_assoc();

        $this->assertEquals($eventData['title'], $event['title']);
        $this->assertEquals($eventData['description'], $event['description']);
        $this->assertEquals('pending', $event['status']);
    }

    public function testUpdateEvent()
    {
        // Insert test event
        $sql = "INSERT INTO Events (title, description, start_datetime, end_datetime, 
                location, category, max_capacity, organizer_id) 
                VALUES ('Test Event', 'Description', '2024-05-01 10:00:00', 
                '2024-05-01 12:00:00', 'Location', 'Category', 100, 1)";
        $this->conn->query($sql);
        $eventId = $this->conn->insert_id;

        // Update event
        $newTitle = 'Updated Test Event';
        $sql = "UPDATE Events SET title = ? WHERE event_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $newTitle, $eventId);
        $result = $stmt->execute();

        $this->assertTrue($result);

        // Verify update
        $sql = "SELECT title FROM Events WHERE event_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $eventId);
        $stmt->execute();
        $result = $stmt->get_result();
        $event = $result->fetch_assoc();

        $this->assertEquals($newTitle, $event['title']);
    }

    public function testDeleteEvent()
    {
        // Insert test event
        $sql = "INSERT INTO Events (title, description, start_datetime, end_datetime, 
                location, category, max_capacity, organizer_id) 
                VALUES ('Test Event', 'Description', '2024-05-01 10:00:00', 
                '2024-05-01 12:00:00', 'Location', 'Category', 100, 1)";
        $this->conn->query($sql);
        $eventId = $this->conn->insert_id;

        // Delete event
        $sql = "DELETE FROM Events WHERE event_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $eventId);
        $result = $stmt->execute();

        $this->assertTrue($result);

        // Verify deletion
        $sql = "SELECT * FROM Events WHERE event_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $eventId);
        $stmt->execute();
        $result = $stmt->get_result();

        $this->assertEquals(0, $result->num_rows);
    }

    public function testEventValidation()
    {
        // Test invalid event data
        $invalidEventData = [
            'title' => '', // Empty title
            'description' => 'Test Description',
            'start_datetime' => '2024-05-01 10:00:00',
            'end_datetime' => '2024-05-01 09:00:00', // End time before start time
            'location' => 'Test Location',
            'category' => 'Test Category',
            'max_capacity' => -1, // Invalid capacity
            'organizer_id' => 1
        ];

        // Attempt to insert invalid event
        $sql = "INSERT INTO Events (title, description, start_datetime, end_datetime, 
                location, category, max_capacity, organizer_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssssssii", 
            $invalidEventData['title'],
            $invalidEventData['description'],
            $invalidEventData['start_datetime'],
            $invalidEventData['end_datetime'],
            $invalidEventData['location'],
            $invalidEventData['category'],
            $invalidEventData['max_capacity'],
            $invalidEventData['organizer_id']
        );
        $result = $stmt->execute();

        $this->assertFalse($result);
    }
} 