CREATE TABLE IF NOT EXISTS system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    site_name VARCHAR(255) NOT NULL DEFAULT 'Campus Events',
    site_description TEXT,
    contact_email VARCHAR(255) NOT NULL DEFAULT 'admin@campus-events.edu',
    max_events_per_organizer INT NOT NULL DEFAULT 10,
    registration_deadline_hours INT NOT NULL DEFAULT 24,
    enable_email_notifications BOOLEAN NOT NULL DEFAULT 1,
    enable_system_notifications BOOLEAN NOT NULL DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default settings if table is empty
INSERT INTO system_settings (
    site_name,
    site_description,
    contact_email,
    max_events_per_organizer,
    registration_deadline_hours,
    enable_email_notifications,
    enable_system_notifications
) SELECT 
    'Campus Events',
    'University Event Management System',
    'admin@campus-events.edu',
    10,
    24,
    1,
    1
FROM dual
WHERE NOT EXISTS (SELECT 1 FROM system_settings);