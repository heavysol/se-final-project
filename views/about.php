<?php
session_start();
require_once '../db/config.php';

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['role']);
}

// Function to get the appropriate dashboard URL based on user role
function getDashboardUrl() {
    if (!isLoggedIn()) {
        return 'views/login.php';
    }
    
    switch ($_SESSION['role']) {
        case 'admin':
            return 'views/dashboards/admin/admin_dashboard.php';
        case 'organizer':
            return 'views/dashboards/organiser/organizer_dashboard.php';
        case 'student':
            return 'views/dashboards/student/student_dashboard.php';
        default:
            return 'views/login.php';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About - Ashesi Campus Events</title>
    <link rel="stylesheet" href="../assets/css/general-styles.css">
    <link rel="stylesheet" href="../assets/css/homepage-styles.css">
    <link rel="stylesheet" href="../assets/css/about-styles.css">
    <style>
        header {
            background-color: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            height: 80px;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo img {
            height: 45px;
            width: auto;
            filter: none;
        }

        .logo h1 {
            font-size: 1.5rem;
            color: #333;
            font-weight: 600;
        }

        .nav-links li a {
            color: #333;
            text-decoration: none;
            padding: 0.5rem 1rem;
            transition: all 0.3s ease;
            font-weight: 500;
            font-size: 1.1rem;
        }

        .nav-links li a:hover,
        .nav-links li a.active {
            color: #28a745;
            background-color: transparent;
        }

        .nav-links li a.active {
            border-bottom: 2px solid #28a745;
            border-radius: 0;
        }

        .auth-buttons {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 8px 20px;
            border-radius: 4px;
            font-weight: 500;
            font-size: 1rem;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .btn-outline {
            border: 1.5px solid #28a745;
            color: #28a745;
            background-color: transparent;
        }

        .btn-primary {
            background-color: #28a745;
            color: white;
            border: none;
        }

        .btn-outline:hover {
            background-color: rgba(40, 167, 69, 0.1);
            color: #28a745;
        }

        .btn-primary:hover {
            background-color: #218838;
        }

        /* Add padding to body to account for fixed header */
        body {
            padding-top: 80px;
        }

        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 100%;
            padding: 0 20px;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <nav>
                <div class="logo">
                    <img src="../assets/images/Ashesi_University_Logo (1).webp" alt="Ashesi Events Logo">
                    <h1>Ashesi Campus Events</h1>
                </div>
                
                <ul class="nav-links">
                    <li><a href="../index.php">Home</a></li>
                    <li><a href="<?php echo getDashboardUrl(); ?>">Events</a></li>
                    <li><a href="about.php" class="active">About</a></li>
                </ul>
                
                <div class="auth-buttons">
                    <?php if (isLoggedIn()): ?>
                        <a href="logout.php" class="btn btn-outline">Log Out</a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-outline">Log In</a>
                        <a href="signup.php" class="btn btn-primary">Sign Up</a>
                    <?php endif; ?>
                </div>
            </nav>
        </div>
    </header>

    <main>
        <section class="about-hero">
            <div class="container">
                <h1>About Ashesi Campus Events</h1>
                <p>Connecting the Ashesi community through meaningful events and experiences</p>
            </div>
        </section>

        <section class="mission-vision">
            <div class="container">
                <div class="mission">
                    <h2>Our Mission</h2>
                    <p>To create a vibrant campus community by providing a centralized platform for event discovery, registration, and management, fostering student engagement and campus life at Ashesi University.</p>
                </div>
                <div class="vision">
                    <h2>Our Vision</h2>
                    <p>To be the leading platform that enhances student life and community engagement at Ashesi University through seamless event management and participation.</p>
                </div>
            </div>
        </section>

        <section class="features">
            <div class="container">
                <h2>What We Offer</h2>
                <div class="features-grid">
                    <div class="feature-card">
                        <div class="feature-icon">🎯</div>
                        <h3>Event Discovery</h3>
                        <p>Find all campus events in one place, from academic seminars to social gatherings.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">📝</div>
                        <h3>Easy Registration</h3>
                        <p>Simple and quick registration process for all events with instant confirmation.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">📱</div>
                        <h3>Mobile Access</h3>
                        <p>Access events and manage your participation from any device.</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">📊</div>
                        <h3>Event Analytics</h3>
                        <p>Track attendance and engagement metrics for better event planning.</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>About</h3>
                    <p>Ashesi Campus Events is a platform designed to enhance student engagement and simplify event management for the Ashesi University community.</p>
                </div>
                
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <ul class="footer-links">
                        <li><a href="../index.php">Home</a></li>
                        <li><a href="<?php echo getDashboardUrl(); ?>">Events</a></li>
                        <li><a href="#">Calendar</a></li>
                        <li><a href="signup.php">Sign Up</a></li>
                        <li><a href="login.php">Log In</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>Resources</h3>
                    <ul class="footer-links">
                        <li><a href="#">FAQ</a></li>
                        <li><a href="#">Contact Support</a></li>
                        <li><a href="#">Feedback</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Terms of Use</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>Connect</h3>
                    <p>Office of Student & Community Affairs (OSCA)<br>
                    Ashesi University<br>
                    1 University Avenue<br>
                    Berekuso, Eastern Region</p>
                </div>
            </div>
            
            <div class="copyright">
                <p>&copy; 2025 Ashesi Campus Events. A Group 19 Project.</p>
            </div>
        </div>
    </footer>
</body>
</html> 