<?php
// inc/dbConfig.php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/ErrorHandler.php';

// Set to true during development, false in production
define('DEBUG_MODE', true);

class Database
{
    private static $instance = null;
    private $conn;

    private function __construct()
    {
        try {
            // First connect without specifying a database
            $this->conn = new PDO(
                "mysql:host=database1.czsa24cac7y5.us-east-1.rds.amazonaws.com;charset=utf8mb4",
                "admin",
                "KappyAdmin",
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );

            // Create database if it doesn't exist
            $this->conn->exec("CREATE DATABASE IF NOT EXISTS bookstore");
            $this->conn->exec("USE bookstore");

            ErrorHandler::setDbStatus(true);
        } catch (PDOException $e) {
            ErrorHandler::setDbStatus(false);
            ErrorHandler::handleException($e);
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function safeQuery($query, $params = [])
    {
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            ErrorHandler::handleException($e);
            return false;
        }
    }
}

// Set error handlers
set_exception_handler([ErrorHandler::class, 'handleException']);
set_error_handler([ErrorHandler::class, 'handleError']);

// Initialize database connection
try {
    $db = Database::getInstance();
} catch (Exception $e) {
    ErrorHandler::handleException($e);
}
