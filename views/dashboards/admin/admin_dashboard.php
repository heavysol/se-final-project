<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrator Dashboard - Campus Events</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../../assets/css/general-styles.css">
    <link rel="stylesheet" href="../../../assets/css/dashboard-styles.css">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h3>Campus Events</h3>
            <div class="text-white-50 small">Admin Dashboard</div>
        </div>
        <ul class="sidebar-menu">
            <li><a href="./student_dashboard.php" class="active"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
            <li><a href="event_management.php"><i class="bi bi-calendar-event"></i> Events Management</a></li>
            <li><a href="user_management.php"><i class="bi bi-people"></i> User Management</a></li>
            <li><a href="event_organization.php"><i class="bi bi-building"></i> Organizations</a></li>
            <li><a href="analytics.html"><i class="bi bi-graph-up"></i> Analytics</a></li>
            <li><a href="../notifications.php"><i class="bi bi-bell"></i> Notifications</a></li>
            <li><a href="../settings.php"><i class="bi bi-gear"></i> Settings</a></li>
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
                    <!-- <button class="btn btn-primary ms-2"><i class="bi bi-plus"></i> Create New Event</button> -->
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
                            <!-- <p class="stats-label">Pending Approvals</p> -->
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="dashboard-card">
                       
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
                                            <button class="event-action-btn" title="Edit">Edit</button>
                                            <button class="event-action-btn" title="Delete">Delete</button>
                                            <button class="event-action-btn" title="Details">Details</button>
                                          </td>                                     
                                    </tr>
                                    <tr>
                                        <td>Global Café: Spanish Culture</td>
                                        <td>OSCA</td>
                                        <td>21 Mar 2025</td>
                                        <td>98/120</td>
                                        <td><span class="event-status active">Active</span></td>
                                        <td>
                                            <a href="#" class="event-action-btn" title="Edit">Edit</a>
                                            <a href="#" class="event-action-btn" title="Delete">Delete</a>
                                            <a href="#" class="event-action-btn" title="Details">Details</a>
                                          </td>                                          
                                    </tr>
                                    <td>Annual Career Fair</td>
                                    <td>Career Services</td>
                                    <td>24 Mar 2025</td>
                                    <td>210/300</td>
                                    <td><span class="event-status active">Active</span></td>
                                    <td>
                                        <button class="event-action-btn" title="Edit">Edit</button>
                                        <button class="event-action-btn" title="Delete">Delete</button>
                                        <button class="event-action-btn" title="Details">Details</button>
                                      </td>                                 
                                </tr>
                                <tr>
                                    <td>Entrepreneurship Workshop</td>
                                    <td>Business Department</td>
                                    <td>31 Mar 2025</td>
                                    <td>45/100</td>
                                    <td><span class="event-status pending">Pending</span></td>
                                    <td>
                                        <button class="event-action-btn" title="Edit">Edit</button>
                                        <button class="event-action-btn" title="Delete">Delete</button>
                                        <button class="event-action-btn" title="Details">Details</button>
                                      </td>                                   
                                </tr>
                                <tr>
                                    <td>Poetry Night</td>
                                    <td>Literary Club</td>
                                    <td>02 Apr 2025</td>
                                    <td>0/50</td>
                                    <td><span class="event-status cancelled">Cancelled</span></td>
                                    <td>
                                        <button class="event-action-btn" title="Edit">Edit</button>
                                        <button class="event-action-btn" title="Delete">Delete</button>
                                        <button class="event-action-btn" title="Details">Details</button>
                                      </td>
                                      
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
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