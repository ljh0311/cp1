<?php
require_once '../inc/config.php';
require_once '../database/DatabaseManager.php';

try {
    $db = DatabaseManager::getInstance();
    
    // Create tables
    $schema_files = [
        __DIR__ . '/schema.mysql.sql',
        __DIR__ . '/remember_tokens.sql'
    ];
    
    foreach ($schema_files as $file) {
        $sql = file_get_contents($file);
        
        // Split SQL into individual statements
        $statements = array_filter(
            array_map('trim', 
                explode(';', str_replace('DELIMITER //', '', str_replace('DELIMITER ;', '', $sql)))
            )
        );
        
        foreach ($statements as $statement) {
            if (!empty($statement)) {
                try {
                    $db->query($statement);
                    echo "Executed: " . substr($statement, 0, 50) . "...\n";
                } catch (Exception $e) {
                    echo "Error executing: " . substr($statement, 0, 50) . "...\n";
                    echo "Error message: " . $e->getMessage() . "\n\n";
                }
            }
        }
    }
    
    echo "\nDatabase initialization completed!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} 