-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS campus_lost_found;
USE campus_lost_found;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    is_admin TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Found items table with status column
CREATE TABLE IF NOT EXISTS found_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    category VARCHAR(50) NOT NULL,
    location VARCHAR(255) NOT NULL,
    found_datetime DATETIME NOT NULL,
    description TEXT NOT NULL,
    image_path VARCHAR(500),
    status ENUM('active', 'claimed', 'removed') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Password resets table
CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_email (email)
);

-- Insert sample data
INSERT IGNORE INTO users (username, email, phone, password, is_admin) VALUES
('admin', 'admin@campus.edu', '+1234567890', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1),
('john_doe', 'john@campus.edu', '+1234567891', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 0),
('jane_smith', 'jane@campus.edu', '+1234567892', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 0);

-- Insert sample found items
INSERT IGNORE INTO found_items (user_id, title, category, location, found_datetime, description, status) VALUES
(2, 'iPhone 13 Pro - Blue', 'Electronics', 'Library - 2nd Floor', '2024-01-15 14:30:00', 'Found a blue iPhone 13 Pro with a clear case. Screen has a small crack on the top right corner. Phone was found on a study table.', 'active'),
(3, 'Black Leather Wallet', 'Accessories', 'Student Center', '2024-01-16 10:15:00', 'Black leather wallet found near the food court. Contains some cards but no cash. Has initials "M.R." embossed on the front.', 'active'),
(2, 'Red Backpack', 'Accessories', 'Engineering Building', '2024-01-17 16:45:00', 'Large red backpack with laptop compartment. Contains textbooks and notebooks. Found in classroom 201.', 'active'),
(3, 'Silver MacBook Air', 'Electronics', 'Computer Lab', '2024-01-18 12:00:00', 'Silver MacBook Air 13-inch. Has several stickers on the lid. Found left on a desk in the computer lab.', 'active'),
(2, 'Car Keys with Toyota Keychain', 'Keys', 'Parking Lot B', '2024-01-19 08:30:00', 'Set of car keys with Toyota keychain and a small flashlight attached. Found near the entrance of parking lot B.', 'active');
