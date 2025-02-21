<?php
require_once '../inc/config.php';
require_once '../database/DatabaseManager.php';

try {
    $db = DatabaseManager::getInstance();
    
    // Create tables
    $queries = [
        "CREATE TABLE IF NOT EXISTS users (
            user_id INT PRIMARY KEY AUTO_INCREMENT,
            first_name VARCHAR(50) NOT NULL,
            last_name VARCHAR(50) NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            role ENUM('customer', 'admin') DEFAULT 'customer',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        
        "CREATE TABLE IF NOT EXISTS books (
            book_id INT PRIMARY KEY AUTO_INCREMENT,
            title VARCHAR(255) NOT NULL,
            author VARCHAR(100) NOT NULL,
            price DECIMAL(10,2) NOT NULL,
            stock INT NOT NULL DEFAULT 0,
            featured BOOLEAN DEFAULT FALSE,
            image_url VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        
        // Insert sample data
        "INSERT INTO books (title, author, price, stock, featured, image_url) VALUES
        ('Sample Book 1', 'Author 1', 29.99, 10, 1, 'images/books/placeholder.jpg'),
        ('Sample Book 2', 'Author 2', 24.99, 15, 1, 'images/books/placeholder.jpg')
        ON DUPLICATE KEY UPDATE title=title"
    ];
    
    foreach ($queries as $query) {
        $db->query($query);
    }
    
    echo "Database initialized successfully!";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
} 
