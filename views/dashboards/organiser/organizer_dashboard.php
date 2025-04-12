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
<!-- Updated Sidebar HTML -->
<div class="sidebar">
    <div class="logo-container">
        <h3>FitInspire Hub</h3>
    </div>

    <ul class="nav-list">
        <li>
            <a href="organizer_dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'organizer_dashboard.php' ? 'active' : ''; ?>">
                <i class="fas fa-home"></i>
                <span class="link-name">Dashboard</span>
            </a>
        </li>
        <li>
            <a href="events-organiser.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'events-organiser.php' ? 'active' : ''; ?>">
                <i class="fas fa-calendar"></i>
                <span class="link-name">My Events</span>
            </a>
        </li>
        <li>
            <a href="analytics.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'analytics.php' ? 'active' : ''; ?>">
                <i class="fas fa-chart-bar"></i>
                <span class="link-name">Analytics</span>
            </a>
        </li>
        
        <!-- Logout with divider -->
        <li class="logout-divider">
            <a href="../../logout.php">
                <i class="fas fa-sign-out-alt"></i>
                <span class="link-name">Logout</span>
            </a>
        </li>
    </ul>
</div>

<!-- Main Content -->
<div class="main-content">
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2>Organizer Dashboard</h2>
            <p class="text-muted">Welcome, <?php echo isset($_SESSION['first_name']) ? $_SESSION['first_name'] : 'Organizer'; ?>!</p>
        </div>
        <div class="col-md-6 text-end">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createEventModal"><i class="bi bi-plus"></i> Create New Event</button>
        </div>
    </div>

    <!-- Welcome message section -->
   

    <!-- Event Creation Modal -->
    <div class="modal fade" id="createEventModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create New Event</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="eventCreationForm" method="post" action="event_management_action.php">
                        <div class="mb-3">
                            <label for="title" class="form-label">Event Title</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="start_datetime" class="form-label">Date Event is Posted</label>
                            <input type="datetime-local" class="form-control" id="start_datetime" name="start_datetime" required>
                        </div>
                        <div class="mb-3">
                            <label for="end_datetime" class="form-label">Date Event is Happening</label>
                            <input type="datetime-local" class="form-control" id="end_datetime" name="end_datetime" required>
                        </div>
                        <div class="mb-3">
                            <label for="location" class="form-label">Location</label>
                            <input type="text" class="form-control" id="location" name="location" required>
                        </div>
                        <div class="mb-3">
                            <label for="category" class="form-label">Category</label>
                            <input type="text" class="form-control" id="category" name="category" required>
                        </div>
                        <div class="mb-3">
                            <label for="max_capacity" class="form-label">Max Capacity</label>
                            <input type="number" class="form-control" id="max_capacity" name="max_capacity" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-icon primary">
                    <i class="bi bi-calendar2-check"></i>
                </div>
                <div class="stats-info">
                    <h3 class="stats-number">12</h3>
                    <p class="stats-label">Active Events</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-icon success">
                    <i class="bi bi-person-check"></i>
                </div>
                <div class="stats-info">
                    <h3 class="stats-number">347</h3>
                    <p class="stats-label">Total Attendees</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-icon warning">
                    <i class="bi bi-clock"></i>
                </div>
                <div class="stats-info">
                    <h3 class="stats-number">4</h3>
                    <p class="stats-label">Pending Events</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stats-icon danger">
                    <i class="bi bi-star"></i>
                </div>
                <div class="stats-info">
                    <h3 class="stats-number">4.7</h3>
                    <p class="stats-label">Average Rating</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Second row with Tasks and Notifications side by side -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="dashboard-card">
                <h4>
                    Checklist
                    <button class="btn btn-sm btn-outline-primary">Add Task</button>
                </h4>
                <div class="task-list">
                    <div class="task-item">
                        <div class="task-checkbox">
                            <input type="checkbox" class="form-check-input" id="task1" checked>
                        </div>
                        <div class="task-title">Submit event proposal for Cultural Night</div>
                        <div class="task-date">Yesterday</div>
                    </div>
                    <div class="task-item">
                        <div class="task-checkbox">
                            <input type="checkbox" class="form-check-input" id="task2" checked>
                        </div>
                        <div class="task-title">Book venue for Akwaaba Night</div>
                        <div class="task-date">Mar 10</div>
                    </div>
                    <div class="task-item">
                        <div class="task-checkbox">
                            <input type="checkbox" class="form-check-input" id="task3">
                        </div>
                        <div class="task-title">Coordinate with speakers for Career Fair</div>
                        <div class="task-date">Today</div>
                    </div>
                    <div class="task-item">
                        <div class="task-checkbox">
                            <input type="checkbox" class="form-check-input" id="task4">
                        </div>
                        <div class="task-title">Send reminders for Global Café attendees</div>
                        <div class="task-date">Tomorrow</div>
                    </div>
                    <div class="task-item">
                        <div class="task-checkbox">
                            <input type="checkbox" class="form-check-input" id="task5">
                        </div>
                        <div class="task-title">Prepare feedback forms for Akwaaba Night</div>
                        <div class="task-date">Mar 19</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="dashboard-card">
                <h4>
                    Recent Notifications
                    <span class="badge badge-custom">3 New</span>
                </h4>
                <div class="list-group">
                    <a href="#" class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">New registration for Akwaaba Night</h6>
                            <small>3 mins ago</small>
                        </div>
                        <p class="mb-1">15 new attendees registered for the upcoming event.</p>
                    </a>
                    <a href="#" class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">Student Center venue confirmed</h6>
                            <small>2 hours ago</small>
                        </div>
                        <p class="mb-1">Your venue request for Akwaaba Night has been approved.</p>
                    </a>
                    <a href="#" class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">Feedback summary available</h6>
                            <small>1 day ago</small>
                        </div>
                        <p class="mb-1">Feedback from Cultural Day is now available for review.</p>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Third row with full-width Recent Attendees -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="dashboard-card">
                <h4>
                    Recent Attendees
                    <button class="btn btn-sm btn-outline-primary">View All</button>
                </h4>
                <div class="attendee-list">
                    <div class="attendee-item">
                        <div class="attendee-avatar">
                            <i class="bi bi-person"></i>
                        </div>
                        <div class="attendee-info">
                            <h6 class="attendee-name">Amina Osei</h6>
                            <p class="attendee-email">aosei@ashesi.edu.gh</p>
                        </div>
                        <div class="attendee-event">
                            <span class="badge bg-light text-dark">Akwaaba Night</span>
                        </div>
                    </div>
                    <div class="attendee-item">
                        <div class="attendee-avatar">
                            <i class="bi bi-person"></i>
                        </div>
                        <div class="attendee-info">
                            <h6 class="attendee-name">David Mensah</h6>
                            <p class="attendee-email">dmensah@ashesi.edu.gh</p>
                        </div>
                        <div class="attendee-event">
                            <span class="badge bg-light text-dark">Entrepreneurship Workshop</span>
                        </div>
                    </div>
                    <div class="attendee-item">
                        <div class="attendee-avatar">
                            <i class="bi bi-person"></i>
                        </div>
                        <div class="attendee-info">
                            <h6 class="attendee-name">Fatima Bello</h6>
                            <p class="attendee-email">fbello@ashesi.edu.gh</p>
                        </div>
                        <div class="attendee-event">
                            <span class="badge bg-light text-dark">Global Café</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('eventCreationForm').addEventListener('submit', function(event) {
    event.preventDefault(); // Prevent the default form submission

    const formData = new FormData(this);

    fetch('../../../actions/create_event_action.php', { // Ensure this path is correct
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        const feedbackMessage = document.getElementById('feedbackMessage');
        if (data.success) {
            feedbackMessage.innerHTML = '<div class="alert alert-success">Event created successfully!</div>';
        } else {
            feedbackMessage.innerHTML = '<div class="alert alert-danger">An error occurred: ' + data.message + '</div>';
        }
    })
    .catch(error => {
        document.getElementById('feedbackMessage').innerHTML = '<div class="alert alert-danger">An error occurred: ' + error.message + '</div>';
    });
});
</script>
</body>
</html>