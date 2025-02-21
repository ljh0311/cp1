<?php
require_once '../inc/config.php';
require_once '../database/DatabaseManager.php';

try {
    $db = DatabaseManager::getInstance();
    
    // Test the connection
    $result = $db->query("SELECT NOW() as server_time");
    $row = $db->fetch($result);
    
    echo "Successfully connected to RDS!\n";
    echo "Server time: " . $row['server_time'] . "\n";
    
    // Test if tables exist
    $tables = [
        'users',
        'books',
        'categories',
        'cart_items',
        'orders',
        'order_items',
        'remember_tokens'
    ];
    
    echo "\nChecking tables:\n";
    foreach ($tables as $table) {
        try {
            $result = $db->query("SELECT COUNT(*) as count FROM $table");
            $row = $db->fetch($result);
            echo "$table: " . $row['count'] . " records\n";
        } catch (Exception $e) {
            echo "$table: Table not found or error\n";
        }
    }
    
} catch (Exception $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
} 