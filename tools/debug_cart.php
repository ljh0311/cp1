<?php
require_once 'inc/config.php';
require_once 'inc/session_config.php';
require_once 'database/DatabaseManager.php';
require_once 'inc/SessionManager.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Cart Debug Information</h2>";

try {
    $db = DatabaseManager::getInstance();
    $sessionManager = SessionManager::getInstance();
    
    // Get user ID
    $user_id = $sessionManager->getUserId();
    echo "<p>Current User ID: " . $user_id . "</p>";
    
    // Check cart items directly from database
    $cart_query = $db->query(
        "SELECT ci.*, b.title, b.price 
         FROM cart_items ci 
         JOIN books b ON ci.book_id = b.book_id 
         WHERE ci.user_id = ?",
        [$user_id]
    );
    
    $cart_items = $db->fetchAll($cart_query);
    
    echo "<h3>Cart Items in Database:</h3>";
    echo "<pre>";
    print_r($cart_items);
    echo "</pre>";
    
    // Check session cart data
    echo "<h3>Session Data:</h3>";
    echo "<pre>";
    print_r($_SESSION);
    echo "</pre>";
    
} catch (Exception $e) {
    echo "<h3>Error:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?> 