<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Organizer Dashboard - Campus Events</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --sidebar-width: 250px;
            --primary-color: #0B5394;
            --secondary-color: #F3F6F9;
            --accent-color: #FFD700;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
        }
        
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            background-color: var(--primary-color);
            color: white;
            padding: 20px 0;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }
        
        .sidebar-header {
            padding: 0 20px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
        }
        
        .sidebar-header h3 {
            margin: 0;
            color: white;
            font-size: 1.2rem;
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 0;
        }
        
        .sidebar-menu li {
            margin-bottom: 5px;
        }
        
        .sidebar-menu a {
            display: block;
            padding: 12px 20px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .sidebar-menu a:hover, .sidebar-menu a.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
            border-left: 4px solid var(--accent-color);
        }
        
        .sidebar-menu a i {
            margin-right: 10px;
        }
        
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 20px;
        }
        
        .dashboard-card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            padding: 20px;
            margin-bottom: 20px;
            height: 100%;
        }
        
        .dashboard-card h4 {
            margin-top: 0;
            color: var(--primary-color);
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .badge-custom {
            background-color: var(--accent-color);
            color: var(--primary-color);
            font-weight: 600;
        }
        
        .stats-card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            padding: 20px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        
        .stats-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            flex-shrink: 0;
        }
        
        .stats-icon.primary {
            background-color: rgba(11, 83, 148, 0.1);
            color: var(--primary-color);
        }
        
        .stats-icon.success {
            background-color: rgba(40, 167, 69, 0.1);
            color: var(--success-color);
        }
        
        .stats-icon.warning {
            background-color: rgba(255, 193, 7, 0.1);
            color: var(--warning-color);
        }
        
        .stats-icon.danger {
            background-color: rgba(220, 53, 69, 0.1);
            color: var(--danger-color);
        }
        
        .stats-info {
            flex-grow: 1;
        }
        
        .stats-number {
            font-size: 1.8rem;
            font-weight: 600;
            margin: 0;
            line-height: 1.2;
        }
        
        .stats-label {
            color: #777;
            margin: 0;
            font-size: 0.9rem;
        }


.table-responsive {
    overflow-x: auto;
}

.event-action-btn {
    width: 2rem;
    height: 2rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background-color: var(--secondary-color);
    color: var(--primary-color);
    margin-right: 5px;
    cursor: pointer;
    text-decoration: none;
}

.event-action-btn:hover {
    background-color: var(--primary-color);
    color: white;
}

.event-status {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 30px;
    font-size: 0.8rem;
    font-weight: 500;
}

.event-status.active {
    background-color: rgba(40, 167, 69, 0.1);
    color: var(--success-color);
}

.event-status.pending {
    background-color: rgba(255, 193, 7, 0.1);
    color: var(--warning-color);
}

.event-status.cancelled {
    background-color: rgba(220, 53, 69, 0.1);
    color: var(--danger-color);
}

.progress {
    height: 10px;
    border-radius: 5px;
}

.progress-sm {
    height: 5px;
}

.task-item {
    display: flex;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid #f0f0f0;
}

.task-item:last-child {
    border-bottom: none;
}

.task-checkbox {
    margin-right: 15px;
}

.task-title {
    flex-grow: 1;
}

.task-date {
    color: #777;
    font-size: 0.8rem;
}

.attendee-item {
    display: flex;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid #f0f0f0;
}

.attendee-item:last-child {
    border-bottom: none;
}

.attendee-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: #e9ecef;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
}

.attendee-info {
    flex-grow: 1;
}

.attendee-name {
    font-weight: 500;
    margin: 0;
}

.attendee-email {
    color: #777;
    font-size: 0.8rem;
    margin: 0;
}

.chart-container {
    position: relative;
    height: 300px;
}
</style>
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
<script>
    // Bootstrap Icons
    document.addEventListener('DOMContentLoaded', function() {
        const iconLink = document.createElement('link');
        iconLink.rel = 'stylesheet';
        iconLink.href = 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css';
        document.head.appendChild(iconLink);
        
        // Event Analytics Chart
        const eventCtx = document.getElementById('eventAnalyticsChart').getContext('2d');
        const eventChart = new Chart(eventCtx, {
            type: 'line',
            data: {
                labels: ['1 Mar', '5 Mar', '10 Mar', '15 Mar', '20 Mar', '25 Mar', '30 Mar'],
                datasets: [{
                    label: 'Registrations',
                    data: [12, 19, 28, 35, 42, 58, 65],
                    borderColor: '#0B5394',
                    backgroundColor: 'rgba(11, 83, 148, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
        
        // Categories Chart
        const catCtx = document.getElementById('categoriesChart').getContext('2d');
        const catChart = new Chart(catCtx, {
            type: 'doughnut',
            data: {
                labels: ['Cultural', 'Academic', 'Professional', 'Entertainment', 'Community Service'],
                datasets: [{
                    data: [35, 25, 20, 15, 5],
                    backgroundColor: [
                        '#0B5394', 
                        '#28a745', 
                        '#ffc107', 
                        '#dc3545',
                        '#6c757d'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    });
</script>
</body>
</html>