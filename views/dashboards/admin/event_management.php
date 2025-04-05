<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Comprehensive Event Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.2/main.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../../assets/css/event-management-styles.css">

</head>
<body>
    <div class="container-fluid mt-3">
        <h2 class="mb-4">Event Management System</h2>
        
        <!-- Navigation Tabs -->
        <ul class="nav nav-tabs mb-4" id="eventTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="list-tab" data-bs-toggle="tab" data-bs-target="#listView" type="button">
                    <i class="fas fa-list"></i> Event List
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="calendar-tab" data-bs-toggle="tab" data-bs-target="#calendarView" type="button">
                    <i class="fas fa-calendar-alt"></i> Calendar
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="approval-tab" data-bs-toggle="tab" data-bs-target="#approvalView" type="button">
                    <i class="fas fa-check-circle"></i> Approval Queue 
                    <span class="badge bg-danger" id="pendingCount">5</span>
                </button>
            </li>
        </ul>
        
        <!-- Tab Content -->
        <div class="tab-content" id="eventTabContent">
            <!-- Event List View -->
            <div class="tab-pane fade show active" id="listView" role="tabpanel">
                <div class="row">
                    <div class="col-12 mb-3">
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createEventModal">
                            <i class="fas fa-plus"></i> Create New Event
                        </button>
                        
                        <div class="float-end">
                            <div class="btn-group">
                                <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                    Bulk Actions
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#" id="bulkApprove">Approve Selected</a></li>
                                    <li><a class="dropdown-item" href="#" id="bulkCancel">Cancel Selected</a></li>
                                    <li><a class="dropdown-item" href="#" id="bulkDelete">Delete Selected</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Search and Filter Options -->
                    <div class="col-12 search-filters mb-3">
                        <div class="row">
                            <div class="col-md-3">
                                <input type="text" class="form-control" placeholder="Search events..." id="eventSearch">
                            </div>
                            <div class="col-md-2">
                                <select class="form-select" id="statusFilter">
                                    <option value="">All Statuses</option>
                                    <option value="active">Active</option>
                                    <option value="pending">Pending</option>
                                    <option value="cancelled">Cancelled</option>
                                    <option value="completed">Completed</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select class="form-select" id="categoryFilter">
                                    <option value="">All Categories</option>
                                    <option value="Academic">Academic</option>
                                    <option value="Social">Social</option>
                                    <option value="Career">Career</option>
                                    <option value="Cultural">Cultural</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" id="sortOption">
                                    <option value="date_asc">Date (Ascending)</option>
                                    <option value="date_desc">Date (Descending)</option>
                                    <option value="title">Title (A-Z)</option>
                                    <option value="organization">Organization</option>
                                    <option value="venue">Venue</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button class="btn btn-primary w-100" id="applyFilters">Apply</button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Event Table -->
                    <div class="col-12">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" id="selectAll"></th>
                                        <th>Title</th>
                                        <th>Date & Time</th>
                                        <th>Category</th>
                                        <th>Venue</th>
                                        <th>Organization</th>
                                        <th>Capacity</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="eventsTableBody">
                                    <tr>
                                        <td><input type="checkbox" class="event-select" data-id="1"></td>
                                        <td>Global Café</td>
                                        <td>May 15, 2025 - 3:00 PM</td>
                                        <td>Cultural</td>
                                        <td>Student Center</td>
                                        <td>International Student Association</td>
                                        <td>80/100</td>
                                        <td><span class="badge status-active">Active</span></td>
                                        <td>
                                            <button class="btn btn-sm btn-info" onclick="viewEventDetails(1)">View</button>
                                            <button class="btn btn-sm btn-primary" onclick="editEvent(1)">Edit</button>
                                            <button class="btn btn-sm btn-danger" onclick="deleteEvent(1)">Delete</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><input type="checkbox" class="event-select" data-id="2"></td>
                                        <td>Career Fair</td>
                                        <td>June 10, 2025 - 10:00 AM</td>
                                        <td>Career</td>
                                        <td>Main Hall</td>
                                        <td>Career Services</td>
                                        <td>120/200</td>
                                        <td><span class="badge status-pending">Pending</span></td>
                                        <td>
                                            <button class="btn btn-sm btn-info" onclick="viewEventDetails(2)">View</button>
                                            <button class="btn btn-sm btn-primary" onclick="editEvent(2)">Edit</button>
                                            <button class="btn btn-sm btn-danger" onclick="deleteEvent(2)">Delete</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><input type="checkbox" class="event-select" data-id="3"></td>
                                        <td>Alumni Networking</td>
                                        <td>April 25, 2025 - 6:00 PM</td>
                                        <td>Social</td>
                                        <td>Conference Room A</td>
                                        <td>Alumni Association</td>
                                        <td>45/50</td>
                                        <td><span class="badge status-active">Active</span></td>
                                        <td>
                                            <button class="btn btn-sm btn-info" onclick="viewEventDetails(3)">View</button>
                                            <button class="btn btn-sm btn-primary" onclick="editEvent(3)">Edit</button>
                                            <button class="btn btn-sm btn-danger" onclick="deleteEvent(3)">Delete</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Calendar View -->
            <div class="tab-pane fade" id="calendarView" role="tabpanel">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-header">
                                <h5>Calendar Legend</h5>
                            </div>
                            <div class="card-body">
                                <div><span class="badge bg-success">&nbsp;</span> Academic Events</div>
                                <div><span class="badge bg-primary">&nbsp;</span> Social Events</div>
                                <div><span class="badge bg-info">&nbsp;</span> Career Events</div>
                                <div><span class="badge bg-warning">&nbsp;</span> Cultural Events</div>
                                <hr>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" id="showAcademic" checked>
                                    <label class="form-check-label" for="showAcademic">Show Academic</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" id="showSocial" checked>
                                    <label class="form-check-label" for="showSocial">Show Social</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" id="showCareer" checked>
                                    <label class="form-check-label" for="showCareer">Show Career</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" id="showCultural" checked>
                                    <label class="form-check-label" for="showCultural">Show Cultural</label>
                                </div>
                                <hr>
                                <h6>Venue Availability</h6>
                                <div class="mb-3">
                                    <select class="form-select" id="venueFilter">
                                        <option value="">All Venues</option>
                                        <option value="Main Hall">Main Hall</option>
                                        <option value="Student Center">Student Center</option>
                                        <option value="Conference Room A">Conference Room A</option>
                                        <option value="Conference Room B">Conference Room B</option>
                                    </select>
                                </div>
                                <button class="btn btn-primary btn-sm w-100" id="applyCalendarFilters">Apply Filters</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-9">
                        <div class="card">
                            <div class="card-body">
                                <div id="calendar"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Approval Workflow View -->
            <div class="tab-pane fade" id="approvalView" role="tabpanel">
                <div class="row">
                    <div class="col-12">
                        <h4>Pending Events for Approval</h4>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" id="selectAllPending"></th>
                                        <th>Title</th>
                                        <th>Date & Time</th>
                                        <th>Organization</th>
                                        <th>Venue</th>
                                        <th>Submitted By</th>
                                        <th>Submission Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="pendingEventsTable">
                                    <tr>
                                        <td><input type="checkbox" class="pending-select" data-id="2"></td>
                                        <td>Career Fair</td>
                                        <td>June 10, 2025 - 10:00 AM</td>
                                        <td>Career Services</td>
                                        <td>Main Hall</td>
                                        <td>John Smith</td>
                                        <td>April 1, 2025</td>
                                        <td>
                                            <button class="btn btn-sm btn-info" onclick="viewPendingEvent(2)">Review</button>
                                            <button class="btn btn-sm btn-success" onclick="approveEvent(2)">Approve</button>
                                            <button class="btn btn-sm btn-danger" onclick="rejectEvent(2)">Reject</button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><input type="checkbox" class="pending-select" data-id="4"></td>
                                        <td>Research Symposium</td>
                                        <td>May 20, 2025 - 1:00 PM</td>
                                        <td>Science Department</td>
                                        <td>Conference Room B</td>
                                        <td>Sarah Johnson</td>
                                        <td>April 2, 2025</td>
                                        <td>
                                            <button class="btn btn-sm btn-info" onclick="viewPendingEvent(4)">Review</button>
                                            <button class="btn btn-sm btn-success" onclick="approveEvent(4)">Approve</button>
                                            <button class="btn btn-sm btn-danger" onclick="rejectEvent(4)">Reject</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Event Creation Modal -->
        <div class="modal fade" id="createEventModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Create New Event</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createEventModal">
                        Create New Event
                    </button>
                    <div class="modal-body">
                        <form id="eventCreationForm">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Event Title</label>
                                        <input type="text" class="form-control" name="title" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Description</label>
                                        <textarea class="form-control" name="description" rows="3"></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Category</label>
                                        <select class="form-select" name="category">
                                            <option>Academic</option>
                                            <option>Social</option>
                                            <option>Career</option>
                                            <option>Cultural</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Start Date and Time</label>
                                        <input type="datetime-local" class="form-control" name="start_datetime" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">End Date and Time</label>
                                        <input type="datetime-local" class="form-control" name="end_datetime" required>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Venue/Location</label>
                                        <select class="form-select" name="venue" required>
                                            <option value="">Select a venue</option>
                                            <option>Main Hall</option>
                                            <option>Student Center</option>
                                            <option>Conference Room A</option>
                                            <option>Conference Room B</option>
                                            <option>Auditorium</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Maximum Capacity</label>
                                        <input type="number" class="form-control" name="capacity" min="1">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Registration Required?</label>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="registration" value="yes">
                                            <label class="form-check-label">Yes</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="registration" value="no" checked>
                                            <label class="form-check-label">No</label>
                                        </div>
                                    </div>
                            <div class="text-end mt-3">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Create Event</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="modal fade" id="eventDetailModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Event Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                
                            <div class="col-md-7">
                                <ul class="nav nav-tabs" id="detailTabs" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="attendees-tab" data-bs-toggle="tab" data-bs-target="#attendeesTab">Attendees</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="checkin-tab" data-bs-toggle="tab" data-bs-target="#checkinTab">Check-in</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="feedback-tab" data-bs-toggle="tab" data-bs-target="#feedbackTab">Feedback</button>
                                    </li>
                                </ul>
        
                                <div class="tab-content mt-3" id="detailTabContent">
                                    <div class="tab-pane fade show active" id="attendeesTab" role="tabpanel">
                                        <div class="d-flex justify-content-between mb-3">
                                            <div><strong>Registration Stats:</strong> <span id="registrationStats">80/100 registered (80% capacity)</span></div>
                                            <button class="btn btn-sm btn-primary" id="addAttendee"><i class="fas fa-plus"></i> Add Attendee</button>
                                        </div>
                                        <div class="input-group mb-3">
                                            <input type="text" class="form-control" placeholder="Search attendees..." id="attendeeSearch">
                                            <button class="btn btn-outline-secondary" type="button">Search</button>
                                        </div>
                                        <div class="attendee-list">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Name</th>
                                                        <th>Email</th>
                                                        <th>Registration Date</th>
                                                        <th>Status</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="attendeeTableBody">
                                                    <tr>
                                                        <td>Michael Johnson</td>
                                                        <td>michael@example.com</td>
                                                        <td>April 2, 2025</td>
                                                        <td><span class="badge bg-success">Confirmed</span></td>
                                                        <td><button class="btn btn-sm btn-danger">Remove</button></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Emily Chen</td>
                                                        <td>emily@example.com</td>
                                                        <td>April 1, 2025</td>
                                                        <td><span class="badge bg-success">Confirmed</span></td>
                                                        <td><button class="btn btn-sm btn-danger">Remove</button></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
        
                                    <div class="tab-pane fade" id="checkinTab" role="tabpanel">
                                        <div class="mb-3">
                                            <div class="input-group">
                                                <input type="text" class="form-control" placeholder="Scan badge or enter email..." id="checkinInput">
                                                <button class="btn btn-primary" type="button">Check-in</button>
                                            </div>
                                        </div>
                                        <div class="row text-center">
                                            <div class="col-6">
                                                <div class="card bg-light mb-3">
                                                    <div class="card-body">
                                                        <h3 id="checkedInCount">42</h3>
                                                        <p class="text-muted">Checked-in</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="card bg-light mb-3">
                                                    <div class="card-body">
                                                        <h3 id="remainingCount">38</h3>
                                                        <p class="text-muted">Remaining</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <h6>Recent Check-ins</h6>
                                            <div class="list-group" id="recentCheckins">
                                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <strong>Emily Chen</strong>
                                                        <small class="d-block text-muted">emily@example.com</small>
                                                    </div>
                                                    <span class="text-muted">2:45 PM</span>
                                                </div>
                                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <strong>Michael Johnson</strong>
                                                        <small class="d-block text-muted">michael@example.com</small>
                                                    </div>
                                                    <span class="text-muted">2:42 PM</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
        
                                    <div class="tab-pane fade" id="feedbackTab" role="tabpanel">
                                        <p>No feedback available yet.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> <!-- /.modal-body -->
                </div> <!-- /.modal-content -->
            </div> <!-- /.modal-dialog -->
        </div> <!-- /.modal -->
        
        <script>
            document.getElementById('createEventModal')?.addEventListener('submit', function(e) {
                e.preventDefault();
                console.log('event Creation Submitted');
            });
            document.addEventListener("DOMContentLoaded", function () {
        const now = new Date();
        const pad = num => String(num).padStart(2, '0');

        const formattedDateTime = `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}T${pad(now.getHours())}:${pad(now.getMinutes())}`;

        const startInput = document.querySelector('input[name="start_datetime"]');
        const endInput = document.querySelector('input[name="end_datetime"]');

        startInput.min = formattedDateTime;
        endInput.min = formattedDateTime;

        startInput.value = formattedDateTime;

        // Add 1 hour for end time by default
        const oneHourLater = new Date(now.getTime() + 60 * 60 * 1000);
        const formattedEndDateTime = `${oneHourLater.getFullYear()}-${pad(oneHourLater.getMonth() + 1)}-${pad(oneHourLater.getDate())}T${pad(oneHourLater.getHours())}:${pad(oneHourLater.getMinutes())}`;
        endInput.value = formattedEndDateTime;
    });
        </script>
        
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    </body>
        </html>
