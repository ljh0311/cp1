<?php
class Database {
    private static $instance = null;
    private $conn;
    
    private function __construct() {
        $config = parse_ini_file('/var/www/private/db-config.ini');
        if (!$config) {
            throw new Exception("Failed to read database config file.");
        }
        
        $this->conn = new mysqli(
            $config['servername'],
            $config['username'],
            $config['password'],
            $config['dbname']
        );
        
        if ($this->conn->connect_error) {
            throw new Exception("Connection failed: " . $this->conn->connect_error);
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->conn;
    }
    
    public function prepare($query) {
        return $this->conn->prepare($query);
    }
    
    public function beginTransaction() {
        return $this->conn->begin_transaction();
    }
    
    public function commit() {
        return $this->conn->commit();
    }
    
    public function rollback() {
        return $this->conn->rollback();
    }
    
    public function close() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
} 