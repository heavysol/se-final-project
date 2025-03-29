<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Registrations - Campus Events</title>
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
            <li><a href="#" class = 'active'><i class="bi bi-calendar-event"></i> Events</a></li>
            <li><a href="#"><i class="bi bi-journal-check"></i> My Registrations</a></li>
            <li><a href="#"><i class="bi bi-star"></i> Favorites</a></li>
            <li><a href="#"><i class="bi bi-people"></i> Clubs & Organizations</a></li>
            <li><a href="../../notifications.php"><i class="bi bi-bell"></i> Notifications</a></li>
            <li><a href="#"><i class="bi bi-gear"></i> Settings</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class = 'container-fluid'>
        <!-- My Registrations Table -->
            <div class="row mb-4">
                <div class="col-md-9">
                    <div class="dashboard-card">
                        <h4>
                            Clubs and Organizations
                        </h4>
                        <!-- Table of clubs and organisations will be output here from backend -->
                    </div>
                </div>   
        </div>
    </div>
</body>