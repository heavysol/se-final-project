<?php

namespace Tests\Dashboard;

use PHPUnit\Framework\TestCase;
use Mockery;

class DashboardStatisticsTest extends TestCase
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
            FOREIGN KEY (user_id) REFERENCES Users(user_id)
        )";
        $this->conn->query($sql);

        // Create Feedback table
        $sql = "CREATE TABLE IF NOT EXISTS Feedback (
            feedback_id INT AUTO_INCREMENT PRIMARY KEY,
            event_id INT NOT NULL,
            user_id INT NOT NULL,
            rating INT NOT NULL,
            comments TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (event_id) REFERENCES Events(event_id),
            FOREIGN KEY (user_id) REFERENCES Users(user_id)
        )";
        $this->conn->query($sql);
    }

    public function testEventStatistics()
    {
        // Insert test data
        $this->insertTestData();

        // Get event statistics
        $sql = "SELECT 
                COUNT(*) as total_events,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_events,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_events,
                SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected_events
                FROM Events";
        $result = $this->conn->query($sql);
        $stats = $result->fetch_assoc();

        $this->assertEquals(3, $stats['total_events']);
        $this->assertEquals(1, $stats['approved_events']);
        $this->assertEquals(1, $stats['pending_events']);
        $this->assertEquals(1, $stats['rejected_events']);
    }

    public function testRegistrationStatistics()
    {
        // Insert test data
        $this->insertTestData();

        // Get registration statistics
        $sql = "SELECT 
                COUNT(*) as total_registrations,
                COUNT(DISTINCT user_id) as unique_users,
                COUNT(DISTINCT event_id) as events_with_registrations
                FROM Registrations";
        $result = $this->conn->query($sql);
        $stats = $result->fetch_assoc();

        $this->assertEquals(4, $stats['total_registrations']);
        $this->assertEquals(2, $stats['unique_users']);
        $this->assertEquals(2, $stats['events_with_registrations']);
    }

    public function testFeedbackStatistics()
    {
        // Insert test data
        $this->insertTestData();

        // Get feedback statistics
        $sql = "SELECT 
                COUNT(*) as total_feedback,
                AVG(rating) as average_rating,
                MIN(rating) as min_rating,
                MAX(rating) as max_rating
                FROM Feedback";
        $result = $this->conn->query($sql);
        $stats = $result->fetch_assoc();

        $this->assertEquals(3, $stats['total_feedback']);
        $this->assertEquals(4.0, $stats['average_rating']);
        $this->assertEquals(3, $stats['min_rating']);
        $this->assertEquals(5, $stats['max_rating']);
    }

    public function testUserStatistics()
    {
        // Insert test data
        $this->insertTestData();

        // Get user statistics
        $sql = "SELECT 
                COUNT(*) as total_users,
                SUM(CASE WHEN role = 'student' THEN 1 ELSE 0 END) as student_count,
                SUM(CASE WHEN role = 'organizer' THEN 1 ELSE 0 END) as organizer_count,
                SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admin_count
                FROM Users";
        $result = $this->conn->query($sql);
        $stats = $result->fetch_assoc();

        $this->assertEquals(3, $stats['total_users']);
        $this->assertEquals(1, $stats['student_count']);
        $this->assertEquals(1, $stats['organizer_count']);
        $this->assertEquals(1, $stats['admin_count']);
    }

    public function testEventCategoryStatistics()
    {
        // Insert test data
        $this->insertTestData();

        // Get category statistics
        $sql = "SELECT 
                category,
                COUNT(*) as event_count,
                AVG(max_capacity) as avg_capacity
                FROM Events
                GROUP BY category";
        $result = $this->conn->query($sql);
        $categories = $result->fetch_all(MYSQLI_ASSOC);

        $this->assertEquals(2, count($categories));
        $this->assertEquals(2, $categories[0]['event_count']);
        $this->assertEquals(1, $categories[1]['event_count']);
    }

    private function insertTestData()
    {
        // Insert test users
        $sql = "INSERT INTO Users (first_name, last_name, email, password, role) 
                VALUES 
                ('Student', 'User', 'student@example.com', 'password', 'student'),
                ('Organizer', 'User', 'organizer@example.com', 'password', 'organizer'),
                ('Admin', 'User', 'admin@example.com', 'password', 'admin')";
        $this->conn->query($sql);

        // Insert test events
        $sql = "INSERT INTO Events (title, description, start_datetime, end_datetime, 
                location, category, max_capacity, status, organizer_id) 
                VALUES 
                ('Event 1', 'Description 1', '2024-05-01 10:00:00', '2024-05-01 12:00:00', 
                'Location 1', 'Category 1', 100, 'approved', 2),
                ('Event 2', 'Description 2', '2024-05-02 10:00:00', '2024-05-02 12:00:00', 
                'Location 2', 'Category 1', 150, 'pending', 2),
                ('Event 3', 'Description 3', '2024-05-03 10:00:00', '2024-05-03 12:00:00', 
                'Location 3', 'Category 2', 200, 'rejected', 2)";
        $this->conn->query($sql);

        // Insert test registrations
        $sql = "INSERT INTO Registrations (event_id, user_id) 
                VALUES 
                (1, 1), (1, 2), (2, 1), (2, 2)";
        $this->conn->query($sql);

        // Insert test feedback
        $sql = "INSERT INTO Feedback (event_id, user_id, rating, comments) 
                VALUES 
                (1, 1, 5, 'Great event!'),
                (1, 2, 4, 'Good event'),
                (2, 1, 3, 'Average event')";
        $this->conn->query($sql);
    }
} 