<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Campus Events</title>
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
            <li><a href="#" class="active"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
            <li><a href="#"><i class="bi bi-calendar-event"></i> Events</a></li>
            <li><a href="#"><i class="bi bi-journal-check"></i> My Registrations</a></li>
            <li><a href="#"><i class="bi bi-star"></i> Favorites</a></li>
            <li><a href="#"><i class="bi bi-people"></i> Clubs & Organizations</a></li>
            <li><a href="#"><i class="bi bi-bell"></i> Notifications</a></li>
            <li><a href="#"><i class="bi bi-gear"></i> Settings</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <div class="row mb-4">
                <div class="col-md-8">
                    <h2>Welcome, John!</h2>
                    <p class="text-muted">Discover what's happening on campus this week</p>
                </div>
                <div class="col-md-4 text-end">
                    <button class="btn btn-primary"><i class="bi bi-plus"></i> Find Events</button>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-9">
                    <div class="dashboard-card">
                        <h4>
                            Upcoming Events
                            <span class="badge badge-custom">4 This Week</span>
                        </h4>
                        <div class="event-item">
                            <div class="event-title">Akwaaba Night</div>
                            <div class="event-details">
                                <i class="bi bi-calendar"></i> Thursday, 20 March 2025, 7:00 PM
                                <br>
                                <i class="bi bi-geo-alt"></i> Student Center
                            </div>
                            <div class="event-actions">
                                <button class="btn btn-sm btn-primary">Register</button>
                                <button class="btn btn-sm btn-outline-primary">Add to Calendar</button>
                            </div>
                        </div>
                        <div class="event-item">
                            <div class="event-title">Global Café: Spanish Culture</div>
                            <div class="event-details">
                                <i class="bi bi-calendar"></i> Friday, 21 March 2025, 4:00 PM
                                <br>
                                <i class="bi bi-geo-alt"></i> Hive
                            </div>
                            <div class="event-actions">
                                <button class="btn btn-sm btn-primary">Register</button>
                                <button class="btn btn-sm btn-outline-primary">Add to Calendar</button>
                            </div>
                        </div>
                        <div class="event-item">
                            <div class="event-title">Annual Career Fair</div>
                            <div class="event-details">
                                <i class="bi bi-calendar"></i> Monday, 24 March 2025, 9:00 AM
                                <br>
                                <i class="bi bi-geo-alt"></i> Archer Cornfield Courtyard
                            </div>
                            <div class="event-actions">
                                <button class="btn btn-sm btn-primary">Register</button>
                                <button class="btn btn-sm btn-outline-primary">Add to Calendar</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="dashboard-card">
                        <h4>My Calendar</h4>
                        <table class="small-calendar">
                            <thead>
                                <tr>
                                    <th>M</th>
                                    <th>T</th>
                                    <th>W</th>
                                    <th>T</th>
                                    <th>F</th>
                                    <th>S</th>
                                    <th>S</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>16</td>
                                    <td>17</td>
                                    <td>18</td>
                                    <td>19</td>
                                    <td class="has-event" title="Akwaaba Night">20</td>
                                    <td class="has-event" title="Global Café">21</td>
                                    <td>22</td>
                                </tr>
                                <tr>
                                    <td>23</td>
                                    <td class="has-event" title="Career Fair">24</td>
                                    <td>25</td>
                                    <td>26</td>
                                    <td>27</td>
                                    <td>28</td>
                                    <td>29</td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="text-center mt-3">
                            <button class="btn btn-sm btn-outline-primary">View Full Calendar</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="dashboard-card">
                        <h4>Recommended For You</h4>
                        <div class="event-item">
                            <div class="event-title">Ashesi Premier League Final</div>
                            <div class="event-details">
                                <i class="bi bi-calendar"></i> Sunday, 29 March 2025, 3:00 PM
                                <br>
                                <i class="bi bi-geo-alt"></i> Sports Complex
                            </div>
                            <div class="event-actions">
                                <button class="btn btn-sm btn-primary">Register</button>
                            </div>
                        </div>
                        <div class="event-item">
                            <div class="event-title">Entrepreneurship Workshop</div>
                            <div class="event-details">
                                <i class="bi bi-calendar"></i> Tuesday, 31 March 2025, 2:00 PM
                                <br>
                                <i class="bi bi-geo-alt"></i> R5 Building
                            </div>
                            <div class="event-actions">
                                <button class="btn btn-sm btn-primary">Register</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="dashboard-card">
                        <h4>My Registrations</h4>
                        <div class="event-item">
                            <div class="event-title">Global Café: Spanish Culture</div>
                            <div class="event-details">
                                <i class="bi bi-calendar"></i> Friday, 21 March 2025, 4:00 PM
                                <br>
                                <i class="bi bi-geo-alt"></i> Hive
                            </div>
                            <div class="event-actions">
                                <button class="btn btn-sm btn-outline-primary">View Ticket</button>
                                <button class="btn btn-sm btn-outline-danger">Cancel</button>
                            </div>
                        </div>
                        <div class="event-item">
                            <div class="event-title">Annual Career Fair</div>
                            <div class="event-details">
                                <i class="bi bi-calendar"></i> Monday, 24 March 2025, 9:00 AM
                                <br>
                                <i class="bi bi-geo-alt"></i> Archer Cornfield Courtyard
                            </div>
                            <div class="event-actions">
                                <button class="btn btn-sm btn-outline-primary">View Ticket</button>
                                <button class="btn btn-sm btn-outline-danger">Cancel</button>
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