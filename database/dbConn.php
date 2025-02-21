<?php
function getDbConnection() {
    try {
        // Database configuration
        $host = "database1.czsa24cac7y5.us-east-1.rds.amazonaws.com";
        $dbname = "tutoring_system";
        $username = "admin";
        $password = "KappyAdmin";

        // Create PDO connection
        $conn = new PDO(
            "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
            $username,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_FOUND_ROWS => true
            ]
        );

        return $conn;
    } catch (PDOException $e) {
        error_log("Database connection failed: " . $e->getMessage());
        throw new Exception("Failed to connect to database");
    }
}
?>