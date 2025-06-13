/* Contains functions for use across the dashboards */

/* Constants and associated vars*/
const studentSidebar = `<div class="sidebar">
        <div class="sidebar-header">
            <h3>Campus Events</h3>
            <div class="text-white-50 small">Student Dashboard</div>
        </div>
        <ul class="sidebar-menu">
            <li><a href="#" class="active"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
            <li><a href="./events.php"><i class="bi bi-calendar-event"></i> Events</a></li>
            <li><a href="#"><i class="bi bi-journal-check"></i> My Registrations</a></li>
            <li><a href="#"><i class="bi bi-star"></i> Favorites</a></li>
            <li><a href="#"><i class="bi bi-people"></i> Clubs & Organizations</a></li>
            <li><a href="#"><i class="bi bi-bell"></i> Notifications</a></li>
            <li><a href="#"><i class="bi bi-gear"></i> Settings</a></li>
        </ul>
    </div>`;

const organiserSidebar = `<div class="sidebar">
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
</div>`;

const adminSidebar = `<div class="sidebar">
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
    </div>`;

// Functions
function getSidebar(type, menuIndex = 0) {
    console.log('function called')
    var sidebar = null;
    switch (type) {
        case 's':
            sidebar = studentSidebar
            break;
        case 'o':
            sidebar = organiserSidebar;
            break;
        case 'a':
            sidebar = adminSidebar;
            break;
        default:
            break;
    }

    document.getElementById('sidebar').innerHTML = sidebar;
}
console.log('script loaded')
getSidebar('s')