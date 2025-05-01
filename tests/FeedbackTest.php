<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Mockery;

class FeedbackTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        // Clean up global variables
        unset($_SESSION['user_id'], $_SESSION['role']);
        unset($_POST['event_id'], $_POST['action']);
    }

    public function testGetFeedbackForEvent()
    {
        // Mock the database connection
        $mockConn = Mockery::mock('mysqli');
        $mockStmt = Mockery::mock('mysqli_stmt');
        $mockResult = Mockery::mock('mysqli_result');

        // Set up the mock expectations
        $mockConn->shouldReceive('prepare')
                ->once()
                ->with("SELECT event_id FROM Events WHERE event_id = ? AND organizer_id = ?")
                ->andReturn($mockStmt);

        $mockStmt->shouldReceive('bind_param')
                ->once()
                ->with("ii", 1, 1)
                ->andReturn(true);

        $mockStmt->shouldReceive('execute')
                ->once()
                ->andReturn(true);

        $mockStmt->shouldReceive('get_result')
                ->once()
                ->andReturn($mockResult);

        $mockResult->shouldReceive('num_rows')
                  ->once()
                  ->andReturn(1);

        // Mock the feedback query
        $mockConn->shouldReceive('prepare')
                ->once()
                ->with("SELECT f.rating, f.comments, f.created_at, u.first_name, u.last_name FROM Feedback f JOIN Users u ON f.user_id = u.user_id WHERE f.event_id = ? ORDER BY f.created_at DESC")
                ->andReturn($mockStmt);

        $mockStmt->shouldReceive('bind_param')
                ->once()
                ->with("i", 1)
                ->andReturn(true);

        $mockStmt->shouldReceive('execute')
                ->once()
                ->andReturn(true);

        $mockStmt->shouldReceive('get_result')
                ->once()
                ->andReturn($mockResult);

        // Mock the result data
        $mockResult->shouldReceive('fetch_assoc')
                  ->times(2)
                  ->andReturn(
                      [
                          'rating' => 5,
                          'comments' => 'Great event!',
                          'created_at' => '2024-01-01 12:00:00',
                          'first_name' => 'John',
                          'last_name' => 'Doe'
                      ],
                      false
                  );

        // Replace the global $conn with our mock
        global $conn;
        $conn = $mockConn;

        // Set up the session
        $_SESSION['user_id'] = 1;
        $_SESSION['role'] = 'organizer';

        // Set up the POST data
        $_POST['event_id'] = 1;
        $_POST['action'] = 'getEventFeedback';

        // Include the file to test
        ob_start();
        require __DIR__ . '/../views/dashboards/organiser/actions/get_feedback.php';
        $output = ob_get_clean();

        // Decode the JSON response
        $response = json_decode($output, true);

        // Assert the response
        $this->assertEquals('success', $response['status']);
        $this->assertCount(1, $response['feedback']);
        $this->assertEquals(5, $response['feedback'][0]['rating']);
        $this->assertEquals('Great event!', $response['feedback'][0]['comments']);
        $this->assertEquals('John Doe', $response['feedback'][0]['username']);
    }

    public function testUnauthorizedAccess()
    {
        // Set up the session with invalid role
        $_SESSION['user_id'] = 1;
        $_SESSION['role'] = 'student';

        // Set up the POST data
        $_POST['event_id'] = 1;
        $_POST['action'] = 'getEventFeedback';

        // Include the file to test
        ob_start();
        require __DIR__ . '/../views/dashboards/organiser/actions/get_feedback.php';
        $output = ob_get_clean();

        // Decode the JSON response
        $response = json_decode($output, true);

        // Assert the response
        $this->assertEquals('error', $response['status']);
        $this->assertEquals('Unauthorized access', $response['message']);
    }

    public function testInvalidRequest()
    {
        // Set up the session
        $_SESSION['user_id'] = 1;
        $_SESSION['role'] = 'organizer';

        // Don't set POST data to simulate invalid request

        // Include the file to test
        ob_start();
        require __DIR__ . '/../views/dashboards/organiser/actions/get_feedback.php';
        $output = ob_get_clean();

        // Decode the JSON response
        $response = json_decode($output, true);

        // Assert the response
        $this->assertEquals('error', $response['status']);
        $this->assertEquals('Invalid request', $response['message']);
    }

    public function testDatabaseConnectionError()
    {
        // Mock a failed database connection
        $mockConn = Mockery::mock('mysqli');
        $mockConn->shouldReceive('prepare')
                ->once()
                ->andThrow(new \Exception("Database connection failed"));

        global $conn;
        $conn = $mockConn;

        // Set up valid session and POST data
        $_SESSION['user_id'] = 1;
        $_SESSION['role'] = 'organizer';
        $_POST['event_id'] = 1;
        $_POST['action'] = 'getEventFeedback';

        // Include the file to test
        ob_start();
        require __DIR__ . '/../views/dashboards/organiser/actions/get_feedback.php';
        $output = ob_get_clean();

        // Decode the JSON response
        $response = json_decode($output, true);

        // Assert the response
        $this->assertEquals('error', $response['status']);
        $this->assertStringContainsString('Database connection failed', $response['message']);
    }

    public function testNoFeedbackFound()
    {
        // Mock the database connection
        $mockConn = Mockery::mock('mysqli');
        $mockStmt = Mockery::mock('mysqli_stmt');
        $mockResult = Mockery::mock('mysqli_result');

        // Set up the mock expectations for event verification
        $mockConn->shouldReceive('prepare')
                ->once()
                ->with("SELECT event_id FROM Events WHERE event_id = ? AND organizer_id = ?")
                ->andReturn($mockStmt);

        $mockStmt->shouldReceive('bind_param')
                ->once()
                ->with("ii", 1, 1)
                ->andReturn(true);

        $mockStmt->shouldReceive('execute')
                ->once()
                ->andReturn(true);

        $mockStmt->shouldReceive('get_result')
                ->once()
                ->andReturn($mockResult);

        $mockResult->shouldReceive('num_rows')
                  ->once()
                  ->andReturn(1);

        // Mock the feedback query with no results
        $mockConn->shouldReceive('prepare')
                ->once()
                ->with("SELECT f.rating, f.comments, f.created_at, u.first_name, u.last_name FROM Feedback f JOIN Users u ON f.user_id = u.user_id WHERE f.event_id = ? ORDER BY f.created_at DESC")
                ->andReturn($mockStmt);

        $mockStmt->shouldReceive('bind_param')
                ->once()
                ->with("i", 1)
                ->andReturn(true);

        $mockStmt->shouldReceive('execute')
                ->once()
                ->andReturn(true);

        $mockStmt->shouldReceive('get_result')
                ->once()
                ->andReturn($mockResult);

        $mockResult->shouldReceive('fetch_assoc')
                  ->once()
                  ->andReturn(false);

        // Replace the global $conn with our mock
        global $conn;
        $conn = $mockConn;

        // Set up the session
        $_SESSION['user_id'] = 1;
        $_SESSION['role'] = 'organizer';

        // Set up the POST data
        $_POST['event_id'] = 1;
        $_POST['action'] = 'getEventFeedback';

        // Include the file to test
        ob_start();
        require __DIR__ . '/../views/dashboards/organiser/actions/get_feedback.php';
        $output = ob_get_clean();

        // Decode the JSON response
        $response = json_decode($output, true);

        // Assert the response
        $this->assertEquals('success', $response['status']);
        $this->assertEmpty($response['feedback']);
    }

    public function testMultipleFeedbackItems()
    {
        // Mock the database connection
        $mockConn = Mockery::mock('mysqli');
        $mockStmt = Mockery::mock('mysqli_stmt');
        $mockResult = Mockery::mock('mysqli_result');

        // Set up the mock expectations for event verification
        $mockConn->shouldReceive('prepare')
                ->once()
                ->with("SELECT event_id FROM Events WHERE event_id = ? AND organizer_id = ?")
                ->andReturn($mockStmt);

        $mockStmt->shouldReceive('bind_param')
                ->once()
                ->with("ii", 1, 1)
                ->andReturn(true);

        $mockStmt->shouldReceive('execute')
                ->once()
                ->andReturn(true);

        $mockStmt->shouldReceive('get_result')
                ->once()
                ->andReturn($mockResult);

        $mockResult->shouldReceive('num_rows')
                  ->once()
                  ->andReturn(1);

        // Mock the feedback query with multiple results
        $mockConn->shouldReceive('prepare')
                ->once()
                ->with("SELECT f.rating, f.comments, f.created_at, u.first_name, u.last_name FROM Feedback f JOIN Users u ON f.user_id = u.user_id WHERE f.event_id = ? ORDER BY f.created_at DESC")
                ->andReturn($mockStmt);

        $mockStmt->shouldReceive('bind_param')
                ->once()
                ->with("i", 1)
                ->andReturn(true);

        $mockStmt->shouldReceive('execute')
                ->once()
                ->andReturn(true);

        $mockStmt->shouldReceive('get_result')
                ->once()
                ->andReturn($mockResult);

        // Mock multiple feedback items
        $mockResult->shouldReceive('fetch_assoc')
                  ->times(4)
                  ->andReturn(
                      [
                          'rating' => 5,
                          'comments' => 'Great event!',
                          'created_at' => '2024-01-01 12:00:00',
                          'first_name' => 'John',
                          'last_name' => 'Doe'
                      ],
                      [
                          'rating' => 4,
                          'comments' => 'Good event',
                          'created_at' => '2024-01-01 11:00:00',
                          'first_name' => 'Jane',
                          'last_name' => 'Smith'
                      ],
                      [
                          'rating' => 3,
                          'comments' => 'Average event',
                          'created_at' => '2024-01-01 10:00:00',
                          'first_name' => 'Bob',
                          'last_name' => 'Johnson'
                      ],
                      false
                  );

        // Replace the global $conn with our mock
        global $conn;
        $conn = $mockConn;

        // Set up the session
        $_SESSION['user_id'] = 1;
        $_SESSION['role'] = 'organizer';

        // Set up the POST data
        $_POST['event_id'] = 1;
        $_POST['action'] = 'getEventFeedback';

        // Include the file to test
        ob_start();
        require __DIR__ . '/../views/dashboards/organiser/actions/get_feedback.php';
        $output = ob_get_clean();

        // Decode the JSON response
        $response = json_decode($output, true);

        // Assert the response
        $this->assertEquals('success', $response['status']);
        $this->assertCount(3, $response['feedback']);
        
        // Verify the first feedback item
        $this->assertEquals(5, $response['feedback'][0]['rating']);
        $this->assertEquals('Great event!', $response['feedback'][0]['comments']);
        $this->assertEquals('John Doe', $response['feedback'][0]['username']);
        
        // Verify the second feedback item
        $this->assertEquals(4, $response['feedback'][1]['rating']);
        $this->assertEquals('Good event', $response['feedback'][1]['comments']);
        $this->assertEquals('Jane Smith', $response['feedback'][1]['username']);
        
        // Verify the third feedback item
        $this->assertEquals(3, $response['feedback'][2]['rating']);
        $this->assertEquals('Average event', $response['feedback'][2]['comments']);
        $this->assertEquals('Bob Johnson', $response['feedback'][2]['username']);
    }

    public function testInvalidEventId()
    {
        // Mock the database connection
        $mockConn = Mockery::mock('mysqli');
        $mockStmt = Mockery::mock('mysqli_stmt');
        $mockResult = Mockery::mock('mysqli_result');

        // Set up the mock expectations for event verification
        $mockConn->shouldReceive('prepare')
                ->once()
                ->with("SELECT event_id FROM Events WHERE event_id = ? AND organizer_id = ?")
                ->andReturn($mockStmt);

        $mockStmt->shouldReceive('bind_param')
                ->once()
                ->with("ii", 999, 1)
                ->andReturn(true);

        $mockStmt->shouldReceive('execute')
                ->once()
                ->andReturn(true);

        $mockStmt->shouldReceive('get_result')
                ->once()
                ->andReturn($mockResult);

        $mockResult->shouldReceive('num_rows')
                  ->once()
                  ->andReturn(0);

        // Replace the global $conn with our mock
        global $conn;
        $conn = $mockConn;

        // Set up the session
        $_SESSION['user_id'] = 1;
        $_SESSION['role'] = 'organizer';

        // Set up the POST data with invalid event ID
        $_POST['event_id'] = 999;
        $_POST['action'] = 'getEventFeedback';

        // Include the file to test
        ob_start();
        require __DIR__ . '/../views/dashboards/organiser/actions/get_feedback.php';
        $output = ob_get_clean();

        // Decode the JSON response
        $response = json_decode($output, true);

        // Assert the response
        $this->assertEquals('error', $response['status']);
        $this->assertEquals('Event not found or unauthorized', $response['message']);
    }

    public function testSQLInjectionAttempt()
    {
        // Set up the session
        $_SESSION['user_id'] = 1;
        $_SESSION['role'] = 'organizer';

        // Set up the POST data with SQL injection attempt
        $_POST['event_id'] = "1; DROP TABLE Users; --";
        $_POST['action'] = 'getEventFeedback';

        // Include the file to test
        ob_start();
        require __DIR__ . '/../views/dashboards/organiser/actions/get_feedback.php';
        $output = ob_get_clean();

        // Decode the JSON response
        $response = json_decode($output, true);

        // Assert the response
        $this->assertEquals('error', $response['status']);
        $this->assertStringContainsString('Invalid request', $response['message']);
    }
} 