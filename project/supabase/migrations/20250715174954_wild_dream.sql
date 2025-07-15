-- BBDMS (Blood Bank Donor Management System) Database Schema
-- Complete working database with all tables and sample data

DROP DATABASE IF EXISTS bbdms;
CREATE DATABASE bbdms;
USE bbdms;

-- Users table (for all user types: admin, donor, seeker)
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
    units_needed INT NOT NULL DEFAULT 1,
    urgency ENUM('Low', 'Medium', 'High', 'Critical') NOT NULL DEFAULT 'Medium',
    hospital_name VARCHAR(100) NOT NULL,
    hospital_address TEXT NOT NULL,
    contact_person VARCHAR(100) NOT NULL,
    contact_phone VARCHAR(15) NOT NULL,
    needed_date DATE NOT NULL,
    description TEXT,
    status ENUM('pending', 'fulfilled', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (seeker_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Messages table for basic messaging system
CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    subject VARCHAR(200) NOT NULL,
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
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Site settings table
CREATE TABLE site_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_name VARCHAR(50) UNIQUE NOT NULL,
    setting_value TEXT NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default admin user (password: admin123)
INSERT INTO users (username, email, password, user_type, full_name, phone, address, gender, blood_group, status) 
VALUES ('admin', 'admin@bbdms.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'System Administrator', '1234567890', 'Admin Office, Medical District', 'Male', 'O+', 'active');

-- Insert sample donors (password: password)
INSERT INTO users (username, email, password, user_type, full_name, phone, address, gender, date_of_birth, blood_group, status) VALUES
('john_doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'donor', 'John Doe', '9876543210', '123 Main Street, Downtown', 'Male', '1990-05-15', 'A+', 'active'),
('jane_smith', 'jane@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'donor', 'Jane Smith', '9876543211', '456 Oak Avenue, Midtown', 'Female', '1988-08-22', 'B+', 'active'),
('mike_wilson', 'mike@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'donor', 'Mike Wilson', '9876543212', '789 Pine Road, Uptown', 'Male', '1992-12-10', 'O-', 'active'),
('sarah_brown', 'sarah@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'donor', 'Sarah Brown', '9876543213', '321 Elm Street, Westside', 'Female', '1985-03-18', 'AB+', 'active'),
('david_jones', 'david@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'donor', 'David Jones', '9876543214', '654 Maple Drive, Eastside', 'Male', '1987-11-30', 'A-', 'active'),
('lisa_garcia', 'lisa@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'donor', 'Lisa Garcia', '9876543215', '987 Cedar Lane, Northside', 'Female', '1991-07-25', 'B-', 'active');

-- Insert sample seekers (password: password)
INSERT INTO users (username, email, password, user_type, full_name, phone, address, gender, date_of_birth, blood_group, status) VALUES
('alex_johnson', 'alex@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'seeker', 'Alex Johnson', '9876543216', '111 First Street, Central', 'Male', '1987-07-25', 'A-', 'active'),
('maria_davis', 'maria@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'seeker', 'Maria Davis', '9876543217', '222 Second Avenue, South', 'Female', '1991-11-30', 'B-', 'active'),
('robert_miller', 'robert@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'seeker', 'Robert Miller', '9876543218', '333 Third Boulevard, North', 'Male', '1989-04-12', 'O+', 'active');

-- Insert sample blood requests
INSERT INTO blood_requests (seeker_id, blood_group, units_needed, urgency, hospital_name, hospital_address, contact_person, contact_phone, needed_date, description) VALUES
(7, 'A+', 2, 'High', 'City General Hospital', '123 Hospital Street, Medical District', 'Dr. Smith', '1234567890', '2024-02-15', 'Emergency surgery required for patient'),
(8, 'O-', 1, 'Critical', 'Metro Medical Center', '456 Medical Avenue, Health District', 'Dr. Johnson', '1234567891', '2024-02-12', 'Urgent transfusion needed'),
(9, 'B+', 3, 'Medium', 'Regional Blood Center', '789 Care Street, Medical Plaza', 'Dr. Williams', '1234567892', '2024-02-20', 'Scheduled surgery preparation');

-- Insert sample messages to test the messaging system
INSERT INTO messages (sender_id, receiver_id, subject, message) VALUES
(7, 2, 'Blood Donation Request', 'Hello John, I urgently need A+ blood for my surgery. Can you help?'),
(2, 7, 'Re: Blood Donation Request', 'Hi Alex, I would be happy to help. Please let me know the hospital details.'),
(8, 4, 'Emergency Blood Need', 'Hi Sarah, I need AB+ blood urgently. Are you available to donate?'),
(9, 3, 'Blood Request for Surgery', 'Hi Jane, I need B+ blood for an upcoming surgery. Would you be able to help?'),
(7, 5, 'Urgent A- Blood Needed', 'Hello David, I need A- blood urgently. Please let me know if you can donate.');

-- Insert site settings
INSERT INTO site_settings (setting_name, setting_value) VALUES
('site_name', 'BBDMS - Blood Bank Donor Management System'),
('site_description', 'Connecting hearts, saving lives through blood donation'),
('contact_email', 'contact@bbdms.com'),
('emergency_phone', '+1-234-567-8900'),
('contact_phone', '+1-234-567-8901'),
('contact_address', '123 Blood Bank Street, Medical District, City 12345'),
('office_hours', 'Mon-Fri: 8:00 AM - 8:00 PM, Sat-Sun: 9:00 AM - 6:00 PM'),
('about_us', 'BBDMS is a comprehensive blood bank management system designed to connect blood donors with those in need. Our mission is to save lives by facilitating safe and efficient blood donation processes.'),
('emergency_message', 'For life-threatening emergencies, always call 911 first, then contact our emergency hotline.');