<?php

namespace Tests\Registration;

use PHPUnit\Framework\TestCase;
use Mockery;

class EventRegistrationTest extends TestCase
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

        // Create Registrations table
        $sql = "CREATE TABLE IF NOT EXISTS Registrations (
            registration_id INT AUTO_INCREMENT PRIMARY KEY,
            event_id INT NOT NULL,
            user_id INT NOT NULL,
            registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (event_id) REFERENCES Events(event_id),
            FOREIGN KEY (user_id) REFERENCES Users(user_id),
            UNIQUE KEY unique_registration (event_id, user_id)
        )";
        $this->conn->query($sql);

        // Drop existing trigger if it exists
        $sql = "DROP TRIGGER IF EXISTS before_registration_insert";
        $this->conn->query($sql);

        // Create trigger to enforce event capacity
        $sql = "CREATE TRIGGER before_registration_insert 
                BEFORE INSERT ON Registrations
                FOR EACH ROW
                BEGIN
                    DECLARE current_registrations INT;
                    DECLARE event_capacity INT;
                    
                    SELECT COUNT(*) INTO current_registrations
                    FROM Registrations
                    WHERE event_id = NEW.event_id;
                    
                    SELECT max_capacity INTO event_capacity
                    FROM Events
                    WHERE event_id = NEW.event_id;
                    
                    IF current_registrations >= event_capacity THEN
                        SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'Event has reached maximum capacity';
                    END IF;
                END;";
        $this->conn->query($sql);
    }

    public function testRegisterForEvent()
    {
        // Insert test data
        $this->insertTestData();

        // Register user for event
        $sql = "INSERT INTO Registrations (event_id, user_id) VALUES (?, ?)";
        $stmt = $this->conn->prepare($sql);
        $eventId = 1;
        $userId = 1;
        $stmt->bind_param("ii", $eventId, $userId);
        $result = $stmt->execute();

        $this->assertTrue($result);

        // Verify registration
        $sql = "SELECT * FROM Registrations WHERE event_id = ? AND user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $eventId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        $this->assertEquals(1, $result->num_rows);
    }

    public function testDuplicateRegistration()
    {
        // Insert test data
        $this->insertTestData();

        // First registration
        $sql = "INSERT INTO Registrations (event_id, user_id) VALUES (?, ?)";
        $stmt = $this->conn->prepare($sql);
        $eventId = 1;
        $userId = 1;
        $stmt->bind_param("ii", $eventId, $userId);
        $result = $stmt->execute();
        $this->assertTrue($result);

        // Attempt duplicate registration
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $eventId, $userId);
        $result = $stmt->execute();

        $this->assertFalse($result);
    }

    public function testCancelRegistration()
    {
        // Insert test data and register
        $this->insertTestData();
        $sql = "INSERT INTO Registrations (event_id, user_id) VALUES (?, ?)";
        $stmt = $this->conn->prepare($sql);
        $eventId = 1;
        $userId = 1;
        $stmt->bind_param("ii", $eventId, $userId);
        $stmt->execute();

        // Cancel registration
        $sql = "DELETE FROM Registrations WHERE event_id = ? AND user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $eventId, $userId);
        $result = $stmt->execute();

        $this->assertTrue($result);

        // Verify cancellation
        $sql = "SELECT * FROM Registrations WHERE event_id = ? AND user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $eventId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        $this->assertEquals(0, $result->num_rows);
    }

    public function testEventCapacity()
    {
        // Insert test data
        $this->insertTestData();

        // Register users until capacity is reached
        $maxCapacity = 2;
        $eventId = 1;
        $sql = "UPDATE Events SET max_capacity = ? WHERE event_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $maxCapacity, $eventId);
        $stmt->execute();

        // Register first user
        $sql = "INSERT INTO Registrations (event_id, user_id) VALUES (?, ?)";
        $stmt = $this->conn->prepare($sql);
        $userId = 1;
        $stmt->bind_param("ii", $eventId, $userId);
        $result = $stmt->execute();
        $this->assertTrue($result);

        // Register second user
        $stmt = $this->conn->prepare($sql);
        $userId = 2;
        $stmt->bind_param("ii", $eventId, $userId);
        $result = $stmt->execute();
        $this->assertTrue($result);

        // Attempt to register third user (should fail)
        $stmt = $this->conn->prepare($sql);
        $userId = 3;
        $stmt->bind_param("ii", $eventId, $userId);
        $result = $stmt->execute();
        $this->assertFalse($result);
    }

    private function insertTestData()
    {
        // Insert test users
        $sql = "INSERT INTO Users (first_name, last_name, email, password, role) 
                VALUES 
                ('Test', 'User1', 'test1@example.com', 'password', 'student'),
                ('Test', 'User2', 'test2@example.com', 'password', 'student'),
                ('Test', 'User3', 'test3@example.com', 'password', 'student')";
        $this->conn->query($sql);

        // Insert test event
        $sql = "INSERT INTO Events (title, description, start_datetime, end_datetime, 
                location, category, max_capacity, organizer_id) 
                VALUES ('Test Event', 'Description', '2024-05-01 10:00:00', 
                '2024-05-01 12:00:00', 'Location', 'Category', 100, 1)";
        $this->conn->query($sql);
    }
} 