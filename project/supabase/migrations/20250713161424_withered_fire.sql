-- BBDMS (Blood Bank Donor Management System) Database Schema
-- Import this file into phpMyAdmin

CREATE DATABASE IF NOT EXISTS bbdms;
USE bbdms;

-- Users table (for all user types)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    user_type ENUM('admin', 'donor', 'seeker') NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(15),
    address TEXT,
    gender ENUM('Male', 'Female', 'Other'),
    date_of_birth DATE,
    blood_group ENUM('A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'),
    status ENUM('active', 'inactive', 'pending') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Blood requests table
CREATE TABLE blood_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    seeker_id INT NOT NULL,
    blood_group ENUM('A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-') NOT NULL,
    units_needed INT NOT NULL,
    urgency ENUM('Low', 'Medium', 'High', 'Critical') NOT NULL,
    hospital_name VARCHAR(100) NOT NULL,
    hospital_address TEXT NOT NULL,
    contact_person VARCHAR(100) NOT NULL,
    contact_phone VARCHAR(15) NOT NULL,
    needed_date DATE NOT NULL,
    description TEXT,
    status ENUM('pending', 'fulfilled', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (seeker_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Messages table
CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    subject VARCHAR(200),
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Contact queries table
CREATE TABLE contact_queries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(15),
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('pending', 'resolved') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Site settings table
CREATE TABLE site_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_name VARCHAR(50) UNIQUE NOT NULL,
    setting_value TEXT NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default admin user
INSERT INTO users (username, email, password, user_type, full_name, phone, address, gender, blood_group, status) 
VALUES ('admin', 'admin@bbdms.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'System Administrator', '1234567890', 'Admin Office', 'Male', 'O+', 'active');

-- Insert sample donors
INSERT INTO users (username, email, password, user_type, full_name, phone, address, gender, date_of_birth, blood_group, status) VALUES
('john_doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'donor', 'John Doe', '9876543210', '123 Main St, City', 'Male', '1990-05-15', 'A+', 'active'),
('jane_smith', 'jane@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'donor', 'Jane Smith', '9876543211', '456 Oak Ave, City', 'Female', '1988-08-22', 'B+', 'active'),
('mike_wilson', 'mike@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'donor', 'Mike Wilson', '9876543212', '789 Pine Rd, City', 'Male', '1992-12-10', 'O-', 'active'),
('sarah_brown', 'sarah@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'donor', 'Sarah Brown', '9876543213', '321 Elm St, City', 'Female', '1985-03-18', 'AB+', 'active');

-- Insert sample seekers
INSERT INTO users (username, email, password, user_type, full_name, phone, address, gender, date_of_birth, blood_group, status) VALUES
('alex_johnson', 'alex@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'seeker', 'Alex Johnson', '9876543214', '654 Maple Dr, City', 'Male', '1987-07-25', 'A-', 'active'),
('lisa_davis', 'lisa@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'seeker', 'Lisa Davis', '9876543215', '987 Cedar Ln, City', 'Female', '1991-11-30', 'B-', 'active');

-- Insert sample blood requests
INSERT INTO blood_requests (seeker_id, blood_group, units_needed, urgency, hospital_name, hospital_address, contact_person, contact_phone, needed_date, description) VALUES
(5, 'A+', 2, 'High', 'City General Hospital', '123 Hospital St, City', 'Dr. Smith', '1234567890', '2024-02-15', 'Emergency surgery required'),
(6, 'O-', 1, 'Medium', 'Metro Medical Center', '456 Medical Ave, City', 'Dr. Johnson', '1234567891', '2024-02-20', 'Routine transfusion needed');

-- Insert site settings
INSERT INTO site_settings (setting_name, setting_value) VALUES
('site_name', 'BBDMS - Blood Bank Donor Management System'),
('contact_email', 'contact@bbdms.com'),
('contact_phone', '+1-234-567-8900'),
('contact_address', '123 Blood Bank Street, Medical District, City 12345'),
('about_us', 'BBDMS is a comprehensive blood bank management system designed to connect blood donors with those in need. Our mission is to save lives by facilitating safe and efficient blood donation processes.');