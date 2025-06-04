-- database setup
-- Warehouse Management System Database Setup
-- Save this as: database/setup.sql

CREATE DATABASE IF NOT EXISTS warehouse_system;
USE warehouse_system;

-- Roles table
CREATE TABLE roles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    role_name VARCHAR(50) NOT NULL UNIQUE,
    permissions TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id)
);

-- Products table
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    category VARCHAR(50),
    price DECIMAL(10,2),
    quantity INT DEFAULT 0,
    company_id VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Orders table
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    status VARCHAR(20) DEFAULT 'pending',
    total_amount DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Shelves table
CREATE TABLE shelves (
    id INT PRIMARY KEY AUTO_INCREMENT,
    location VARCHAR(50) NOT NULL,
    capacity INT DEFAULT 100,
    current_items INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default roles
INSERT INTO roles (role_name, permissions) VALUES 
('Administrator', 'all'),
('Noliktavas darbinieks', 'products,orders,reports'),
('Plauktu kārtotājs', 'shelves,products_placement,reports');

-- Insert default admin user (password: admin123)
INSERT INTO users (username, password, role_id) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1);
