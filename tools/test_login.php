<?php
require_once 'inc/config.php';
require_once 'database/DatabaseManager.php';

try {
    $db = DatabaseManager::getInstance();
    
    // 1. Check if users table exists and its structure
    echo "Checking database structure:\n";
    $stmt = $db->query("DESCRIBE users");
    $columns = $db->fetchAll($stmt);
    echo "Users table structure:\n";
    print_r($columns);
    
    // 2. Check for any users in the database
    echo "\nChecking existing users:\n";
    $stmt = $db->query("SELECT user_id, username, email, status FROM users");
    $users = $db->fetchAll($stmt);
    echo "Users in database:\n";
    print_r($users);
    
    // 3. Test password verification for a specific user
    echo "\nEnter a username to test password verification:\n";
    $test_username = isset($_GET['username']) ? $_GET['username'] : '';
    if ($test_username) {
        $stmt = $db->query("SELECT user_id, username, password_hash, status FROM users WHERE username = :username",
                          [':username' => $test_username]);
        $user = $db->fetch($stmt);
        
        if ($user) {
            echo "User found:\n";
            echo "Username: " . $user['username'] . "\n";
            echo "Status: " . $user['status'] . "\n";
            echo "Password hash exists: " . (!empty($user['password_hash']) ? 'Yes' : 'No') . "\n";
        } else {
            echo "User not found\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} 