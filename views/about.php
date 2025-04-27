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
            color: #A53838;
            background-color: transparent;
        }

        .nav-links li a.active {
            border-bottom: 2px solid #A53838;
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
         /* Hero Section */
        .hero {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: var(--text-light);
            padding: 6rem 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('assets/images/pattern.png') repeat;
            opacity: 0.1;
            z-index: 1;
        }

        .hero .container {
            position: relative;
            z-index: 2;
        }

        .hero h2 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            color: var(--text-light);
            text-shadow: 2px 2px 4px var(--shadow-color);
        }

        .hero p {
            font-size: 1.25rem;
            max-width: 800px;
            margin: 0 auto 2rem;
            color: rgba(255, 255, 255, 0.9);
        }

        .hero .cta-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
        }

        .hero .btn-primary {
            background-color: var(--accent-color);
            color: var(--text-light);
            padding: 0.75rem 2rem;
            font-size: 1.1rem;
            border-radius: 50px;
            box-shadow: 0 4px 6px var(--shadow-color);
        }

        .hero .btn-primary:hover {
            background-color: var(--hover-color);
            transform: translateY(-2px);
            box-shadow: 0 6px 8px var(--shadow-color);
        }

        .hero .btn-outline {
            background-color: transparent;
            color: var(--text-light);
            border: 2px solid var(--text-light);
            padding: 0.75rem 2rem;
            font-size: 1.1rem;
            border-radius: 50px;
        }

        .hero .btn-outline:hover {
            background-color: var(--text-light);
            color: var(--primary-color);
            transform: translateY(-2px);
        }


        .btn-outline {
            border: 1.5px solid #A53838;
            color: #A53838;
            background-color: transparent;
        }

        .btn-primary {
            background-color: #A53838;
            color: white;
            border: none;
        }

        .btn-outline:hover {
            background-color: rgba(191, 173, 173, 0.1);
            color: #A53838;
        }

        .btn-primary:hover {
            background-color: #8c2d2d;
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

        .about-hero {
            background-color: #C14444;
            color: white;
            text-align: center;
            padding: 8rem 0;
            margin-bottom: 4rem;
            position: relative;
            overflow: hidden;
        }

        .about-hero::before {
            display: none;
        }

        .about-hero .container {
            position: relative;
            z-index: 2;
        }

        .mission-vision {
            background-color: #f8f9fa;
            padding: 6rem 0;
        }

        .mission, .vision {
            background: white;
            padding: 3rem;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(242, 229, 229, 0.1);
            transition: all 0.3s ease;
        }

        .mission:hover, .vision:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
        }

        .mission h2, .vision h2 {
            color: #A53838;
            margin-bottom: 1.5rem;
        }

        .features {
            padding: 6rem 0;
            background: white;
        }

        .features h2 {
            text-align: center;
            margin-bottom: 3rem;
            color: #A53838;
        }

        .feature-card {
            background: white;
            padding: 2.5rem;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: all 0.3s ease;
            border: 1px solid rgba(0, 0, 0, 0.1);
        }

        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
            border-color: #A53838;
        }

        .feature-icon {
            font-size: 3rem;
            margin-bottom: 1.5rem;
            color: #A53838;
        }

        .feature-card h3 {
            color: #A53838;
            margin-bottom: 1rem;
        }

        footer {
            background-color: #A53838;
            color: white;
            padding: 2rem 0;
        }

        .footer-section h3 {
            color: white;
            border-bottom: 2px solid white;
            padding-bottom: 0.5rem;
            margin-bottom: 1rem;
            display: inline-block;
        }

        .footer-links a {
            color: white;
            text-decoration: none;
        }

        .footer-links a:hover {
            color: rgba(255, 255, 255, 0.8);
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
                        <a href="Signup.php" class="btn btn-primary">Sign Up</a>
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
                        <li><a href="<?php echo getDashboardUrl(); ?>">Dashboard</a></li>
                        <li><a href="#">Calendar</a></li>
                        <li><a href="Signup.php">Sign Up</a></li>
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