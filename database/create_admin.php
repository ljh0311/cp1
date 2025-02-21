<?php
require_once '/var/www/html/inc/config.php';
require_once '/var/www/html/database/DatabaseManager.php';

try {
    $db = DatabaseManager::getInstance();
    
    // Check if admin user exists
    $check_query = "SELECT user_id FROM users WHERE username = 'admin'";
    $result = $db->query($check_query);
    $admin = $db->fetch($result);
    
    if (!$admin) {
        // Create admin user if doesn't exist
        $password = 'Admin123!';
        $hash = password_hash($password, PASSWORD_DEFAULT);
        
        $insert_query = "INSERT INTO users (username, email, password_hash, full_name, is_admin, status) 
                        VALUES (:username, :email, :password_hash, :full_name, :is_admin, :status)";
        
        $db->query($insert_query, [
            ':username' => 'admin',
            ':email' => 'admin@example.com',
            ':password_hash' => $hash,
            ':full_name' => 'Admin User',
            ':is_admin' => true,
            ':status' => 'active'
        ]);
        
        echo "Admin user created successfully!\n";
    } else {
        // Update admin password if user exists
        $password = 'Admin123!';
        $hash = password_hash($password, PASSWORD_DEFAULT);
        
        $update_query = "UPDATE users SET password_hash = :password_hash WHERE username = 'admin'";
        $db->query($update_query, [':password_hash' => $hash]);
        
        echo "Admin password updated successfully!\n";
    }
    
    echo "\nYou can now log in with:\n";
    echo "Username: admin\n";
    echo "Password: Admin123!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} 