<?php
class DatabaseManager {
    private static $instance = null;
    private $conn = null;
    private $isUsingFallback = false;
    private $dbType = '';

    private function __construct() {
        try {
            // Try MySQL first
            require_once 'dbConn.php';
            $this->conn = getDbConnection();
            $this->dbType = 'mysql';
        } catch (Exception $e) {
            // Fallback to SQLite
            try {
                $this->initializeSQLite();
                $this->dbType = 'sqlite';
                $this->isUsingFallback = true;
            } catch (Exception $e) {
                throw new Exception("Both MySQL and SQLite connections failed: " . $e->getMessage());
            }
        }
    }

    private function initializeSQLite() {
        $dbPath = __DIR__ . '/fallback.db';
        $this->conn = new SQLite3($dbPath);
        
        // Create tables if they don't exist
        $this->createFallbackTables();
        
        // Insert demo data if tables are empty
        $this->insertDemoData();
    }

    private function createFallbackTables() {
        // Books table
        $this->conn->exec("CREATE TABLE IF NOT EXISTS books (
            book_id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            author TEXT NOT NULL,
            price REAL NOT NULL,
            image_url TEXT,
            description TEXT,
            featured INTEGER DEFAULT 0,
            category TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        // Users table
        $this->conn->exec("CREATE TABLE IF NOT EXISTS users (
            user_id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL UNIQUE,
            email TEXT NOT NULL UNIQUE,
            password TEXT NOT NULL,
            first_name TEXT,
            last_name TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        // Orders table
        $this->conn->exec("CREATE TABLE IF NOT EXISTS orders (
            order_id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER,
            total_amount REAL NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
    }

    private function insertDemoData() {
        // Check if books table is empty
        $result = $this->conn->query("SELECT COUNT(*) as count FROM books");
        $row = $result->fetchArray(SQLITE3_ASSOC);
        
        if ($row['count'] == 0) {
            // Insert demo books
            $demoBooks = require __DIR__ . '/../inc/default_data.php';
            $books = DefaultData::getFeaturedBooks();
            
            foreach ($books as $book) {
                $stmt = $this->conn->prepare("
                    INSERT INTO books (title, author, price, image_url, description, featured, category)
                    VALUES (:title, :author, :price, :image_url, :description, :featured, :category)
                ");
                
                $stmt->bindValue(':title', $book['title'], SQLITE3_TEXT);
                $stmt->bindValue(':author', $book['author'], SQLITE3_TEXT);
                $stmt->bindValue(':price', $book['price'], SQLITE3_FLOAT);
                $stmt->bindValue(':image_url', $book['image_url'], SQLITE3_TEXT);
                $stmt->bindValue(':description', $book['description'], SQLITE3_TEXT);
                $stmt->bindValue(':featured', $book['featured'], SQLITE3_INTEGER);
                $stmt->bindValue(':category', $book['category'], SQLITE3_TEXT);
                
                $stmt->execute();
            }
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

    public function isUsingFallback() {
        return $this->isUsingFallback;
    }

    public function query($sql, $params = []) {
        try {
            if ($this->dbType === 'mysql') {
                $stmt = $this->conn->prepare($sql);
                if ($params) {
                    $stmt->execute($params);
                } else {
                    $stmt->execute();
                }
                return $stmt;
            } else {
                $stmt = $this->conn->prepare($sql);
                foreach ($params as $key => $value) {
                    $stmt->bindValue($key, $value);
                }
                return $stmt->execute();
            }
        } catch (Exception $e) {
            error_log("Query failed: " . $e->getMessage());
            throw $e;
        }
    }

    public function fetchAll($result) {
        $rows = [];
        if ($this->dbType === 'mysql') {
            return $result->fetchAll(PDO::FETCH_ASSOC);
        } else {
            while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                $rows[] = $row;
            }
            return $rows;
        }
    }

    public function fetch($result) {
        if ($this->dbType === 'mysql') {
            return $result->fetch(PDO::FETCH_ASSOC);
        } else {
            return $result->fetchArray(SQLITE3_ASSOC);
        }
    }
} 