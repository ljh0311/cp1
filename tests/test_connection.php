<?php
require_once 'inc/dbConfig.php';

try {
    $db = Database::getInstance();
    $result = $db->safeQuery("SELECT 1");
    
    if ($result) {
        echo "Database connection successful!";
    } else {
        echo "Database connection failed!";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?> 