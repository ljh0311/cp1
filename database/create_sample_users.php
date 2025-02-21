<?php
require_once '../inc/config.php';
require_once '../database/DatabaseManager.php';

try {
    $db = DatabaseManager::getInstance();
    
    // Create sample users with proper password hashes
    $password = 'Password123!';
    $admin_hash = password_hash($password, PASSWORD_DEFAULT);
    $student_hash = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert admin user
    $admin_query = "INSERT INTO users (username, email, password_hash, full_name, is_admin, status) 
                    VALUES (:username, :email, :password_hash, :full_name, :is_admin, :status)";
    
    $db->query($admin_query, [
        ':username' => 'admin',
        ':email' => 'admin@example.com',
        ':password_hash' => $admin_hash,
        ':full_name' => 'Admin User',
        ':is_admin' => true,
        ':status' => 'active'
    ]);
    
    // Insert student user
    $student_query = "INSERT INTO users (username, email, password_hash, full_name, is_admin, status) 
                     VALUES (:username, :email, :password_hash, :full_name, :is_admin, :status)";
    
    $db->query($student_query, [
        ':username' => 'student',
        ':email' => 'student@example.com',
        ':password_hash' => $student_hash,
        ':full_name' => 'Student User',
        ':is_admin' => false,
        ':status' => 'active'
    ]);
    
    echo "Sample users created successfully!\n";
    echo "\nYou can now log in with these credentials:\n";
    echo "Admin User:\n";
    echo "Username: admin\n";
    echo "Email: admin@example.com\n";
    echo "Password: Password123!\n\n";
    echo "Student User:\n";
    echo "Username: student\n";
    echo "Email: student@example.com\n";
    echo "Password: Password123!\n";
    
} catch (Exception $e) {
    echo "Error creating sample users: " . $e->getMessage() . "\n";
} 