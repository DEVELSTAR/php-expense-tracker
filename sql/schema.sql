-- Create expenses table
CREATE TABLE expenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    expense_date DATE,
    total DECIMAL(10,2),
    category VARCHAR(50),
    message TEXT DEFAULT NULL,
    user_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create users table for authentication
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Add user_id column to expenses table (for existing tables)
ALTER TABLE expenses ADD COLUMN user_id INT DEFAULT NULL;

-- Add foreign key constraint (optional, for data integrity)
ALTER TABLE expenses ADD CONSTRAINT fk_expenses_user 
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL;

-- Create categories table
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    user_id INT DEFAULT NULL, -- NULL for guest/default categories
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_category_user (name, user_id)
);

-- Create a default guest user (optional)
INSERT INTO users (username, password_hash) VALUES 
('guest', '$2y$10$abcdefghijklmnopqrstuvwxyz1234567890');

-- Insert default categories for guest users
INSERT INTO categories (name, user_id) VALUES 
('Food', NULL),
('Transportation', NULL),
('Entertainment', NULL),
('Shopping', NULL),
('Bills', NULL),
('Healthcare', NULL),
('Education', NULL),
('Other', NULL);
