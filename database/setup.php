<?php
require_once 'inc/dbConfig.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // Create database if it doesn't exist
    $db->exec("CREATE DATABASE IF NOT EXISTS bookstore");
    $db->exec("USE bookstore");
    
    // Read and execute the schema
    $schema = file_get_contents(__DIR__ . '/database/schema.sql');
    $db->exec($schema);
    
    // Insert some sample data
    $db->exec("
        INSERT INTO users (first_name, last_name, email, password_hash, role) 
        VALUES ('Admin', 'User', 'admin@example.com', '" . password_hash('admin123', PASSWORD_DEFAULT) . "', 'admin')
    ");
    
    echo json_encode([
        'success' => true,
        'message' => 'Database initialized successfully'
    ]);
} catch (Exception $e) {
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
} 