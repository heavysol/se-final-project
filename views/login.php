<?php
require_once '../includes/session_handler.php';
require '../db/config.php';

$error_message = '';
$success_message = '';

// Check for timeout message
if (isset($_GET['timeout'])) {
    $error_message = "Your session has expired. Please login again.";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $error_message = "Please fill in all fields";
    } else {
        // Modify the query to fetch first_name and last_name
        $stmt = $conn->prepare("SELECT user_id, first_name, last_name, role, email, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            $error_message = "Invalid email or password";
        } else {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                // Combine first_name and last_name to create full_name
                $full_name = $user['first_name'] . ' ' . $user['last_name'];

                // Store session data
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['full_name'] = $full_name;
                $_SESSION['role'] = $user['role'];
                $_SESSION['last_activity'] = time();

                // Set flash message for successful login
                setFlashMessage('success', 'Welcome back, ' . $full_name . '!');

                // Redirect based on the role from the database
                switch ($user['role']) {
                    case 'admin':
                        header("Location: ./dashboards/admin/admin_dashboard.php");
                        break;
                    case 'organizer':
                        header("Location: ./dashboards/organizer/organizer_dashboard.php");
                        break;
                    case 'student':
                        header("Location: ./dashboards/student/student_dashboard.php");
                        break;
                    default:
                        $error_message = "Invalid role assigned.";
                        break;
                }
                exit();
            } else {
                $error_message = "Invalid email or password";
            }
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Campus Events</title>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/general-styles.css">
    <link rel="stylesheet" href="../assets/css/logsign-styles.css">
    <style>
        body {
            margin: 0;
            font-family: 'Lato', sans-serif;
            background: #ffffff;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .login-container {
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
            max-width: 400px;
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

        .form-group input {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            border-color: #A53838;
            outline: none;
            box-shadow: 0 0 0 3px rgba(165, 56, 56, 0.1);
        }

        .login-btn {
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

        .login-btn:hover {
            background: #8A2E2E;
        }

        .error-message {
            background: #fee;
            color: #c00;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            text-align: center;
        }

        .signup-link {
            text-align: center;
            margin-top: 1.5rem;
            color: #666;
        }

        .signup-link a {
            color: #A53838;
            text-decoration: none;
            font-weight: 600;
        }

        .signup-link a:hover {
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
    <div class="login-container">
        <div class="form-container">
            <h1>Login</h1>
            <?php displayFlashMessage(); ?>
            <?php if (!empty($error_message)): ?>
                <div class="error-message"><?php echo $error_message; ?></div>
            <?php endif; ?>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="login-btn">Login</button>
            </form>
            <div class="signup-link">
                Don't have an account? <a href="Signup.php">Sign up</a>
            </div>
        </div>
    </div>
</body>
</html>