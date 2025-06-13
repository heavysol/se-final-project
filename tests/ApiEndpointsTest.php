<?php

namespace Tests\Api;

use PHPUnit\Framework\TestCase;
use Mockery;

class ApiEndpointsTest extends TestCase
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
            role ENUM('student', 'organizer', 'admin') NOT NULL,
            api_key VARCHAR(255) UNIQUE
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

        // Create API_Logs table
        $sql = "CREATE TABLE IF NOT EXISTS API_Logs (
            log_id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            endpoint VARCHAR(255) NOT NULL,
            method VARCHAR(10) NOT NULL,
            request_data TEXT,
            response_data TEXT,
            status_code INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES Users(user_id)
        )";
        $this->conn->query($sql);
    }

    public function testApiAuthentication()
    {
        // Insert test user with API key
        $this->insertTestData();

        // Test valid API key
        $sql = "SELECT user_id FROM Users WHERE api_key = ?";
        $stmt = $this->conn->prepare($sql);
        $apiKey = 'test_api_key';
        $stmt->bind_param("s", $apiKey);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        $this->assertNotNull($user);
        $this->assertEquals(1, $user['user_id']);

        // Test invalid API key
        $stmt = $this->conn->prepare($sql);
        $invalidApiKey = 'invalid_key';
        $stmt->bind_param("s", $invalidApiKey);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        $this->assertNull($user);
    }

    public function testGetEventsEndpoint()
    {
        // Insert test data
        $this->insertTestData();

        // Mock API request
        $sql = "INSERT INTO API_Logs (user_id, endpoint, method, status_code) 
                VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $userId = 1;
        $endpoint = '/api/events';
        $method = 'GET';
        $statusCode = 200;
        $stmt->bind_param("issi", $userId, $endpoint, $method, $statusCode);
        $result = $stmt->execute();

        $this->assertTrue($result);

        // Verify API log
        $sql = "SELECT * FROM API_Logs WHERE endpoint = ? AND method = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ss", $endpoint, $method);
        $stmt->execute();
        $result = $stmt->get_result();
        $log = $result->fetch_assoc();

        $this->assertEquals($userId, $log['user_id']);
        $this->assertEquals($endpoint, $log['endpoint']);
        $this->assertEquals($method, $log['method']);
        $this->assertEquals($statusCode, $log['status_code']);
    }

    public function testCreateEventEndpoint()
    {
        // Insert test data
        $this->insertTestData();

        // Mock API request
        $requestData = json_encode([
            'title' => 'New Event',
            'description' => 'Event Description',
            'start_datetime' => '2024-05-01 10:00:00',
            'end_datetime' => '2024-05-01 12:00:00',
            'location' => 'Event Location',
            'category' => 'Event Category',
            'max_capacity' => 100
        ]);

        $sql = "INSERT INTO API_Logs (user_id, endpoint, method, request_data, status_code) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $userId = 1;
        $endpoint = '/api/events';
        $method = 'POST';
        $statusCode = 201;
        $stmt->bind_param("isssi", $userId, $endpoint, $method, $requestData, $statusCode);
        $result = $stmt->execute();

        $this->assertTrue($result);

        // Create the event
        $sql = "INSERT INTO Events (title, description, start_datetime, end_datetime, 
                location, category, max_capacity, organizer_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $title = 'New Event';
        $description = 'Event Description';
        $startDatetime = '2024-05-01 10:00:00';
        $endDatetime = '2024-05-01 12:00:00';
        $location = 'Event Location';
        $category = 'Event Category';
        $maxCapacity = 100;
        $organizerId = 1;
        $stmt->bind_param("ssssssii", $title, $description, $startDatetime, $endDatetime, 
                         $location, $category, $maxCapacity, $organizerId);
        $result = $stmt->execute();

        $this->assertTrue($result);

        // Verify event was created
        $sql = "SELECT * FROM Events WHERE title = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $title);
        $stmt->execute();
        $result = $stmt->get_result();
        $event = $result->fetch_assoc();

        $this->assertNotNull($event);
        $this->assertEquals('New Event', $event['title']);
        $this->assertEquals('Event Description', $event['description']);
        $this->assertEquals('Event Location', $event['location']);
    }

    public function testUpdateEventEndpoint()
    {
        // Insert test data
        $this->insertTestData();

        // Mock API request
        $requestData = json_encode([
            'title' => 'Updated Event',
            'description' => 'Updated Description'
        ]);

        $sql = "INSERT INTO API_Logs (user_id, endpoint, method, request_data, status_code) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $userId = 1;
        $endpoint = '/api/events/1';
        $method = 'PUT';
        $statusCode = 200;
        $stmt->bind_param("isssi", $userId, $endpoint, $method, $requestData, $statusCode);
        $result = $stmt->execute();

        $this->assertTrue($result);

        // Verify event was updated
        $sql = "UPDATE Events SET title = ?, description = ? WHERE event_id = ?";
        $stmt = $this->conn->prepare($sql);
        $title = 'Updated Event';
        $description = 'Updated Description';
        $eventId = 1;
        $stmt->bind_param("ssi", $title, $description, $eventId);
        $result = $stmt->execute();

        $this->assertTrue($result);
    }

    public function testDeleteEventEndpoint()
    {
        // Insert test data
        $this->insertTestData();

        // Mock API request
        $sql = "INSERT INTO API_Logs (user_id, endpoint, method, status_code) 
                VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $userId = 1;
        $endpoint = '/api/events/1';
        $method = 'DELETE';
        $statusCode = 204;
        $stmt->bind_param("issi", $userId, $endpoint, $method, $statusCode);
        $result = $stmt->execute();

        $this->assertTrue($result);

        // Verify event was deleted
        $sql = "DELETE FROM Events WHERE event_id = ?";
        $stmt = $this->conn->prepare($sql);
        $eventId = 1;
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

    private function insertTestData()
    {
        // Insert test user with API key
        $sql = "INSERT INTO Users (first_name, last_name, email, password, role, api_key) 
                VALUES ('Test', 'User', 'test@example.com', 'password', 'organizer', 'test_api_key')";
        $this->conn->query($sql);

        // Insert test event
        $sql = "INSERT INTO Events (title, description, start_datetime, end_datetime, 
                location, category, max_capacity, organizer_id) 
                VALUES ('Test Event', 'Description', '2024-05-01 10:00:00', 
                '2024-05-01 12:00:00', 'Location', 'Category', 100, 1)";
        $this->conn->query($sql);
    }
} 