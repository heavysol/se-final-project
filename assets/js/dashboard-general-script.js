/* Contains functions for use across the dashboards */

/* Constants and associated vars*/
var studentSidebarActives = [] // for controlling active class in sidebar menu selections; see getSidebar
const studentSidebarIconNum = 7 // Number of menu selections in sidebar
for (let i = 0; i < studentSidebarIconNum; i++) {
    studentSidebarActives.push('');
}
const studentSidebar = `<div class="sidebar">
        <div class="sidebar-header">
            <h3>Campus Events</h3>
            <div class="text-white-50 small">Student Dashboard</div>
        </div>
        <ul class="sidebar-menu">
            <li><a href="#" ${studentSidebarIconNum[0]}><i class="bi bi-speedometer2"></i> Dashboard</a></li>
            <li><a href="./events.php" ${studentSidebarIconNum[1]}><i class="bi bi-calendar-event"></i> Events</a></li>
            <li><a href="#" ${studentSidebarIconNum[2]}><i class="bi bi-journal-check"></i> My Registrations</a></li>
            <li><a href="#" ${studentSidebarIconNum[3]}><i class="bi bi-star"></i> Favorites</a></li>
            <li><a href="#" ${studentSidebarIconNum[4]}><i class="bi bi-people"></i> Clubs & Organizations</a></li>
        </ul>
    </div>`;

var organiserSidebarActives = [] // for controlling active class in sidebar menu selections; see getSidebar
const organiserSidebarIconNum = 6 // Number of menu selections in sidebar
for (let i = 0; i < organiserSidebarIconNum; i++) {
    organiserSidebarActives.push('');
}
const organiserSidebar = `<div class="sidebar">
<div class="sidebar-header">
    <h3>Campus Events</h3>
    <div class="text-white-50 small">Organizer Dashboard</div>
</div>
<ul class="sidebar-menu">
    <li><a href="#" ${organiserSidebarIconNum[0]}><i class="bi bi-speedometer2"></i> Dashboard</a></li>
    <li><a href="#" ${organiserSidebarIconNum[1]}><i class="bi bi-calendar-event"></i> My Events</a></li>
    <li><a href="#" ${organiserSidebarIconNum[2]}><i class="bi bi-plus-circle"></i> Create Event</a></li>
    <li><a href="#" ${organiserSidebarIconNum[3]}><i class="bi bi-people"></i> Attendees</a></li>
    <li><a href="#" ${organiserSidebarIconNum[4]}><i class="bi bi-graph-up"></i> Analytics</a></li>
    <li><a href="#" ${organiserSidebarIconNum[5]}><i class="bi bi-chat-dots"></i> Feedback</a></li>
</ul>
</div>`;

var adminSidebarActives = [] // for controlling active class in sidebar menu selections; see getSidebar
const adminSidebarIconNum = 6 // Number of menu selections in sidebar
for (let i = 0; i < adminSidebarIconNum; i++) {
    adminSidebarActives.push('');
}
const adminSidebar = `<div class="sidebar">
        <div class="sidebar-header">
            <h3>Campus Events</h3>
            <div class="text-white-50 small">Admin Dashboard</div>
        </div>
        <ul class="sidebar-menu">
            <li><a href="#" ${adminSidebarIconNum[0]}><i class="bi bi-speedometer2"></i> Dashboard</a></li>
            <li><a href="#" ${adminSidebarIconNum[1]}><i class="bi bi-calendar-event"></i> Events Management</a></li>
            <li><a href="#" ${adminSidebarIconNum[2]}><i class="bi bi-people"></i> User Management</a></li>
            <li><a href="#" ${adminSidebarIconNum[3]}><i class="bi bi-building"></i> Organizations</a></li>
            <li><a href="#" ${adminSidebarIconNum[4]}><i class="bi bi-graph-up"></i> Analytics</a></li>
            <li><a href="#" ${adminSidebarIconNum[5]}><i class="bi bi-flag"></i> Reports</a></li>
        </ul>
    </div>`;

const notifySettingsButtons = `<li><a href="#"><i class="bi bi-bell"></i> Notifications</a></li>
            <li><a href="#"><i class="bi bi-gear"></i> Settings</a></li>`;

// Functions
function getSidebar(type, menuIndex) {
    var sidebar = null;
    switch (key) {
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

    
}