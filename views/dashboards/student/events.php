<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events - Campus Events</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../../assets/css/general-styles.css">
    <link rel="stylesheet" href="../../../assets/css/dashboard-styles.css">
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h3>Campus Events</h3>
            <div class="text-white-50 small">Student Dashboard</div>
        </div>
        <ul class="sidebar-menu">
        <li><a href="./student_dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
            <li><a href="./events.php" class = 'active'><i class="bi bi-calendar-event"></i> Events</a></li>
            <li><a href="./registrations.php"><i class="bi bi-journal-check"></i> My Registrations</a></li>
            <li><a href="./favourites.php"><i class="bi bi-star"></i> Favorites</a></li>
            <li><a href="./clubs-orgs.php"><i class="bi bi-people"></i> Clubs & Organizations</a></li>
            <li><a href="../../notifications.php"><i class="bi bi-bell"></i> Notifications</a></li>
            <li><a href="../../settings.php"><i class="bi bi-gear"></i> Settings</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class = 'container-fluid'>
        <!-- Search Bar -->
        <div class="row mb-4 search-area"> <!-- Trying to add search class here -->
            <h4> Search for events </h4>
            <input type = 'text' id = 'search' name = 'search' placeholder = 'Search for events here' class = 'search'> <div class = 'event-actions'><button class = 'btn btn-sm btn-primary'> Search </button></div>
        </div>

        <!-- Event list -->
        <div class="row mb-4">
                <div class="col-md-9">
                    <div class="dashboard-card">
                        <h4>
                            Upcoming Events
                            <span class="badge badge-custom"><!-- Current number of upcoming events this week output from backend --> This Week</span>
                        </h4>
                        <!-- HTML tags for event frontend interface; backend people can output these from backend -->
                        <div class="event-item">
                            <div class="event-title"><!-- Title of event, from backend --></div>
                            <div class="event-details">
                                <i class="bi bi-calendar"></i> <!-- Date and time of event, from backend -->
                                <br>
                                <i class="bi bi-geo-alt"></i> <!-- Venue of event, from backend -->
                            </div>
                            <div class="event-actions">
                                <button class="btn btn-sm btn-primary">Register</button>
                                <button class="btn btn-sm btn-outline-primary">Add to Calendar</button>
                            </div>
                        </div>
                        
        </div>
    </div>
</body>