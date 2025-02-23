<?php
// Define root path if not already defined
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', __DIR__);
}

require_once 'inc/config.php';
require_once 'database/DatabaseManager.php';

try {
    $db = DatabaseManager::getInstance();
    
    // Update Database Design for Mere Mortals
    $db->query(
        "UPDATE books SET image_url = ? WHERE title = ?",
        ['images/books/database-design-mere-mortals.jpg', 'Database Design for Mere Mortals']
    );
    
    // Update PHP & MySQL Web Development
    $db->query(
        "UPDATE books SET image_url = ? WHERE title = ?",
        ['images/books/40540.jpg', 'PHP & MySQL Web Development']
    );
    
    // Update Python Crash Course
    $db->query(
        "UPDATE books SET image_url = ? WHERE title = ?",
        ['images/books/8ab85e06-664f-4780-9711-b362a62236fe.jpg', 'Python Crash Course']
    );
    
    echo "Successfully updated book image URLs.\n";
    
} catch (Exception $e) {
    echo "Error updating book image URLs: " . $e->getMessage() . "\n";
} 