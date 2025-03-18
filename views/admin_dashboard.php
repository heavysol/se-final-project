<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrator Dashboard - Campus Events</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --sidebar-width: 250px;
            --primary-color: #0B5394;
            --secondary-color: #F3F6F9;
            --accent-color: #FFD700;
            --success-color:#0B5394 ;
            --warning-color: #ffc107;
            --danger-color:#0B5394;
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
        
        .chart-container {
            position: relative;
            height: 300px;
        }
        
        .pagination {
            justify-content: center;
            margin-top: 20px;
        }
        
        .dropdown-toggle::after {
            display: none;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h3>Campus Events</h3>
            <div class="text-white-50 small">Admin Dashboard</div>
        </div>
        <ul class="sidebar-menu">
            <li><a href="#" class="active"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
            <li><a href="#"><i class="bi bi-calendar-event"></i> Events Management</a></li>
            <li><a href="#"><i class="bi bi-people"></i> User Management</a></li>
            <li><a href="#"><i class="bi bi-building"></i> Organizations</a></li>
            <li><a href="#"><i class="bi bi-graph-up"></i> Analytics</a></li>
            <li><a href="#"><i class="bi bi-bell"></i> Notifications</a></li>
            <li><a href="#"><i class="bi bi-flag"></i> Reports</a></li>
            <li><a href="#"><i class="bi bi-gear"></i> Settings</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <div class="row mb-4">
                <div class="col-md-6">
                    <h2>Administrator Dashboard</h2>
                    <p class="text-muted">Overview of campus events and activities</p>
                </div>
                <div class="col-md-6 text-end">
                    <div class="btn-group">
                        <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-file-earmark-arrow-down"></i> Export
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#">PDF Report</a></li>
                            <li><a class="dropdown-item" href="#">Excel Spreadsheet</a></li>
                            <li><a class="dropdown-item" href="#">CSV Data</a></li>
                        </ul>
                    </div>
                    <button class="btn btn-primary ms-2"><i class="bi bi-plus"></i> Create New Event</button>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="stats-icon primary">
                            <i class="bi bi-calendar2-check"></i>
                        </div>
                        <div class="stats-info">
                            <h3 class="stats-number">42</h3>
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
                            <h3 class="stats-number">1,284</h3>
                            <p class="stats-label">Total Registrations</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="stats-icon warning">
                            <i class="bi bi-building"></i>
                        </div>
                        <div class="stats-info">
                            <h3 class="stats-number">18</h3>
                            <p class="stats-label">Active Organizations</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="stats-icon danger">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>
                        <div class="stats-info">
                            <h3 class="stats-number">5</h3>
                            <p class="stats-label">Pending Approvals</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-8">
                    <div class="dashboard-card">
                        <h4>
                            Recent Events
                            <button class="btn btn-sm btn-outline-primary">View All</button>
                        </h4>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Event Name</th>
                                        <th>Organizer</th>
                                        <th>Date</th>
                                        <th>Registered</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Akwaaba Night</td>
                                        <td>ASC</td>
                                        <td>20 Mar 2025</td>
                                        <td>156/200</td>
                                        <td><span class="event-status active">Active</span></td>
                                        <td>
                                            <a href="#" class="event-action-btn"><i class="bi bi-eye"></i></a>
                                            <a href="#" class="event-action-btn"><i class="bi bi-pencil"></i></a>
                                            <a href="#" class="event-action-btn"><i class="bi bi-trash"></i></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Global Café: Spanish Culture</td>
                                        <td>OSCA</td>
                                        <td>21 Mar 2025</td>
                                        <td>98/120</td>
                                        <td><span class="event-status active">Active</span></td>
                                        <td>
                                            <a href="#" class="event-action-btn"><i class="bi bi-eye"></i></a>
                                            <a href="#" class="event-action-btn"><i class="bi bi-pencil"></i></a>
                                            <a href="#" class="event-action-btn"><i class="bi bi-trash"></i></a>
                                        </td>
                                    </tr>
                                    <td>Annual Career Fair</td>
                                    <td>Career Services</td>
                                    <td>24 Mar 2025</td>
                                    <td>210/300</td>
                                    <td><span class="event-status active">Active</span></td>
                                    <td>
                                        <a href="#" class="event-action-btn"><i class="bi bi-eye"></i></a>
                                        <a href="#" class="event-action-btn"><i class="bi bi-pencil"></i></a>
                                        <a href="#" class="event-action-btn"><i class="bi bi-trash"></i></a>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Entrepreneurship Workshop</td>
                                    <td>Business Department</td>
                                    <td>31 Mar 2025</td>
                                    <td>45/100</td>
                                    <td><span class="event-status pending">Pending</span></td>
                                    <td>
                                        <a href="#" class="event-action-btn"><i class="bi bi-eye"></i></a>
                                        <a href="#" class="event-action-btn"><i class="bi bi-pencil"></i></a>
                                        <a href="#" class="event-action-btn"><i class="bi bi-trash"></i></a>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Poetry Night</td>
                                    <td>Literary Club</td>
                                    <td>02 Apr 2025</td>
                                    <td>0/50</td>
                                    <td><span class="event-status cancelled">Cancelled</span></td>
                                    <td>
                                        <a href="#" class="event-action-btn"><i class="bi bi-eye"></i></a>
                                        <a href="#" class="event-action-btn"><i class="bi bi-pencil"></i></a>
                                        <a href="#" class="event-action-btn"><i class="bi bi-trash"></i></a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <nav>
                        <ul class="pagination">
                            <li class="page-item disabled">
                                <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Previous</a>
                            </li>
                            <li class="page-item active"><a class="page-link" href="#">1</a></li>
                            <li class="page-item"><a class="page-link" href="#">2</a></li>
                            <li class="page-item"><a class="page-link" href="#">3</a></li>
                            <li class="page-item">
                                <a class="page-link" href="#">Next</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
            <div class="col-md-4">
                <div class="dashboard-card">
                    <h4>
                        Event Participation
                        <select class="form-select form-select-sm" style="width: auto;">
                            <option>Last 7 days</option>
                            <option>Last 30 days</option>
                            <option>Last 90 days</option>
                        </select>
                    </h4>
                    <div class="chart-container">
                        <!-- Placeholder for chart -->
                        <div style="text-align: center; padding-top: 120px; color: #999;">
                            [Event Participation Chart]
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="dashboard-card">
                    <h4>
                        Pending Approvals
                        <span class="badge badge-custom">5 New</span>
                    </h4>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Event Name</th>
                                    <th>Organizer</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Cultural Night</td>
                                    <td>International Students Association</td>
                                    <td>01 Apr 2025</td>
                                    <td>
                                        <button class="btn btn-sm btn-success">Approve</button>
                                        <button class="btn btn-sm btn-danger">Reject</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Tech Talk: AI Ethics</td>
                                    <td>Computer Science Club</td>
                                    <td>05 Apr 2025</td>
                                    <td>
                                        <button class="btn btn-sm btn-success">Approve</button>
                                        <button class="btn btn-sm btn-danger">Reject</button>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Basketball Tournament</td>
                                    <td>Sports Committee</td>
                                    <td>10 Apr 2025</td>
                                    <td>
                                        <button class="btn btn-sm btn-success">Approve</button>
                                        <button class="btn btn-sm btn-danger">Reject</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="dashboard-card">
                    <h4>
                        Recent Activity
                        <button class="btn btn-sm btn-outline-primary">View All</button>
                    </h4>
                    <div class="activity-list">
                        <div class="d-flex mb-3">
                            <div class="flex-shrink-0" style="width: 40px; height: 40px; background-color: #e9ecef; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-person-plus"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-0">New event submitted</h6>
                                    <small>3 mins ago</small>
                                </div>
                                <p class="mb-0 text-muted">Cultural Night by International Students Association</p>
                            </div>
                        </div>
                        <div class="d-flex mb-3">
                            <div class="flex-shrink-0" style="width: 40px; height: 40px; background-color: #e9ecef; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-check-circle"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-0">Event approved</h6>
                                    <small>1 hour ago</small>
                                </div>
                                <p class="mb-0 text-muted">Annual Career Fair by Career Services</p>
                            </div>
                        </div>
                        <div class="d-flex mb-3">
                            <div class="flex-shrink-0" style="width: 40px; height: 40px; background-color: #e9ecef; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-pencil-square"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-0">Event updated</h6>
                                    <small>3 hours ago</small>
                                </div>
                                <p class="mb-0 text-muted">Global Café: Spanish Culture by OSCA</p>
                            </div>
                        </div>
                        <div class="d-flex mb-3">
                            <div class="flex-shrink-0" style="width: 40px; height: 40px; background-color: #e9ecef; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-x-circle"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-0">Event cancelled</h6>
                                    <small>5 hours ago</small>
                                </div>
                                <p class="mb-0 text-muted">Poetry Night by Literary Club</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css"></script>
</body>
</html>