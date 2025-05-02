<?php

namespace Tests\Feedback;

use PHPUnit\Framework\TestCase;
use Mockery;

class FeedbackTest extends TestCase
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

        // Create Feedback table
        $sql = "CREATE TABLE IF NOT EXISTS Feedback (
            feedback_id INT AUTO_INCREMENT PRIMARY KEY,
            event_id INT NOT NULL,
            user_id INT NOT NULL,
            rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
            comments TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (event_id) REFERENCES Events(event_id),
            FOREIGN KEY (user_id) REFERENCES Users(user_id)
        )";
        $this->conn->query($sql);
    }

    public function testSubmitFeedback()
    {
        // Insert test user and event
        $this->insertTestData();

        // Test feedback data
        $eventId = 1;
        $userId = 1;
        $rating = 5;
        $comments = 'Great event!';

        // Insert feedback
        $sql = "INSERT INTO Feedback (event_id, user_id, rating, comments) 
                VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iiis", $eventId, $userId, $rating, $comments);
        $result = $stmt->execute();

        $this->assertTrue($result);

        // Verify feedback was submitted
        $sql = "SELECT * FROM Feedback WHERE event_id = ? AND user_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $eventId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $feedback = $result->fetch_assoc();

        $this->assertEquals($rating, $feedback['rating']);
        $this->assertEquals($comments, $feedback['comments']);
    }

    public function testGetEventFeedback()
    {
        // Insert test data
        $this->insertTestData();

        // Insert test feedback
        $sql = "INSERT INTO Feedback (event_id, user_id, rating, comments) 
                VALUES (1, 1, 5, 'Great event!')";
        $this->conn->query($sql);

        // Get feedback for event
        $sql = "SELECT f.*, u.first_name, u.last_name 
                FROM Feedback f 
                JOIN Users u ON f.user_id = u.user_id 
                WHERE f.event_id = ? 
                ORDER BY f.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $eventId = 1;
        $stmt->bind_param("i", $eventId);
        $stmt->execute();
        $result = $stmt->get_result();

        $this->assertEquals(1, $result->num_rows);

        $feedback = $result->fetch_assoc();
        $this->assertEquals(5, $feedback['rating']);
        $this->assertEquals('Great event!', $feedback['comments']);
        $this->assertEquals('Test', $feedback['first_name']);
        $this->assertEquals('User', $feedback['last_name']);
    }

    public function testInvalidFeedbackRating()
    {
        // Insert test data
        $this->insertTestData();

        // Test invalid rating
        $eventId = 1;
        $userId = 1;
        $rating = 6; // Invalid rating (should be 1-5)
        $comments = 'Test comment';

        // Attempt to insert invalid feedback
        $sql = "INSERT INTO Feedback (event_id, user_id, rating, comments) 
                VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iiis", $eventId, $userId, $rating, $comments);
        $result = $stmt->execute();

        $this->assertFalse($result);
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
} 