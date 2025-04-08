<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'inc/config.php';

echo "Testing database connection...\n\n";
echo "Configuration:\n";
echo "Host: " . DB_HOST . "\n";
echo "Database: " . DB_NAME . "\n";
echo "User: " . DB_USER . "\n";

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 5
    ];
    
    echo "\nAttempting to connect to database...\n";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    echo "Successfully connected to database!\n";
    
    $result = $pdo->query("SELECT NOW() as time");
    $row = $result->fetch();
    echo "Current server time: " . $row['time'] . "\n";
    
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
    
    if ($e->getCode() == 1049) {
        echo "\nTrying to create database...\n";
        try {
            $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS, $options);
            $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
            echo "Database created successfully!\n";
        } catch (PDOException $e2) {
            echo "Failed to create database: " . $e2->getMessage() . "\n";
        }
    }
} 