<?php
session_start();
require_once 'db/config.php';

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

// Function to get the appropriate event registration URL
function getEventRegistrationUrl($eventId) {
    if (!isLoggedIn()) {
        return 'views/login.php';
    }
    
    switch ($_SESSION['role']) {
        case 'student':
            return 'views/dashboards/student/student_dashboard.php?event_id=' . $eventId;
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

    <title>Ashesi Campus Events - Discover, Register, Engage</title>
    <link rel="stylesheet" href="./assets/css/general-styles.css">
    <link rel="stylesheet" href="./assets/css/homepage-styles.css">

</head>

<body>

    <header>
        <div class="container">
            <nav>
                <div class="logo">
                    <img src="assets/images/Ashesi_University_Logo (1).webp" alt="Ashesi Events Logo">
                    <h1>Ashesi Campus Events</h1>
                </div>
                
                <ul class="nav-links">
                    <li><a href="./index.php">Home</a></li>
                    <li><a href="<?php echo getDashboardUrl(); ?>">Events</a></li>
                    <li><a href="<?php echo getDashboardUrl(); ?>">Dashboard</a></li>
                    <li><a href="views/about.php">About</a></li>
                </ul>
                
                <div class="auth-buttons">
                    <?php if (isLoggedIn()): ?>
                        <a href="views/logout.php" class="btn btn-outline">Log Out</a>
                    <?php else: ?>
                        <a href="views/login.php" class="btn btn-outline">Log In</a>
                        <a href="views/Signup.php" class="btn btn-primary">Sign Up</a>
                    <?php endif; ?>
                </div>
            </nav>
        </div>
    </header>

    

    <section class="hero">
        <div class="container">
            <h2>Discover, Register, Engage</h2>
            <p>Your one-stop platform for all Ashesi University campus events. Never miss an opportunity to connect, learn, and grow.</p>
        </div>
    </section>

    

    <section class="featured-events">

        <div class="container">

            <h2 class="section-title">Upcoming Events</h2>

            

            <div class="event-grid">

                <?php

                // Get one event from each category
                $categories = ['academic', 'cultural', 'sports', 'social'];
                $events = [];
                
                foreach ($categories as $category) {
                    $query = "SELECT * FROM events 
                             WHERE start_datetime >= NOW() 
                             AND LOWER(category) = ?
                             ORDER BY start_datetime ASC 
                             LIMIT 1";
                    
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("s", $category);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($result && $result->num_rows > 0) {
                        $events[] = $result->fetch_assoc();
                    }
                }
                
                // If we don't have enough events from different categories, get more upcoming events
                if (count($events) < 4) {
                    $remaining = 4 - count($events);
                    $query = "SELECT * FROM events 
                             WHERE start_datetime >= NOW() 
                             AND event_id NOT IN (" . implode(',', array_map(function($e) { return $e['event_id']; }, $events)) . ")
                             ORDER BY start_datetime ASC 
                             LIMIT " . $remaining;
                    
                    $result = $conn->query($query);
                    if ($result) {
                        while ($row = $result->fetch_assoc()) {
                            $events[] = $row;
                        }
                    }
                }
                
                if (!empty($events)) {
                    foreach ($events as $event) {
                        // Determine image based on category
                        $category = strtolower($event['category']);
                        $imagePath = 'assets/images/';
                        
                        switch ($category) {
                            case 'academic':
                                $imagePath .= 'accademic.jpg';
                                break;
                            case 'cultural':
                                $imagePath .= 'cultural.jpg';
                                break;
                            case 'sports':
                                $imagePath .= 'sport.jpg';
                                break;
                            case 'social':
                                $imagePath .= 'Social.jpg';
                                break;
                            default:
                                $imagePath .= 'student-party.jpg';
                        }
                        
                        // Format date and time
                        $date = date('F j, Y', strtotime($event['start_datetime']));
                        $time = date('g:i A', strtotime($event['start_datetime']));
                        
                        echo '<div class="event-card">
                                <div class="event-img">
                                    <img src="' . $imagePath . '" alt="' . htmlspecialchars($event['title']) . '">
                                </div>
                                <div class="event-details">
                                    <h3>' . htmlspecialchars($event['title']) . '</h3>
                                    <div class="event-meta">
                                        <span>' . $date . '</span>
                                        <span>' . $time . '</span>
                                    </div>
                                    <p>' . htmlspecialchars($event['description']) . '</p>
                                    <a href="' . getEventRegistrationUrl($event['event_id']) . '" class="register-btn">Register Now</a>
                                </div>
                            </div>';
                    }
                } else {
                    echo '<p class="no-events">No upcoming events found.</p>';
                }
                ?>

            </div>

            

            <div style="text-align: center; margin-top: 2rem;">

                <a href="<?php echo getDashboardUrl(); ?>" style="color: var(--primary); font-weight: 500; text-decoration: none;">View All Events →</a>

            </div>

        </div>

    </section>

    

    <section class="features">

        <div class="container">

            <h2 class="section-title">Platform Features</h2>

            

            <div class="features-grid">

                <div class="feature-card">

                    <div class="feature-icon">📅</div>

                    <h3>Event Discovery</h3>

                    <p>Find all campus events in one centralized location with easy search and filtering.</p>

                </div>

                

                <div class="feature-card">

                    <div class="feature-icon">📱</div>

                    <h3>Easy Registration</h3>

                    <p>Register for events with a single click and receive confirmation instantly.</p>

                </div>

                

                <div class="feature-card">

                    <div class="feature-icon">🔔</div>

                    <h3>Event Reminders</h3>

                    <p>Get timely reminders via email or SMS so you never miss an event.</p>

                </div>

                

                <div class="feature-card">

                    <div class="feature-icon">📊</div>

                    <h3>QR Check-ins</h3>

                    <p>Quick and easy attendance tracking with QR code scanning.</p>

                </div>

            </div>

        </div>

    </section>

    

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

                        <li><a href="#">Home</a></li>

                        <li><a href="#">Events</a></li>

                        <li><a href="#">Calendar</a></li>

                        <li><a href="#">Sign Up</a></li>

                        <li><a href="#">Log In</a></li>

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

    <style>
        .error-message {
            background-color: #ffebee;
            color: #c62828;
            padding: 15px;
            margin: 10px 0;
            border-radius: 4px;
            border-left: 4px solid #c62828;
        }
        .debug-info {
            background-color: #e3f2fd;
            color: #1565c0;
            padding: 15px;
            margin: 10px 0;
            border-radius: 4px;
            border-left: 4px solid #1565c0;
        }
        .no-events {
            background-color: #fff3e0;
            color: #ef6c00;
            padding: 15px;
            margin: 10px 0;
            border-radius: 4px;
            border-left: 4px solid #ef6c00;
        }
        .no-events ul {
            margin: 10px 0;
            padding-left: 20px;
        }
    </style>

</body>

</html>