<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../app/AuthenticationService.php';

class AuthenticationServiceTest extends TestCase {
    private $mysqli;
    private $service;

    protected function setUp(): void {
        $this->mysqli = $this->createMock(mysqli::class);
        $this->service = new AuthenticationService($this->mysqli);
    }

    public function testLoginSuccess() {
        $email = 'test@example.com';
        $password = 'password123';
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $this->getMockBuilder('mysqli_stmt')
            ->disableOriginalConstructor()
            ->getMock();
        
        $result = $this->getMockBuilder('mysqli_result')
            ->disableOriginalConstructor()
            ->getMock();
        
        $result->method('fetch_assoc')->willReturn([
            'user_id' => 1,
            'password' => $hashedPassword,
            'role' => 'student'
        ]);

        $stmt->method('bind_param')->willReturn(true);
        $stmt->method('execute')->willReturn(true);
        $stmt->method('get_result')->willReturn($result);

        $this->mysqli->method('prepare')->willReturn($stmt);

        $result = $this->service->login($email, $password);
        $this->assertEquals('success', $result['status']);
        $this->assertEquals('Login successful', $result['message']);
        $this->assertEquals(1, $result['user_id']);
        $this->assertEquals('student', $result['role']);
    }

    public function testLoginInvalidPassword() {
        $email = 'test@example.com';
        $password = 'wrongpassword';
        $hashedPassword = password_hash('correctpassword', PASSWORD_DEFAULT);

        $stmt = $this->getMockBuilder('mysqli_stmt')
            ->disableOriginalConstructor()
            ->getMock();
        
        $result = $this->getMockBuilder('mysqli_result')
            ->disableOriginalConstructor()
            ->getMock();
        
        $result->method('fetch_assoc')->willReturn([
            'user_id' => 1,
            'password' => $hashedPassword,
            'role' => 'student'
        ]);

        $stmt->method('bind_param')->willReturn(true);
        $stmt->method('execute')->willReturn(true);
        $stmt->method('get_result')->willReturn($result);

        $this->mysqli->method('prepare')->willReturn($stmt);

        $result = $this->service->login($email, $password);
        $this->assertEquals('error', $result['status']);
        $this->assertEquals('Invalid password', $result['message']);
    }

    public function testRegisterSuccess() {
        $userData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'role' => 'student'
        ];

        // Mock email check
        $checkStmt = $this->getMockBuilder('mysqli_stmt')
            ->disableOriginalConstructor()
            ->getMock();
        
        $checkResult = $this->getMockBuilder('mysqli_result')
            ->disableOriginalConstructor()
            ->getMock();
        
        $checkResult->method('fetch_assoc')->willReturn(['count' => 0]);
        $checkStmt->method('bind_param')->willReturn(true);
        $checkStmt->method('execute')->willReturn(true);
        $checkStmt->method('get_result')->willReturn($checkResult);

        // Mock insert
        $insertStmt = new class {
            public $insert_id = 1;
            public function bind_param() { return true; }
            public function execute() { return true; }
        };

        $this->mysqli->method('prepare')
            ->will($this->onConsecutiveCalls($checkStmt, $insertStmt));

        $result = $this->service->register($userData);
        $this->assertEquals('success', $result['status']);
        $this->assertEquals('Registration successful', $result['message']);
        $this->assertEquals(1, $result['user_id']);
    }

    public function testRegisterEmailExists() {
        $userData = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'role' => 'student'
        ];

        $stmt = $this->getMockBuilder('mysqli_stmt')
            ->disableOriginalConstructor()
            ->getMock();
        
        $result = $this->getMockBuilder('mysqli_result')
            ->disableOriginalConstructor()
            ->getMock();
        
        $result->method('fetch_assoc')->willReturn(['count' => 1]);
        $stmt->method('bind_param')->willReturn(true);
        $stmt->method('execute')->willReturn(true);
        $stmt->method('get_result')->willReturn($result);

        $this->mysqli->method('prepare')->willReturn($stmt);

        $result = $this->service->register($userData);
        $this->assertEquals('error', $result['status']);
        $this->assertEquals('Email already registered', $result['message']);
    }

    public function testChangePasswordSuccess() {
        $userId = 1;
        $currentPassword = 'oldpassword';
        $newPassword = 'newpassword';
        $hashedOldPassword = password_hash($currentPassword, PASSWORD_DEFAULT);

        // Mock password check
        $checkStmt = $this->getMockBuilder('mysqli_stmt')
            ->disableOriginalConstructor()
            ->getMock();
        
        $checkResult = $this->getMockBuilder('mysqli_result')
            ->disableOriginalConstructor()
            ->getMock();
        
        $checkResult->method('fetch_assoc')->willReturn(['password' => $hashedOldPassword]);
        $checkStmt->method('bind_param')->willReturn(true);
        $checkStmt->method('execute')->willReturn(true);
        $checkStmt->method('get_result')->willReturn($checkResult);

        // Mock update
        $updateStmt = $this->getMockBuilder('mysqli_stmt')
            ->disableOriginalConstructor()
            ->getMock();
        
        $updateStmt->method('bind_param')->willReturn(true);
        $updateStmt->method('execute')->willReturn(true);

        $this->mysqli->method('prepare')
            ->will($this->onConsecutiveCalls($checkStmt, $updateStmt));

        $result = $this->service->changePassword($userId, $currentPassword, $newPassword);
        $this->assertEquals('success', $result['status']);
        $this->assertEquals('Password updated successfully', $result['message']);
    }

    public function testChangePasswordIncorrectCurrent() {
        $userId = 1;
        $currentPassword = 'wrongpassword';
        $newPassword = 'newpassword';
        $hashedPassword = password_hash('correctpassword', PASSWORD_DEFAULT);

        $stmt = $this->getMockBuilder('mysqli_stmt')
            ->disableOriginalConstructor()
            ->getMock();
        
        $result = $this->getMockBuilder('mysqli_result')
            ->disableOriginalConstructor()
            ->getMock();
        
        $result->method('fetch_assoc')->willReturn(['password' => $hashedPassword]);
        $stmt->method('bind_param')->willReturn(true);
        $stmt->method('execute')->willReturn(true);
        $stmt->method('get_result')->willReturn($result);

        $this->mysqli->method('prepare')->willReturn($stmt);

        $result = $this->service->changePassword($userId, $currentPassword, $newPassword);
        $this->assertEquals('error', $result['status']);
        $this->assertEquals('Current password is incorrect', $result['message']);
    }
} 