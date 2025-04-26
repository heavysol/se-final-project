<?php
session_start();
require '../db/config.php';

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $role = trim($_POST['role']);

    // Basic validation
    if (empty($first_name) || empty($last_name) || empty($email) || empty($password) || empty($confirm_password) || empty($role)) {
        $error_message = "All fields are required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format";
    } elseif (strlen($password) < 8) {
        $error_message = "Password must be at least 8 characters long";
    } elseif ($password !== $confirm_password) {
        $error_message = "Passwords do not match";
    } else {
        // Check if email exists
        $check_email = $conn->prepare("SELECT email FROM users WHERE email = ?");
        $check_email->bind_param("s", $email);
        $check_email->execute();
        $check_email->store_result();
        
        if ($check_email->num_rows > 0) {
            $error_message = "Email already exists";
        } else {
            // Check if the email is valid for the selected role
            if (stripos($email, 'admin') !== false && $role !== 'admin') {
                $error_message = "Invalid role selection for this admin email type";
                header("Location: Signup.php?error=" . urlencode($error_message));  // Redirect with error message
                exit();
            } elseif (stripos($email, 'organizer') !== false && $role !== 'organizer') {
                $error_message = "Invalid role selection for this organizer email type";
                header("Location: Signup.php?error=" . urlencode($error_message));  // Redirect with error message
                exit();
            } elseif (stripos($email, 'admin') === false && stripos($email, 'organizer') === false && $role !== 'student') {
                $error_message = "This email is only valid for student accounts";
                header("Location: Signup.php?error=" . urlencode($error_message));  // Redirect with error message
                exit();
            }

            if (empty($error_message)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Correcting the insert statement
                $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, password, role) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssss", $first_name, $last_name, $email, $hashed_password, $role);

                if ($stmt->execute()) {
                    $success_message = "Registration successful! Please <a href='login.php'>login</a>";
                } else {
                    $error_message = "Registration failed. Please try again.";
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
            background: linear-gradient(135deg, #1a1a1a 0%, #333333 100%);
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
            background: rgba(255, 255, 255, 0.95);
            padding: 2.5rem;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
            backdrop-filter: blur(10px);
        }

        h1 {
            color: #BC1E4A;
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
            border-color: #BC1E4A;
            outline: none;
            box-shadow: 0 0 0 3px rgba(188, 30, 74, 0.1);
        }

        .signup-btn {
            width: 100%;
            padding: 1rem;
            background: #BC1E4A;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .signup-btn:hover {
            background: #8A1435;
        }

        .message {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            text-align: center;
        }

        .error-message {
            background: #fee;
            color: #c00;
        }

        .success-message {
            background: #efe;
            color: #070;
        }

        .success-message a {
            color: #BC1E4A;
            text-decoration: none;
            font-weight: 600;
        }

        .login-link {
            text-align: center;
            margin-top: 1.5rem;
            color: #666;
        }

        .login-link a {
            color: #BC1E4A;
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
    <div class="container" style="background-color: var(--secondary-color);">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card" style="border: none; box-shadow: 0 2px 4px var(--shadow-color);">
                    <div class="card-header" style="background-color: var(--primary-color); color: var(--text-light);">
                        <h3 class="text-center">Sign Up</h3>
                    </div>
                    <div class="card-body">
                        <form id="signupForm" method="POST" action="Signup.php">
                            <div class="form-group">
                                <label for="first_name" style="color: var(--text-dark);">First Name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" required 
                                       style="border-color: var(--border-color);">
                            </div>
                            <div class="form-group">
                                <label for="last_name" style="color: var(--text-dark);">Last Name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" required 
                                       style="border-color: var(--border-color);">
                            </div>
                            <div class="form-group">
                                <label for="email" style="color: var(--text-dark);">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required 
                                       style="border-color: var(--border-color);">
                            </div>
                            <div class="form-group">
                                <label for="password" style="color: var(--text-dark);">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required 
                                       style="border-color: var(--border-color);">
                            </div>
                            <div class="form-group">
                                <label for="confirm_password" style="color: var(--text-dark);">Confirm Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required 
                                       style="border-color: var(--border-color);">
                            </div>
                            <div class="form-group">
                                <label for="role" style="color: var(--text-dark);">Role</label>
                                <select class="form-control" id="role" name="role" required>
                                    <option value="" disabled selected>Sign up as:</option>
                                    <option value="student">Student</option>
                                    <option value="organizer">Organizer</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block" 
                                    style="background-color: var(--primary-color); border-color: var(--primary-color); color: var(--text-light);">
                                Sign Up
                            </button>
                        </form>
                        <div class="text-center mt-3">
                            <p style="color: var(--text-secondary);">Already have an account? 
                                <a href="login.php" style="color: var(--primary-color);">Login</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>