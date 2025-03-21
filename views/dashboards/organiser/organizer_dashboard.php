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
    <li><a href="#" class="active"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
    <li><a href="#"><i class="bi bi-calendar-event"></i> My Events</a></li>
    <li><a href="#"><i class="bi bi-plus-circle"></i> Create Event</a></li>
    <li><a href="#"><i class="bi bi-people"></i> Attendees</a></li>
    <li><a href="#"><i class="bi bi-graph-up"></i> Analytics</a></li>
    <li><a href="#"><i class="bi bi-chat-dots"></i> Feedback</a></li>
    <li><a href="#"><i class="bi bi-bell"></i> Notifications</a></li>
    <li><a href="#"><i class="bi bi-gear"></i> Settings</a></li>
</ul>
</div>

<!-- Main Content -->
<div class="main-content">
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2>Organizer Dashboard</h2>
            <p class="text-muted">Welcome, ASC Event Coordinator!</p>
        </div>
        <div class="col-md-6 text-end">
            <button class="btn btn-primary"><i class="bi bi-plus"></i> Create New Event</button>
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

    <div class="row mb-4">
        <div class="col-md-8">
            <div class="dashboard-card">
                <h4>
                    Upcoming Events
                    <button class="btn btn-sm btn-outline-primary">View All</button>
                </h4>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Event Name</th>
                                <th>Date</th>
                                <th>Location</th>
                                <th>Registration</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Akwaaba Night</td>
                                <td>20 Mar 2025</td>
                                <td>Student Center</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1 me-2">
                                            <div class="progress">
                                                <div class="progress-bar bg-success" role="progressbar" style="width: 78%" aria-valuenow="78" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                        <span>156/200</span>
                                    </div>
                                </td>
                                <td><span class="event-status active">Active</span></td>
                                <td>
                                    <a href="#" class="event-action-btn"><i class="bi bi-eye"></i></a>
                                    <a href="#" class="event-action-btn"><i class="bi bi-pencil"></i></a>
                                    <a href="#" class="event-action-btn"><i class="bi bi-qr-code"></i></a>
                                </td>
                            </tr>
                            <tr>
                                <td>Global Café: Spanish Culture</td>
                                <td>21 Mar 2025</td>
                                <td>Hive</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1 me-2">
                                            <div class="progress">
                                                <div class="progress-bar bg-success" role="progressbar" style="width: 82%" aria-valuenow="82" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                        <span>98/120</span>
                                    </div>
                                </td>
                                <td><span class="event-status active">Active</span></td>
                                <td>
                                    <a href="#" class="event-action-btn"><i class="bi bi-eye"></i></a>
                                    <a href="#" class="event-action-btn"><i class="bi bi-pencil"></i></a>
                                    <a href="#" class="event-action-btn"><i class="bi bi-qr-code"></i></a>
                                </td>
                            </tr>
                            <tr>
                                <td>Entrepreneurship Workshop</td>
                                <td>31 Mar 2025</td>
                                <td>R5 Building</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1 me-2">
                                            <div class="progress">
                                                <div class="progress-bar bg-warning" role="progressbar" style="width: 45%" aria-valuenow="45" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                        <span>45/100</span>
                                    </div>
                                </td>
                                <td><span class="event-status pending">Pending</span></td>
                                <td>
                                    <a href="#" class="event-action-btn"><i class="bi bi-eye"></i></a>
                                    <a href="#" class="event-action-btn"><i class="bi bi-pencil"></i></a>
                                    <a href="#" class="event-action-btn"><i class="bi bi-qr-code"></i></a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-4">
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
    </div>

    <div class="row">
        <div class="col-md-6">
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
        <div class="col-md-6">
            <div class="dashboard-card">
                <h4>
                    Event Analytics
                    <div class="dropdown d-inline">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                            Last 30 Days
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <li><a class="dropdown-item" href="#">Last 7 Days</a></li>
                            <li><a class="dropdown-item" href="#">Last 30 Days</a></li>
                            <li><a class="dropdown-item" href="#">Last 90 Days</a></li>
                        </ul>
                    </div>
                </h4>
                <div class="chart-container">
                    <canvas id="eventAnalyticsChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-4">
            <div class="dashboard-card">
                <h4>Popular Event Categories</h4>
                <div class="chart-container">
                    <canvas id="categoriesChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-8">
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
</div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src = '../../../assets/js/organizer-script.js'></script>
</body>
</html>