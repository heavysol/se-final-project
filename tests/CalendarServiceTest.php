<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../app/CalendarService.php';

class CalendarServiceTest extends TestCase {
    private $mysqli;
    private $service;

    protected function setUp(): void {
        $this->mysqli = $this->createMock(mysqli::class);
        $this->service = new CalendarService($this->mysqli, 1);
    }

    public function testGetMonthlyEventsSuccess() {
        $year_month = '2024-05';
        $expectedEvents = [
            [
                'event_id' => 1,
                'title' => 'Test Event',
                'description' => 'Test Description',
                'start_datetime' => '2024-05-01 10:00:00',
                'end_datetime' => '2024-05-01 12:00:00',
                'location' => 'Test Location',
                'category' => 'Test Category',
                'max_capacity' => 100,
                'first_name' => 'John',
                'last_name' => 'Doe'
            ]
        ];

        $stmt = $this->getMockBuilder('mysqli_stmt')
            ->disableOriginalConstructor()
            ->getMock();
        
        $result = $this->getMockBuilder('mysqli_result')
            ->disableOriginalConstructor()
            ->getMock();
        
        $result->method('fetch_assoc')
            ->will($this->onConsecutiveCalls($expectedEvents[0], null));

        $stmt->method('bind_param')->willReturn(true);
        $stmt->method('execute')->willReturn(true);
        $stmt->method('get_result')->willReturn($result);

        $this->mysqli->method('prepare')->willReturn($stmt);

        $result = $this->service->getMonthlyEvents($year_month);
        $this->assertEquals('success', $result['status']);
        $this->assertEquals('Events retrieved successfully', $result['message']);
        $this->assertCount(1, $result['events']);
        $this->assertEquals('Test Event', $result['events'][0]['title']);
        $this->assertEquals('John Doe', $result['events'][0]['organizer']);
    }

    public function testGetMonthlyEventsInvalidFormat() {
        $result = $this->service->getMonthlyEvents('invalid-format');
        $this->assertEquals('error', $result['status']);
        $this->assertEquals('Invalid date format. Use YYYY-MM', $result['message']);
    }

    public function testGetDailyEventsSuccess() {
        $date = '2024-05-01';
        $expectedEvents = [
            [
                'event_id' => 1,
                'title' => 'Test Event',
                'description' => 'Test Description',
                'start_datetime' => '2024-05-01 10:00:00',
                'end_datetime' => '2024-05-01 12:00:00',
                'location' => 'Test Location',
                'category' => 'Test Category',
                'max_capacity' => 100,
                'first_name' => 'John',
                'last_name' => 'Doe',
                'registered_count' => 50
            ]
        ];

        $stmt = $this->getMockBuilder('mysqli_stmt')
            ->disableOriginalConstructor()
            ->getMock();
        
        $result = $this->getMockBuilder('mysqli_result')
            ->disableOriginalConstructor()
            ->getMock();
        
        $result->method('fetch_assoc')
            ->will($this->onConsecutiveCalls($expectedEvents[0], null));

        $stmt->method('bind_param')->willReturn(true);
        $stmt->method('execute')->willReturn(true);
        $stmt->method('get_result')->willReturn($result);

        $this->mysqli->method('prepare')->willReturn($stmt);

        $result = $this->service->getDailyEvents($date);
        $this->assertEquals('success', $result['status']);
        $this->assertEquals('Events retrieved successfully', $result['message']);
        $this->assertCount(1, $result['events']);
        $this->assertEquals('Test Event', $result['events'][0]['title']);
        $this->assertEquals('John Doe', $result['events'][0]['organizer']);
        $this->assertEquals(50, $result['events'][0]['registered_count']);
        $this->assertFalse($result['events'][0]['is_full']);
    }

    public function testGetDailyEventsInvalidFormat() {
        $result = $this->service->getDailyEvents('invalid-format');
        $this->assertEquals('error', $result['status']);
        $this->assertEquals('Invalid date format. Use YYYY-MM-DD', $result['message']);
    }

    public function testGetUserEventsSuccess() {
        $expectedEvents = [
            [
                'event_id' => 1,
                'title' => 'Organized Event',
                'description' => 'Test Description',
                'start_datetime' => '2024-05-01 10:00:00',
                'end_datetime' => '2024-05-01 12:00:00',
                'location' => 'Test Location',
                'category' => 'Test Category',
                'max_capacity' => 100,
                'registered_count' => 50,
                'organizer_id' => 1
            ],
            [
                'event_id' => 2,
                'title' => 'Registered Event',
                'description' => 'Test Description',
                'start_datetime' => '2024-05-02 10:00:00',
                'end_datetime' => '2024-05-02 12:00:00',
                'location' => 'Test Location',
                'category' => 'Test Category',
                'max_capacity' => 100,
                'registered_count' => 75,
                'organizer_id' => 2
            ]
        ];

        $stmt = $this->getMockBuilder('mysqli_stmt')
            ->disableOriginalConstructor()
            ->getMock();
        
        $result = $this->getMockBuilder('mysqli_result')
            ->disableOriginalConstructor()
            ->getMock();
        
        $result->method('fetch_assoc')
            ->will($this->onConsecutiveCalls($expectedEvents[0], $expectedEvents[1], null));

        $stmt->method('bind_param')->willReturn(true);
        $stmt->method('execute')->willReturn(true);
        $stmt->method('get_result')->willReturn($result);

        $this->mysqli->method('prepare')->willReturn($stmt);

        $result = $this->service->getUserEvents();
        $this->assertEquals('success', $result['status']);
        $this->assertEquals('Events retrieved successfully', $result['message']);
        $this->assertCount(1, $result['events']['organizing']);
        $this->assertCount(1, $result['events']['registered']);
        $this->assertEquals('Organized Event', $result['events']['organizing'][0]['title']);
        $this->assertEquals('Registered Event', $result['events']['registered'][0]['title']);
    }
} 