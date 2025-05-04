<?php
session_start();
require '../db/config.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

$error_message = '';
$success_message = '';
$show_error_image = false;

// Debug: Log POST data
error_log('POST data: ' . print_r($_POST, true));

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $role = trim($_POST['role']);

    error_log("Processing signup - Email: $email, Role: $role");

    // Basic validation
    if (empty($first_name) || empty($last_name) || empty($email) || empty($password) || empty($confirm_password) || empty($role)) {
        $error_message = "All fields are required";
        error_log("Validation failed: Empty fields");
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format";
        error_log("Validation failed: Invalid email format");
    } elseif (strlen($password) < 8) {
        $error_message = "Password must be at least 8 characters long";
        error_log("Validation failed: Password too short");
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $error_message = "Password must include at least one uppercase letter";
        error_log("Validation failed: No uppercase letter");
    } elseif (!preg_match('/[a-z]/', $password)) {
        $error_message = "Password must include at least one lowercase letter";
        error_log("Validation failed: No lowercase letter");
    } elseif (!preg_match('/[0-9]/', $password)) {
        $error_message = "Password must include at least one number";
        error_log("Validation failed: No number");
    } elseif (!preg_match('/[\W]/', $password)) {
        $error_message = "Password must include at least one special character";
        error_log("Validation failed: No special character");
    } elseif ($password !== $confirm_password) {
        $error_message = "Passwords do not match";
        error_log("Validation failed: Passwords don't match");
    } else {
        // Check if email exists
        $check_email = $conn->prepare("SELECT email FROM users WHERE email = ?");
        $check_email->bind_param("s", $email);
        $check_email->execute();
        $check_email->store_result();
        
        if ($check_email->num_rows > 0) {
            $error_message = "Email already exists";
            error_log("Validation failed: Email exists");
        } else {
            // Check if the email is valid for the selected role
            if (stripos($email, 'admin') !== false && $role !== 'admin') {
                $error_message = "This email contains 'admin' - you must select the Admin role";
                error_log("Validation failed: Admin email with wrong role");
            } elseif (stripos($email, 'organizer') !== false && $role !== 'organizer') {
                $error_message = "This email contains 'organizer' - you must select the Organizer role";
                error_log("Validation failed: Organizer email with wrong role");
            } elseif (stripos($email, 'admin') === false && stripos($email, 'organizer') === false && $role !== 'student') {
                $error_message = "This email is only valid for student accounts";
                error_log("Validation failed: Student email with wrong role");
            } else {
                // If all validations pass, proceed with registration
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password, role) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssss", $first_name, $last_name, $email, $hashed_password, $role);

                if ($stmt->execute()) {
                    $success_message = "Registration successful! Please <a href='login.php'>login</a>";
                    error_log("Registration successful");
                } else {
                    $error_message = "Registration failed. Please try again.";
                    error_log("Registration failed: " . $stmt->error);
                }
                $stmt->close();
            }
        }
        $check_email->close();
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up | Campus Events</title>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            margin: 0;
            font-family: 'Lato', sans-serif;
            background: #ffffff;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .signup-container {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem;
        }

        .form-container {
            background: #ffffff;
            padding: 2.5rem;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
            border: 1px solid #e0e0e0;
        }

        h1 {
            color: #A53838;
            text-align: center;
            margin-bottom: 2rem;
            font-size: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
            font-weight: 500;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus {
            border-color: #A53838;
            outline: none;
            box-shadow: 0 0 0 3px rgba(165, 56, 56, 0.1);
        }

        .signup-btn {
            width: 100%;
            padding: 1rem;
            background: #A53838;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .signup-btn:hover {
            background: #8A2E2E;
        }

        .message {
            display: block !important;
            margin-bottom: 20px;
            padding: 15px;
            border-radius: 5px;
            font-weight: 500;
            text-align: center;
        }
        
        .error-message {
            background-color: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }
        
        .success-message {
            background-color: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
        }

        .success-message a {
            color: #A53838;
            text-decoration: none;
            font-weight: 600;
        }

        .login-link {
            text-align: center;
            margin-top: 1.5rem;
            color: #666;
        }

        .login-link a {
            color: #A53838;
            text-decoration: none;
            font-weight: 600;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .form-container {
                margin: 1rem;
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="signup-container">
        <div class="form-container">
            <h1>Sign Up</h1>
            <?php if (!empty($error_message)): ?>
                <div class="message error-message" style="display: block !important; background-color: #ffebee; color: #c62828; padding: 15px; margin-bottom: 20px; border-radius: 5px; border: 1px solid #ffcdd2; text-align: center;">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>
            <?php if (!empty($success_message)): ?>
                <div class="message success-message" style="display: block !important; background-color: #e8f5e9; color: #2e7d32; padding: 15px; margin-bottom: 20px; border-radius: 5px; border: 1px solid #c8e6c9; text-align: center;">
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            <form method="POST" action="" id="signupForm">
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" id="first_name" name="first_name" required>
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" id="last_name" name="last_name" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                    <small style="color: #666; display: block; margin-top: 0.5rem;">
                        Password must be at least 8 characters long and include uppercase, lowercase, number, and special character.
                    </small>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                <div class="form-group">
                    <label for="role">Role</label>
                    <select id="role" name="role" required>
                        <option value="" disabled selected>Select your role</option>
                        <option value="student">Student</option>
                        <option value="organizer">Organizer</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <button type="submit" class="signup-btn">Sign Up</button>
            </form>
            <div class="login-link">
                Already have an account? <a href="login.php">Login</a>
            </div>
        </div>
    </div>
    <script>
    document.getElementById('eventSearch').addEventListener('input', function() {
        const search = this.value.toLowerCase();
        document.querySelectorAll('#eventsTable tbody tr').forEach(function(row) {
            const title = row.querySelector('td:first-child').textContent.toLowerCase();
            row.style.display = title.includes(search) ? '' : 'none';
        });
    });
    document.getElementById('clearSearch').addEventListener('click', function() {
        document.getElementById('eventSearch').value = '';
        document.querySelectorAll('#eventsTable tbody tr').forEach(function(row) {
            row.style.display = '';
        });
    });
    document.getElementById('signupForm').addEventListener('submit', function(e) {
        // Clear any existing messages
        const errorMessages = document.querySelectorAll('.error-message');
        const successMessages = document.querySelectorAll('.success-message');
        errorMessages.forEach(msg => msg.remove());
        successMessages.forEach(msg => msg.remove());
    });
    </script>
</body>
</html>