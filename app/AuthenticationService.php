<?php

class AuthenticationService {
    private $conn;

    public function __construct(mysqli $conn) {
        $this->conn = $conn;
    }

    public function login(string $email, string $password): array {
        if (empty($email) || empty($password)) {
            return ['status' => 'error', 'message' => 'Email and password are required'];
        }

        $stmt = $this->conn->prepare("SELECT user_id, password, role FROM users WHERE email = ?");
        if (!$stmt) {
            return ['status' => 'error', 'message' => 'Failed to prepare statement'];
        }

        $stmt->bind_param("s", $email);
        if (!$stmt->execute()) {
            return ['status' => 'error', 'message' => 'Failed to execute query'];
        }

        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if (!$user) {
            return ['status' => 'error', 'message' => 'User not found'];
        }

        if (!password_verify($password, $user['password'])) {
            return ['status' => 'error', 'message' => 'Invalid password'];
        }

        return [
            'status' => 'success',
            'message' => 'Login successful',
            'user_id' => $user['user_id'],
            'role' => $user['role']
        ];
    }

    public function register(array $userData): array {
        $requiredFields = ['first_name', 'last_name', 'email', 'password', 'role'];
        foreach ($requiredFields as $field) {
            if (!isset($userData[$field]) || empty($userData[$field])) {
                return ['status' => 'error', 'message' => "Missing required field: $field"];
            }
        }

        // Validate email format
        if (!filter_var($userData['email'], FILTER_VALIDATE_EMAIL)) {
            return ['status' => 'error', 'message' => 'Invalid email format'];
        }

        // Check if email already exists
        if ($this->emailExists($userData['email'])) {
            return ['status' => 'error', 'message' => 'Email already registered'];
        }

        // Validate role
        if (!in_array($userData['role'], ['student', 'organizer', 'admin'])) {
            return ['status' => 'error', 'message' => 'Invalid role'];
        }

        // Hash password
        $hashedPassword = password_hash($userData['password'], PASSWORD_DEFAULT);

        $stmt = $this->conn->prepare("INSERT INTO users (first_name, last_name, email, password, role) VALUES (?, ?, ?, ?, ?)");
        if (!$stmt) {
            return ['status' => 'error', 'message' => 'Failed to prepare statement'];
        }

        $stmt->bind_param("sssss", 
            $userData['first_name'],
            $userData['last_name'],
            $userData['email'],
            $hashedPassword,
            $userData['role']
        );

        if (!$stmt->execute()) {
            return ['status' => 'error', 'message' => 'Failed to create user'];
        }

        return [
            'status' => 'success',
            'message' => 'Registration successful',
            'user_id' => $stmt->insert_id
        ];
    }

    public function changePassword(int $userId, string $currentPassword, string $newPassword): array {
        if (empty($currentPassword) || empty($newPassword)) {
            return ['status' => 'error', 'message' => 'Current and new passwords are required'];
        }

        // Get current password hash
        $stmt = $this->conn->prepare("SELECT password FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if (!$user) {
            return ['status' => 'error', 'message' => 'User not found'];
        }

        if (!password_verify($currentPassword, $user['password'])) {
            return ['status' => 'error', 'message' => 'Current password is incorrect'];
        }

        // Hash and update new password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        $stmt->bind_param("si", $hashedPassword, $userId);

        if (!$stmt->execute()) {
            return ['status' => 'error', 'message' => 'Failed to update password'];
        }

        return ['status' => 'success', 'message' => 'Password updated successfully'];
    }

    private function emailExists(string $email): bool {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row['count'] > 0;
    }
} 