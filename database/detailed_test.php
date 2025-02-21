<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../inc/config.php';

echo "Testing MySQL connection...\n\n";

echo "Configuration:\n";
echo "Host: " . DB_HOST . "\n";
echo "Database: " . DB_NAME . "\n";
echo "User: " . DB_USER . "\n";
echo "Charset: " . DB_CHARSET . "\n\n";

try {
    // Test basic connection first
    $dsn = "mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 3, // 3 seconds timeout
    ];
    
    echo "Attempting to connect to MySQL server...\n";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    echo "Successfully connected to MySQL server!\n\n";
    
    // Test if database exists
    echo "Checking if database exists...\n";
    $stmt = $pdo->query("SHOW DATABASES LIKE '" . DB_NAME . "'");
    $exists = $stmt->fetch();
    
    if ($exists) {
        echo "Database '" . DB_NAME . "' exists!\n";
        
        // Try to select the database
        $pdo->query("USE " . DB_NAME);
        echo "Successfully connected to database!\n\n";
        
        // Test query
        $result = $pdo->query("SELECT NOW() as time");
        $row = $result->fetch();
        echo "Current server time: " . $row['time'] . "\n";
    } else {
        echo "Database '" . DB_NAME . "' does not exist!\n";
        echo "Creating database...\n";
        $pdo->exec("CREATE DATABASE " . DB_NAME);
        echo "Database created successfully!\n";
    }
    
} catch (PDOException $e) {
    echo "Connection failed!\n";
    echo "Error code: " . $e->getCode() . "\n";
    echo "Error message: " . $e->getMessage() . "\n\n";
    
    // Additional diagnostics
    echo "Diagnostic Information:\n";
    if ($e->getCode() == 2002) {
        echo "Error 2002 indicates a network connectivity issue.\n";
        echo "Please check:\n";
        echo "1. Security groups are properly configured\n";
        echo "2. EC2 and RDS are in the same VPC\n";
        echo "3. RDS endpoint is correct\n";
        echo "4. Port 3306 is open in the security group\n";
    } elseif ($e->getCode() == 1045) {
        echo "Error 1045 indicates invalid credentials.\n";
        echo "Please verify username and password.\n";
    } elseif ($e->getCode() == 1049) {
        echo "Error 1049 indicates database does not exist.\n";
    }
    
    // Try to ping the host
    echo "\nAttempting to ping " . DB_HOST . "...\n";
    system("ping -c 3 " . DB_HOST);
    
    // Try netcat
    echo "\nChecking port 3306 connectivity...\n";
    system("nc -zv " . DB_HOST . " 3306 2>&1");
} 