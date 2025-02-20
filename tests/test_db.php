<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    require_once 'inc/dbConfig.php';
    
    echo "Attempting to connect to database...<br>";
    
    $db = Database::getInstance();
    
    echo "Database instance created successfully!<br>";
    
    $result = $db->safeQuery("SELECT 1");
    
    if ($result) {
        echo "Database query successful!<br>";
    }
    
} catch (Exception $e) {
    echo "Error occurred:<br>";
    echo "Message: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . "<br>";
    echo "Line: " . $e->getLine() . "<br>";
} 