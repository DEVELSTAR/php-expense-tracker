-- Drop all existing tables to start fresh
DROP TABLE IF EXISTS expenses;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;

-- Create users table for authentication
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create categories table
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    user_id INT DEFAULT NULL, -- NULL for guest/default categories
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_category_user (name, user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create expenses table
CREATE TABLE expenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    expense_date DATE,
    total DECIMAL(10,2),
    category VARCHAR(50),
    message TEXT DEFAULT NULL,
    user_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Insert default categories for guest users (user_id = NULL)
INSERT INTO categories (name, user_id) VALUES 
('Food', NULL),
('Transportation', NULL),
('Entertainment', NULL),
('Shopping', NULL),
('Bills', NULL),
('Healthcare', NULL),
('Education', NULL),
('Other', NULL);

-- Create a default guest user (optional - for reference)
INSERT INTO users (username, password_hash) VALUES 
('guest', '$2y$10$abcdefghijklmnopqrstuvwxyz1234567890');

-- Note: Guest users will have user_id = NULL in expenses table
-- Logged-in users will have their actual user_id
