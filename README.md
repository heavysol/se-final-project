# se-final-project
Software Engineering final project repo for Group 19 (Abigail, Kojo, Rahinatu, Steve)

NB: This is _not_ the final version of this README file; more updates are expected to come.

# Problem statement
In Ashesi University, student participation in campus events is low. This is due to the lack of a  centralised platform to put event notices, register and be reminded about events for Ashesi. Event organisers also struggle with tracking their campus event attendance and event management.

# Solution
A **web-based campus event management and registration platform** that aims
to address these issues by centralising event information, streamlining registrations, and
providing automated reminders; the platform will also simplify event planning, promotion, and participation.

# Requirements
1. Functional:
- Event Management: Organisers can create, edit, and delete events.
- Registration and Reminders: Students can register for events and receive reminders
via email or SMS.
- Event Recommendations: Provide personalized event suggestions.
- Feedback Collection: Gather post-event reviews.
- Calendar Integration: Allow students to sync events with personal calendars.
- User Authentication: Secure login for students and organisers using their Ashesi
credentials.
- Admin Panel: Provide ASC and OSCA with tools to manage events and view
analytics.

2. Non-functional:
- Performance: The platform should load event pages within 3 seconds.
- Scalability: It should handle up to 500 concurrent users without performance issues.
- Security: Use HTTPS and encrypted passwords to protect user data.
- Usability: The interface should be intuitive and mobile-friendly.
- Reliability: Keeping event registrations available 24/7.
- Maintainability: Use a modular code structure to facilitate updates and debugging.
- Accessibility: Adhere to web accessibility standards to ensure usability for all students.

# Tech Stack
1. Frontend:
- HTML: This is used to create the structural layout of the platform.
- Vanilla CSS & Bootstrap: This is for styling and ensuring mobile responsiveness.
- JavaScript: For interactive elements and dynamic behavior.

2. Backend:
- PHP: For managing business logic and server-side operations.
- MySQL: For storing user data, event details, and registration information.

3. Other Tools:
- XAMPP: For local development and testing
- phpMyAdmin: To manage and monitor the MySQL database

# File structure
1. actions: contains backend code for controlling business logic of the functionalities.
2. assets: contains styling code and multimedia assets for the presentation layer (styling of frontend) of the platform.
3. db: contains db of platform
4. functions: contains functions and methods used in actions
5. plans: contains design docs and diagrams of platform
6. utils: contains useful utilities and miscellaneous functions
7. contains: contains frontend (html pages) of platform