-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 24, 2025 at 02:19 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `campuseventmanagement`
--

-- --------------------------------------------------------

--
-- Table structure for table `adminpanel`
--

CREATE TABLE `adminpanel` (
  `admin_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `permissions` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `eventanalytics`
--

CREATE TABLE `eventanalytics` (
  `analytics_id` int(11) NOT NULL,
  `event_id` int(11) DEFAULT NULL,
  `total_registrations` int(11) DEFAULT 0,
  `total_attended` int(11) DEFAULT 0,
  `total_feedback` int(11) DEFAULT 0,
  `average_rating` float DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `eventcalendar`
--

CREATE TABLE `eventcalendar` (
  `calendar_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `event_id` int(11) DEFAULT NULL,
  `sync_status` enum('synced','pending') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `eventcalendar`
--

INSERT INTO `eventcalendar` (`calendar_id`, `user_id`, `event_id`, `sync_status`) VALUES
(119, 1, 5, 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `event_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `start_datetime` datetime NOT NULL,
  `end_datetime` datetime NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `organizer_id` int(11) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `max_capacity` int(11) DEFAULT NULL,
  `is_public` tinyint(1) DEFAULT 1,
  `status` enum('pending','approved','rejected','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`event_id`, `title`, `description`, `start_datetime`, `end_datetime`, `location`, `organizer_id`, `category`, `max_capacity`, `is_public`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Ashesi Tech Talk', 'A panel discussion on the future of tech in Africa.', '2025-05-01 10:00:00', '2025-05-01 12:00:00', 'Ashesi Auditorium', 2, 'Technology', 120, 1, '', '2025-04-15 09:05:07', '2025-04-15 09:05:07'),
(2, 'Career Fair', 'Meet top employers and explore internship opportunities.', '2025-05-03 09:00:00', '2025-05-03 15:00:00', 'Ashesi Career Center', 3, 'Career', 150, 1, '', '2025-04-15 09:05:07', '2025-04-15 09:05:07'),
(3, 'Sustainability Hackathon', 'Find innovative solutions for sustainability challenges.', '2025-05-05 08:00:00', '2025-05-06 18:00:00', 'Ashesi Lab 3', 4, 'Innovation', 60, 1, '', '2025-04-15 09:05:07', '2025-04-15 09:05:07'),
(4, 'Ashesi Film Night', 'Enjoy a curated list of documentaries and student films.', '2025-05-07 18:00:00', '2025-05-07 21:00:00', 'Open Air Theater', 5, 'Entertainment', 80, 0, '', '2025-04-15 09:05:07', '2025-04-15 09:05:07'),
(5, 'Women in Leadership Forum', 'Empowering young women to lead boldly.', '2025-05-10 14:00:00', '2025-05-10 17:00:00', 'Ashesi Hall A', 2, 'Leadership', 90, 1, '', '2025-04-15 09:05:07', '2025-04-15 09:05:07'),
(6, 'Mental Health Awareness Week', 'Workshops and talks around student wellbeing.', '2025-05-12 10:00:00', '2025-05-16 17:00:00', 'Campus Wellness Center', 3, 'Health', 200, 1, '', '2025-04-15 09:05:07', '2025-04-15 09:05:07'),
(7, 'Cultural Day', 'A celebration of Africa’s diverse cultures at Ashesi.', '2025-05-18 10:00:00', '2025-05-18 16:00:00', 'Main Quad', 4, 'Culture', 300, 1, '', '2025-04-15 09:05:07', '2025-04-15 09:05:07'),
(9, 'Entrepreneurship Bootcamp', 'Learn how to launch your startup from scratch.', '2025-05-22 09:00:00', '2025-05-24 17:00:00', 'Business Incubator', 2, 'cultural', 100, 1, 'pending', '2025-04-15 09:05:07', '2025-04-22 23:37:27'),
(11, 'Save', 'ihfroht/pihghg', '2025-04-21 23:12:00', '2025-05-01 23:12:00', 'Car Park', 9, 'Social', 300, 1, 'approved', '2025-04-21 23:12:31', '2025-04-22 00:20:14'),
(12, 'zgfcgv', 'xfghjk', '2025-04-22 00:15:00', '2025-05-01 13:30:00', 'Car Park', 9, 'Sports', 134, 1, 'approved', '2025-04-22 00:17:10', '2025-04-22 00:20:19'),
(13, 'One to one session', 'Learn to interact', '2025-04-22 01:48:00', '2025-04-24 01:48:00', 'Car Park', 9, 'Academic', 45, 1, 'approved', '2025-04-22 01:49:15', '2025-04-22 01:50:38'),
(14, 'Self Control', 'Learning to control ourself is important.', '2025-04-22 22:22:00', '2025-04-30 22:22:00', 'Hive', 9, 'Social', 200, 1, 'approved', '2025-04-22 22:23:06', '2025-04-22 22:38:35'),
(15, 'Assignment', 'sui;fugr', '2025-04-22 23:09:00', '2025-06-05 23:09:00', 'RB100', 9, 'Academic', 300, 1, 'approved', '2025-04-22 23:10:02', '2025-04-22 23:32:08'),
(16, 'ryht', 'df', '2025-04-05 13:40:00', '2025-04-22 23:38:00', 'Business Incubator', 11, 'sports', 200, 1, 'approved', '2025-04-22 23:39:04', '2025-04-22 23:39:28'),
(17, 'scs', 'sac', '2025-04-22 23:58:00', '2025-04-24 23:58:00', 'scsa', 12, 'cultural', 34, 1, 'pending', '2025-04-22 23:58:20', '2025-04-22 23:58:20');

-- --------------------------------------------------------

--
-- Table structure for table `favorites`
--

CREATE TABLE `favorites` (
  `favorite_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `feedback_id` int(11) NOT NULL,
  `event_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` between 1 and 5),
  `comments` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `organizer_id` int(11) NOT NULL,
  `event_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `organizer_id`, `event_id`, `title`, `message`, `is_read`, `created_at`) VALUES
(1, 9, 11, 'Event Approved', 'Your event \'Save\' has been approved and is now live.', 0, '2025-04-22 00:20:14'),
(2, 9, 12, 'Event Approved', 'Your event \'zgfcgv\' has been approved and is now live.', 0, '2025-04-22 00:20:19'),
(3, 9, 13, 'Event Approved', 'Your event \'One to one session\' has been approved and is now live.', 0, '2025-04-22 01:50:38'),
(4, 9, 14, 'Event Approved', 'Your event \'Self Control\' has been approved and is now live.', 0, '2025-04-22 22:38:35'),
(5, 9, 15, 'Event Approved', 'Your event \'Assignment\' has been approved and is now live.', 0, '2025-04-22 23:32:08'),
(6, 2, 9, 'Event Approved', 'Your event \'Entrepreneurship Bootcamp\' has been approved and is now live.', 0, '2025-04-22 23:34:38'),
(7, 11, 16, 'Event Approved', 'Your event \'ryht\' has been approved and is now live.', 0, '2025-04-22 23:39:28');

-- --------------------------------------------------------

--
-- Table structure for table `registrations`
--

CREATE TABLE `registrations` (
  `registration_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `event_id` int(11) DEFAULT NULL,
  `registration_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `attendance_status` enum('pending','attended','missed') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `registrations`
--

INSERT INTO `registrations` (`registration_id`, `user_id`, `event_id`, `registration_date`, `attendance_status`) VALUES
(98, 1, 1, '2025-04-18 01:57:08', 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `task_id` int(11) NOT NULL,
  `organizer_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `due_date` datetime DEFAULT NULL,
  `status` enum('pending','completed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tasks`
--

INSERT INTO `tasks` (`task_id`, `organizer_id`, `title`, `description`, `due_date`, `status`, `created_at`, `updated_at`) VALUES
(1, 9, 'book', 'ftdgfjewhf;iuroig', '2025-04-30 23:11:00', 'pending', '2025-04-21 23:11:54', '2025-04-21 23:11:54');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('student','organizer','admin') DEFAULT 'student',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `first_name`, `last_name`, `email`, `password`, `role`, `created_at`, `updated_at`) VALUES
(1, 'Kojo', 'lem', 'Steven@gmail.com', '$2y$10$TSswi02aRziJH9N3ryAlquAZe43v4esdjeusWg3tsLGNqGv4D5anO', 'organizer', '2025-04-13 08:53:45', '2025-04-22 23:42:57'),
(2, 'Steve Nsabimana', 'Nsabimana', 'nsabimana.organizer@ashesi.edu.gh', '$2y$10$p9w7T9kTaNokyHVb2jV16eJXPpnRf2yU5Z4E2uV4DTGF0MpCxMRha', 'organizer', '2025-04-13 08:58:11', '2025-04-13 08:58:11'),
(3, 'Steve', 'Nsabimana', 'j.organizer@gmail.com', '$2y$10$9zSdCwlqw4TnB0iuibmJTOF5uLDf4afqoN2iMMvtjXvJQKHpzNUOW', 'organizer', '2025-04-15 08:55:37', '2025-04-15 08:55:37'),
(4, 'Steve', 'Nsabimana', 's.organizer@gmail.com', '$2y$10$JeMnfnXNLPTwUfjWHtJPL.rxOkxfaqNPKmb3mV4Gt/TyAgQUwpyl.', 'organizer', '2025-04-15 08:56:12', '2025-04-15 08:56:12'),
(5, 'Steve', 'Nsabimana', 'a.organizer@gmail.com', '$2y$10$xXt8W7kc/VTbPCCUJgvOm.1r7qQY50S0XBKXutn4J/RozJouib47W', 'organizer', '2025-04-15 08:56:40', '2025-04-15 08:56:40'),
(6, 'Steve Nsabimana', 'Nsabimana', 'ste.oganizer@gmail.com', '$2y$10$kxy9LZ4dQi6kFiPZg/bZ5.wHBRVu7rrYyq0QDos3d/7FMIEkR5FLm', 'student', '2025-04-15 08:57:41', '2025-04-15 08:57:41'),
(9, 'Rahinatu', 'Lawal', 'rahinatu.organizer@gmail.com', '$2y$10$rSLs93rE0UMupcXAzveubeNOl5PNRsL6ojvviYiFQtf54sRrxaOL2', 'organizer', '2025-04-21 23:08:12', '2025-04-22 23:44:30'),
(11, 'stevem', 'Nsabimana', 'rahinatu.admin@gmail.com', '$2y$10$mPlkbUQ4H4Df2T7WlBV4VuZSzgHk7MA4aoukUpuaRjFlzsigpSTPe', 'admin', '2025-04-22 00:19:49', '2025-04-22 23:42:06'),
(12, 'Kojo', 'Lme', 'lem@gmail.com', '$2y$10$5KJwDHxFu2daXwrCWxt37.8NVnY8m7lDbRpyr5S4nTmJiGRHvwn0m', 'admin', '2025-04-22 23:45:51', '2025-04-22 23:45:51');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `adminpanel`
--
ALTER TABLE `adminpanel`
  ADD PRIMARY KEY (`admin_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `eventanalytics`
--
ALTER TABLE `eventanalytics`
  ADD PRIMARY KEY (`analytics_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `eventcalendar`
--
ALTER TABLE `eventcalendar`
  ADD PRIMARY KEY (`calendar_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`event_id`),
  ADD KEY `organizer_id` (`organizer_id`);

--
-- Indexes for table `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`favorite_id`),
  ADD UNIQUE KEY `unique_favorite` (`user_id`,`event_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`feedback_id`),
  ADD KEY `event_id` (`event_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `organizer_id` (`organizer_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `registrations`
--
ALTER TABLE `registrations`
  ADD PRIMARY KEY (`registration_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`task_id`),
  ADD KEY `organizer_id` (`organizer_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `adminpanel`
--
ALTER TABLE `adminpanel`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `eventanalytics`
--
ALTER TABLE `eventanalytics`
  MODIFY `analytics_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `eventcalendar`
--
ALTER TABLE `eventcalendar`
  MODIFY `calendar_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=149;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `favorites`
--
ALTER TABLE `favorites`
  MODIFY `favorite_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=147;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `feedback_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `registrations`
--
ALTER TABLE `registrations`
  MODIFY `registration_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=117;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `task_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `adminpanel`
--
ALTER TABLE `adminpanel`
  ADD CONSTRAINT `adminpanel_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `eventanalytics`
--
ALTER TABLE `eventanalytics`
  ADD CONSTRAINT `eventanalytics_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`) ON DELETE CASCADE;

--
-- Constraints for table `eventcalendar`
--
ALTER TABLE `eventcalendar`
  ADD CONSTRAINT `eventcalendar_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `eventcalendar_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`) ON DELETE CASCADE;

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY (`organizer_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `favorites_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`) ON DELETE CASCADE;

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `feedback_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`organizer_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`);

--
-- Constraints for table `registrations`
--
ALTER TABLE `registrations`
  ADD CONSTRAINT `registrations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `registrations_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`) ON DELETE CASCADE;

--
-- Constraints for table `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`organizer_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
