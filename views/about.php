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
    <link rel="stylesheet" href="../assets/css/about-styles.css">
</head>
<body>
    <header>
        <div class="container">
            <nav>
                <div class="logo">
                    <img src="../Ashesi logo.jpg" alt="Ashesi Events Logo">
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

        <section class="team">
            <div class="container">
                <h2>Our Team</h2>
                <p class="team-description">A dedicated group of students and staff working together to enhance campus life at Ashesi University.</p>
                <div class="team-grid">
                    <div class="team-member">
                        <div class="member-image">
                            <img src="../assets/images/team-placeholder.jpg" alt="Team Member">
                        </div>
                        <h3>Student Team</h3>
                        <p>Event Management</p>
                    </div>
                    <div class="team-member">
                        <div class="member-image">
                            <img src="../assets/images/team-placeholder.jpg" alt="Team Member">
                        </div>
                        <h3>Staff Support</h3>
                        <p>Administration</p>
                    </div>
                    <div class="team-member">
                        <div class="member-image">
                            <img src="../assets/images/team-placeholder.jpg" alt="Team Member">
                        </div>
                        <h3>Technical Team</h3>
                        <p>Platform Development</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="contact">
            <div class="container">
                <h2>Get in Touch</h2>
                <p>Have questions or suggestions? We'd love to hear from you!</p>
                <div class="contact-info">
                    <div class="contact-item">
                        <span class="icon">📧</span>
                        <p>events@ashesi.edu.gh</p>
                    </div>
                    <div class="contact-item">
                        <span class="icon">📍</span>
                        <p>Ashesi University, Berekuso</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php 
    require_once('../includes/config.php');
    include('../includes/footer.php'); 
    ?>
</body>
</html> 