<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'inc/dbConfig.php';

try {
    echo "Testing database connection...<br>";
    
    $db = Database::getInstance();
    echo "Database instance created.<br>";
    
    $result = $db->safeQuery("SELECT 1");
    echo "Query executed.<br>";
    
    if ($result) {
        echo "Database connection successful!<br>";
        
        // Test tables
        $tables = $db->safeQuery("SHOW TABLES");
        echo "Tables in database:<br>";
        while ($row = $tables->fetch()) {
            print_r($row);
            echo "<br>";
        }
        
        // Test books table
        $books = $db->safeQuery("SELECT * FROM books LIMIT 1");
        if ($books) {
            echo "Sample book:<br>";
            print_r($books->fetch());
        }
    } else {
        echo "Database connection failed!<br>";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . "<br>";
    echo "Line: " . $e->getLine() . "<br>";
    echo "Trace:<br>" . $e->getTraceAsString();
} 