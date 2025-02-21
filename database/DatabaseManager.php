<?php
class DatabaseManager
{
    private static $instance = null;
    private $pdo = null;

    private function __construct()
    {
        try {
            // Ensure we're using the correct host from config
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            // Log connection attempt for debugging
            error_log("Attempting to connect to database at " . DB_HOST);
            
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            
            error_log("Successfully connected to database");
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            
            // Try to create database if it doesn't exist
            if ($e->getCode() == 1049) { // Unknown database
                try {
                    $dsn = "mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET;
                    $tempPdo = new PDO($dsn, DB_USER, DB_PASS, $options);
                    
                    // Create database
                    $tempPdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
                    
                    // Connect to the newly created database
                    $tempPdo->exec("USE " . DB_NAME);
                    $this->pdo = $tempPdo;
                    
                    // Initialize database schema
                    $this->initializeDatabase();
                    
                    error_log("Created and initialized new database");
                    return;
                } catch (PDOException $e2) {
                    error_log("Failed to create database: " . $e2->getMessage());
                }
            }
            throw new Exception("Database connection failed. Please try again later.");
        }
    }

    private function initializeDatabase()
    {
        // Create tables
        $schema = file_get_contents(__DIR__ . '/schema.mysql.sql');
        $statements = array_filter(array_map('trim', explode(';', $schema)));
        
        foreach ($statements as $statement) {
            if (!empty($statement)) {
                try {
                    $this->pdo->exec($statement);
                } catch (PDOException $e) {
                    error_log("Error executing schema statement: " . $e->getMessage());
                    // Continue with other statements even if one fails
                }
            }
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function query($sql, $params = [])
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Query failed: " . $e->getMessage());
            throw new Exception("Database query failed. Please try again later.");
        }
    }

    public function fetch($result)
    {
        return $result->fetch();
    }

    public function fetchAll($result)
    {
        return $result->fetchAll();
    }

    public function lastInsertId()
    {
        return $this->pdo->lastInsertId();
    }

    public function beginTransaction()
    {
        return $this->pdo->beginTransaction();
    }

    public function commit()
    {
        return $this->pdo->commit();
    }

    public function rollBack()
    {
        return $this->pdo->rollBack();
    }

    public function quote($value)
    {
        return $this->pdo->quote($value);
    }
}