<?php
// models/Book.php

class Book {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAllBooks($limit = 10, $offset = 0) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM books 
                ORDER BY created_at DESC 
                LIMIT :limit OFFSET :offset
            ");
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new Exception("Error fetching books: " . $e->getMessage());
        }
    }

    public function getBookById($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM books WHERE book_id = :id");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch();
        } catch (PDOException $e) {
            throw new Exception("Error fetching book: " . $e->getMessage());
        }
    }

    public function searchBooks($query) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM books 
                WHERE title LIKE :query 
                OR author LIKE :query 
                OR description LIKE :query
            ");
            $searchQuery = "%{$query}%";
            $stmt->bindValue(':query', $searchQuery, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new Exception("Error searching books: " . $e->getMessage());
        }
    }
}
