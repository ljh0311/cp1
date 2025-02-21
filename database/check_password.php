<?php
require_once '/var/www/html/inc/config.php';
require_once '/var/www/html/database/DatabaseManager.php';

try {
    $db = DatabaseManager::getInstance();
    
    // Get the current admin user data
    $query = "SELECT username, password_hash FROM users WHERE username = 'admin'";
    $result = $db->query($query);
    $user = $db->fetch($result);
    
    if ($user) {
        echo "Current hash in database: " . $user['password_hash'] . "\n\n";
        
        // Test password verification
        $test_password = 'Admin123!';
        $verification_result = password_verify($test_password, $user['password_hash']);
        
        echo "Testing password verification:\n";
        echo "Password being tested: " . $test_password . "\n";
        echo "Verification result: " . ($verification_result ? "SUCCESS" : "FAILED") . "\n\n";
        
        // Create a new hash for comparison
        $new_hash = password_hash($test_password, PASSWORD_DEFAULT);
        echo "New hash generated: " . $new_hash . "\n";
        
        // Verify the new hash works
        echo "Verifying new hash: " . (password_verify($test_password, $new_hash) ? "SUCCESS" : "FAILED") . "\n";
    } else {
        echo "Admin user not found in database!\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} 