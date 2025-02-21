<?php
require_once '/var/www/html/inc/config.php';
require_once '/var/www/html/database/DatabaseManager.php';

try {
    $db = DatabaseManager::getInstance();
    
    // Create the correct password hash
    $password = 'Admin123!';
    $hash = password_hash($password, PASSWORD_DEFAULT);
    
    // Update the admin user's password
    $db->query(
        "UPDATE users SET password_hash = :hash WHERE username = :username",
        [':hash' => $hash, ':username' => 'admin']
    );
    
    echo "Admin password has been reset successfully!\n";
    echo "You can now log in with:\n";
    echo "Username: admin\n";
    echo "Password: Admin123!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} 