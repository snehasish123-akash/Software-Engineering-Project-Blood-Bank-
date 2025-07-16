-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 16, 2025 at 01:53 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bbdms`
--

-- --------------------------------------------------------

--
-- Table structure for table `blood_requests`
--

CREATE TABLE `blood_requests` (
  `id` int(11) NOT NULL,
  `seeker_id` int(11) NOT NULL,
  `blood_group` enum('A+','A-','B+','B-','AB+','AB-','O+','O-') NOT NULL,
  `units_needed` int(11) NOT NULL DEFAULT 1,
  `urgency` enum('Low','Medium','High','Critical') NOT NULL DEFAULT 'Medium',
  `hospital_name` varchar(100) NOT NULL,
  `hospital_address` text NOT NULL,
  `contact_person` varchar(100) NOT NULL,
  `contact_phone` varchar(15) NOT NULL,
  `needed_date` date NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('pending','fulfilled','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `blood_requests`
--

INSERT INTO `blood_requests` (`id`, `seeker_id`, `blood_group`, `units_needed`, `urgency`, `hospital_name`, `hospital_address`, `contact_person`, `contact_phone`, `needed_date`, `description`, `status`, `created_at`, `updated_at`) VALUES
(1, 7, 'A+', 2, 'High', 'City General Hospital', '123 Hospital Street, Medical District', 'Dr. Smith', '1234567890', '2024-02-15', 'Emergency surgery required for patient', 'pending', '2025-07-15 23:36:01', '2025-07-15 23:36:01'),
(2, 8, 'O-', 1, 'Critical', 'Metro Medical Center', '456 Medical Avenue, Health District', 'Dr. Johnson', '1234567891', '2024-02-12', 'Urgent transfusion needed', 'pending', '2025-07-15 23:36:01', '2025-07-15 23:36:01'),
(3, 9, 'B+', 3, 'Medium', 'Regional Blood Center', '789 Care Street, Medical Plaza', 'Dr. Williams', '1234567892', '2024-02-20', 'Scheduled surgery preparation', 'pending', '2025-07-15 23:36:01', '2025-07-15 23:36:01');

-- --------------------------------------------------------

--
-- Table structure for table `contact_queries`
--

CREATE TABLE `contact_queries` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `subject` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `status` enum('pending','resolved') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `subject` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `subject`, `message`, `is_read`, `created_at`) VALUES
(1, 7, 2, 'Blood Donation Request', 'Hello John, I urgently need A+ blood for my surgery. Can you help?', 0, '2025-07-15 23:36:01'),
(2, 2, 7, 'Re: Blood Donation Request', 'Hi Alex, I would be happy to help. Please let me know the hospital details.', 0, '2025-07-15 23:36:01'),
(3, 8, 4, 'Emergency Blood Need', 'Hi Sarah, I need AB+ blood urgently. Are you available to donate?', 0, '2025-07-15 23:36:01');

-- --------------------------------------------------------

--
-- Table structure for table `site_settings`
--

CREATE TABLE `site_settings` (
  `id` int(11) NOT NULL,
  `setting_name` varchar(50) NOT NULL,
  `setting_value` text NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `site_settings`
--

INSERT INTO `site_settings` (`id`, `setting_name`, `setting_value`, `updated_at`) VALUES
(1, 'site_name', 'BBDMS - Blood Bank Donor Management System', '2025-07-15 23:36:01'),
(2, 'site_description', 'Connecting hearts, saving lives through blood donation', '2025-07-15 23:36:01'),
(3, 'contact_email', 'contact@bbdms.com', '2025-07-15 23:36:01'),
(4, 'emergency_phone', '+1-234-567-8900', '2025-07-15 23:36:01'),
(5, 'contact_phone', '+1-234-567-8901', '2025-07-15 23:36:01'),
(6, 'contact_address', '123 Blood Bank Street, Medical District, City 12345', '2025-07-15 23:36:01'),
(7, 'office_hours', 'Mon-Fri: 8:00 AM - 8:00 PM, Sat-Sun: 9:00 AM - 6:00 PM', '2025-07-15 23:36:01'),
(8, 'about_us', 'BBDMS is a comprehensive blood bank management system designed to connect blood donors with those in need. Our mission is to save lives by facilitating safe and efficient blood donation processes.', '2025-07-15 23:36:01'),
(9, 'emergency_message', 'For life-threatening emergencies, always call 911 first, then contact our emergency hotline.', '2025-07-15 23:36:01');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `user_type` enum('admin','donor','seeker') NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `blood_group` enum('A+','A-','B+','B-','AB+','AB-','O+','O-') DEFAULT NULL,
  `status` enum('active','inactive','pending') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `user_type`, `full_name`, `phone`, `address`, `gender`, `date_of_birth`, `blood_group`, `status`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@bbdms.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'System Administrator', '1234567890', 'Admin Office, Medical District', 'Male', NULL, 'O+', 'active', '2025-07-15 23:36:01', '2025-07-15 23:36:01'),
(2, 'rana', 'rana@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'donor', 'rana', '9876543210', '123 Main Street, Downtown', 'Male', '1990-05-15', 'A+', 'active', '2025-07-15 23:36:01', '2025-07-15 23:45:32'),
(3, 'sourav', 'sourav@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'donor', 'Sourav', '9876543211', '456 Oak Avenue, Midtown', 'Male', '1988-08-22', 'B+', 'active', '2025-07-15 23:36:01', '2025-07-15 23:44:53'),
(4, 'akash', 'akash@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'donor', 'akash', '9876543212', '789 Pine Road, Uptown', 'Male', '1992-12-10', 'O-', 'active', '2025-07-15 23:36:01', '2025-07-15 23:46:00'),
(5, 'sarah_brown', 'sarah@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'donor', 'Sarah Brown', '9876543213', '321 Elm Street, Westside', 'Female', '1985-03-18', 'AB+', 'active', '2025-07-15 23:36:01', '2025-07-15 23:36:01'),
(6, 'david_jones', 'david@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'donor', 'David Jones', '9876543214', '654 Maple Drive, Eastside', 'Male', '1987-11-30', 'A-', 'active', '2025-07-15 23:36:01', '2025-07-15 23:36:01'),
(7, 'lisa_garcia', 'lisa@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'donor', 'Lisa Garcia', '9876543215', '987 Cedar Lane, Northside', 'Female', '1991-07-25', 'B-', 'active', '2025-07-15 23:36:01', '2025-07-15 23:36:01'),
(8, 'sourava', 'sourava@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'seeker', 'sourava', '9876543216', '111 First Street, Central', 'Male', '1987-07-25', 'A-', 'active', '2025-07-15 23:36:01', '2025-07-15 23:47:03'),
(9, 'ranaa', 'ranaa@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'seeker', 'ranaa', '9876543217', '222 Second Avenue, South', 'Male', '1991-11-30', 'B-', 'active', '2025-07-15 23:36:01', '2025-07-15 23:46:32'),
(10, 'akasha', 'akasha@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'seeker', 'akasha', '9876543218', '333 Third Boulevard, North', 'Male', '1989-04-12', 'O+', 'active', '2025-07-15 23:36:01', '2025-07-15 23:47:27');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `blood_requests`
--
ALTER TABLE `blood_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `seeker_id` (`seeker_id`);

--
-- Indexes for table `contact_queries`
--
ALTER TABLE `contact_queries`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Indexes for table `site_settings`
--
ALTER TABLE `site_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_name` (`setting_name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `blood_requests`
--
ALTER TABLE `blood_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `contact_queries`
--
ALTER TABLE `contact_queries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `site_settings`
--
ALTER TABLE `site_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `blood_requests`
--
ALTER TABLE `blood_requests`
  ADD CONSTRAINT `blood_requests_ibfk_1` FOREIGN KEY (`seeker_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
