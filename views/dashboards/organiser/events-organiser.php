<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Organizer Dashboard - Campus Events</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../../assets/css/general-styles.css">
    <link rel="stylesheet" href="../../../assets/css/dashboard-styles.css">
</head>
<body>
<!-- Sidebar -->
<div class="sidebar">
<div class="sidebar-header">
    <h3>Campus Events</h3>
    <div class="text-white-50 small">Organizer Dashboard</div>
</div>
<ul class="sidebar-menu">
    <li><a href="./organizer_dashboard.php" class="active"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
    <li><a href="./events-organiser.php"><i class="bi bi-calendar-event"></i> My Events</a></li>
    <li><a href="./create-events.php"><i class="bi bi-plus-circle"></i> Create Event</a></li>
    <!--PROBABLY NOT NEEDED: attendee info can be in Analytics <li><a href="#"><i class="bi bi-people"></i> Attendees</a></li>-->
    <li><a href="./analytics.php"><i class="bi bi-graph-up"></i> Analytics</a></li>
    <!--PROBABLY NOT NEEDED: feedback info can be in Analytics <li><a href="#"><i class="bi bi-chat-dots"></i> Feedback</a></li>-->
    <li><a href="../notifications.php"><i class="bi bi-bell"></i> Notifications</a></li>
    <li><a href="../settings.php"><i class="bi bi-gear"></i> Settings</a></li>
</ul>
</div>

<div class="main-content">
        <div class = 'container-fluid'>
        <!-- Favourites -->
            <div class="row mb-4">
                <div class="col-md-9">
                    <div class="dashboard-card">
                        <h4>
                            My Events
                        </h4>
                        <!-- Table of organiser events will be output here from backend -->
                    </div>
                </div>  
            </div> 
        </div>
    </div>
</html>