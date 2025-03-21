-- Create the database if it doesn't exist
CREATE DATABASE IF NOT EXISTS CampusEventManagement;
USE CampusEventManagement;

-- Users table for students and organizers
CREATE TABLE Users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    asheshi_id VARCHAR(20) UNIQUE NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('student', 'organizer', 'admin') DEFAULT 'student',
    phone_number VARCHAR(15),
    profile_picture VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Events table
CREATE TABLE Events (
    event_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    start_datetime DATETIME NOT NULL,
    end_datetime DATETIME NOT NULL,
    location VARCHAR(255),
    organizer_id INT,
    category VARCHAR(100),
    max_capacity INT,
    is_public BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (organizer_id) REFERENCES Users(user_id)
);

-- Registrations table
CREATE TABLE Registrations (
    registration_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    event_id INT,
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    attendance_status ENUM('pending', 'attended', 'missed') DEFAULT 'pending',
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES Events(event_id) ON DELETE CASCADE
);

-- Feedback table
CREATE TABLE Feedback (
    feedback_id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT,
    user_id INT,
    rating INT CHECK (rating BETWEEN 1 AND 5),
    comments TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES Events(event_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE
);

-- Event Analytics Table
CREATE TABLE EventAnalytics (
    analytics_id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT,
    total_registrations INT DEFAULT 0,
    total_attended INT DEFAULT 0,
    total_feedback INT DEFAULT 0,
    average_rating FLOAT DEFAULT 0,
    FOREIGN KEY (event_id) REFERENCES Events(event_id) ON DELETE CASCADE
);

-- Admin Panel (For ASC & OSCA Management)
CREATE TABLE AdminPanel (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    permissions TEXT,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE
);

-- Event Calendar Integration
CREATE TABLE EventCalendar (
    calendar_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    event_id INT,
    sync_status ENUM('synced', 'pending') DEFAULT 'pending',
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES Events(event_id) ON DELETE CASCADE
);

USE CampusEventManagement;
SHOW TABLES;
