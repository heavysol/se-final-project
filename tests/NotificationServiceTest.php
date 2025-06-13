<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../app/NotificationService.php';

class NotificationServiceTest extends TestCase {
    private $mysqli;
    private $service;

    protected function setUp(): void {
        $this->mysqli = $this->createMock(mysqli::class);
        $this->service = new NotificationService($this->mysqli, 1);
    }

    public function testCreateNotificationSuccess() {
        $notificationData = [
            'user_id' => 1,
            'type' => 'event_reminder',
            'message' => 'Test notification'
        ];

        $stmt = new class {
            public $insert_id = 1;
            public function bind_param() { return true; }
            public function execute() { return true; }
        };

        $this->mysqli->method('prepare')->willReturn($stmt);

        $result = $this->service->createNotification($notificationData);
        $this->assertEquals('success', $result['status']);
        $this->assertEquals('Notification created successfully', $result['message']);
        $this->assertEquals(1, $result['notification_id']);
    }

    public function testCreateNotificationInvalidType() {
        $notificationData = [
            'user_id' => 1,
            'type' => 'invalid_type',
            'message' => 'Test notification'
        ];

        $result = $this->service->createNotification($notificationData);
        $this->assertEquals('error', $result['status']);
        $this->assertEquals('Invalid notification type', $result['message']);
    }

    public function testGetUserNotificationsSuccess() {
        $expectedNotifications = [
            [
                'notification_id' => 1,
                'type' => 'event_reminder',
                'message' => 'Test notification 1',
                'is_read' => 0,
                'created_at' => '2024-05-01 10:00:00'
            ],
            [
                'notification_id' => 2,
                'type' => 'registration',
                'message' => 'Test notification 2',
                'is_read' => 1,
                'created_at' => '2024-05-01 11:00:00'
            ]
        ];

        $stmt = $this->getMockBuilder('mysqli_stmt')
            ->disableOriginalConstructor()
            ->getMock();
        
        $result = $this->getMockBuilder('mysqli_result')
            ->disableOriginalConstructor()
            ->getMock();
        
        $result->method('fetch_assoc')
            ->will($this->onConsecutiveCalls($expectedNotifications[0], $expectedNotifications[1], null));

        $stmt->method('bind_param')->willReturn(true);
        $stmt->method('execute')->willReturn(true);
        $stmt->method('get_result')->willReturn($result);

        $this->mysqli->method('prepare')->willReturn($stmt);

        $result = $this->service->getUserNotifications();
        $this->assertEquals('success', $result['status']);
        $this->assertEquals('Notifications retrieved successfully', $result['message']);
        $this->assertCount(2, $result['notifications']);
        $this->assertEquals('Test notification 1', $result['notifications'][0]['message']);
        $this->assertEquals('Test notification 2', $result['notifications'][1]['message']);
    }

    public function testMarkAsReadSuccess() {
        // Mock the notification check
        $checkStmt = $this->getMockBuilder('mysqli_stmt')
            ->disableOriginalConstructor()
            ->getMock();
        
        $checkResult = $this->getMockBuilder('mysqli_result')
            ->disableOriginalConstructor()
            ->getMock();
        
        $checkResult->method('fetch_assoc')->willReturn(['user_id' => 1]);
        $checkStmt->method('bind_param')->willReturn(true);
        $checkStmt->method('execute')->willReturn(true);
        $checkStmt->method('get_result')->willReturn($checkResult);

        // Mock the update
        $updateStmt = $this->getMockBuilder('mysqli_stmt')
            ->disableOriginalConstructor()
            ->getMock();
        
        $updateStmt->method('bind_param')->willReturn(true);
        $updateStmt->method('execute')->willReturn(true);

        $this->mysqli->method('prepare')
            ->will($this->onConsecutiveCalls($checkStmt, $updateStmt));

        $result = $this->service->markAsRead(1);
        $this->assertEquals('success', $result['status']);
        $this->assertEquals('Notification marked as read', $result['message']);
    }

    public function testMarkAsReadUnauthorized() {
        $stmt = $this->getMockBuilder('mysqli_stmt')
            ->disableOriginalConstructor()
            ->getMock();
        
        $result = $this->getMockBuilder('mysqli_result')
            ->disableOriginalConstructor()
            ->getMock();
        
        $result->method('fetch_assoc')->willReturn(['user_id' => 2]);
        $stmt->method('bind_param')->willReturn(true);
        $stmt->method('execute')->willReturn(true);
        $stmt->method('get_result')->willReturn($result);

        $this->mysqli->method('prepare')->willReturn($stmt);

        $result = $this->service->markAsRead(1);
        $this->assertEquals('error', $result['status']);
        $this->assertEquals('Not authorized to update this notification', $result['message']);
    }

    public function testMarkAllAsReadSuccess() {
        $stmt = new class {
            public $affected_rows = 5;
            public function bind_param() { return true; }
            public function execute() { return true; }
        };

        $this->mysqli->method('prepare')->willReturn($stmt);

        $result = $this->service->markAllAsRead();
        $this->assertEquals('success', $result['status']);
        $this->assertEquals('All notifications marked as read', $result['message']);
        $this->assertEquals(5, $result['updated_count']);
    }

    public function testDeleteNotificationSuccess() {
        // Mock the notification check
        $checkStmt = $this->getMockBuilder('mysqli_stmt')
            ->disableOriginalConstructor()
            ->getMock();
        
        $checkResult = $this->getMockBuilder('mysqli_result')
            ->disableOriginalConstructor()
            ->getMock();
        
        $checkResult->method('fetch_assoc')->willReturn(['user_id' => 1]);
        $checkStmt->method('bind_param')->willReturn(true);
        $checkStmt->method('execute')->willReturn(true);
        $checkStmt->method('get_result')->willReturn($checkResult);

        // Mock the delete
        $deleteStmt = $this->getMockBuilder('mysqli_stmt')
            ->disableOriginalConstructor()
            ->getMock();
        
        $deleteStmt->method('bind_param')->willReturn(true);
        $deleteStmt->method('execute')->willReturn(true);

        $this->mysqli->method('prepare')
            ->will($this->onConsecutiveCalls($checkStmt, $deleteStmt));

        $result = $this->service->deleteNotification(1);
        $this->assertEquals('success', $result['status']);
        $this->assertEquals('Notification deleted successfully', $result['message']);
    }

    public function testDeleteNotificationUnauthorized() {
        $stmt = $this->getMockBuilder('mysqli_stmt')
            ->disableOriginalConstructor()
            ->getMock();
        
        $result = $this->getMockBuilder('mysqli_result')
            ->disableOriginalConstructor()
            ->getMock();
        
        $result->method('fetch_assoc')->willReturn(['user_id' => 2]);
        $stmt->method('bind_param')->willReturn(true);
        $stmt->method('execute')->willReturn(true);
        $stmt->method('get_result')->willReturn($result);

        $this->mysqli->method('prepare')->willReturn($stmt);

        $result = $this->service->deleteNotification(1);
        $this->assertEquals('error', $result['status']);
        $this->assertEquals('Not authorized to delete this notification', $result['message']);
    }
} 