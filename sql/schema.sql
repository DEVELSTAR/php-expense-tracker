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
 
-- Create categories table (simplified, no foreign key constraints)
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    user_id INT DEFAULT NULL, -- NULL for default categories, user ID for custom categories
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_category_user (name, user_id)
);
 
-- Create expenses table (simplified, no foreign key constraints)
CREATE TABLE expenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    expense_date DATE,
    total DECIMAL(10,2),
    category VARCHAR(50),
    message TEXT DEFAULT NULL,
    user_id INT DEFAULT NULL, -- NULL for guest users, user ID for logged-in users
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
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
 
-- Note: 
-- - No foreign key constraints to avoid NULL value issues
-- - Categories with user_id = NULL are default categories for everyone
-- - Categories with user_id = [actual ID] are custom categories for that user
-- - Expenses with user_id = NULL are guest expenses
-- - Expenses with user_id = [actual ID] are user expenses
 