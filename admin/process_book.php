<?php
require_once '../inc/config.php';
require_once '../inc/session_config.php';
require_once '../database/DatabaseManager.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    http_response_code(403);
    die(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

header('Content-Type: application/json');

try {
    $db = DatabaseManager::getInstance();
    
    // Get book details
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get') {
        $book_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$book_id) {
            throw new Exception('Invalid book ID');
        }
        
        $book = $db->query(
            "SELECT * FROM books WHERE book_id = ?",
            [$book_id]
        );
        
        $result = $db->fetch($book);
        if (!$result) {
            throw new Exception('Book not found');
        }
        
        echo json_encode($result);
        exit;
    }
    
    // Handle POST requests
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? 'add';
        
        if ($action === 'delete') {
            $book_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            if (!$book_id) {
                throw new Exception('Invalid book ID');
            }
            
            // Delete book
            $db->query(
                "DELETE FROM books WHERE book_id = ?",
                [$book_id]
            );
            
            echo json_encode(['success' => true, 'message' => 'Book deleted successfully']);
            exit;
        }
        
        // Validate required fields
        $required_fields = ['title', 'author', 'price', 'stock_quantity', 'description'];
        foreach ($required_fields as $field) {
            if (!isset($_POST[$field]) || trim($_POST[$field]) === '') {
                throw new Exception("$field is required");
            }
        }
        
        // Prepare data
        $data = [
            'title' => trim($_POST['title']),
            'author' => trim($_POST['author']),
            'price' => filter_var($_POST['price'], FILTER_VALIDATE_FLOAT),
            'stock_quantity' => filter_var($_POST['stock_quantity'], FILTER_VALIDATE_INT),
            'description' => trim($_POST['description']),
            'category_id' => filter_var($_POST['category_id'], FILTER_VALIDATE_INT),
            'isbn' => trim($_POST['isbn'] ?? '')
        ];
        
        // Handle file upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['image'];
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            
            if (!in_array($file['type'], $allowed_types)) {
                throw new Exception('Invalid file type. Only JPG, PNG and GIF are allowed.');
            }
            
            $max_size = 5 * 1024 * 1024; // 5MB
            if ($file['size'] > $max_size) {
                throw new Exception('File is too large. Maximum size is 5MB.');
            }
            
            $upload_dir = '../images/books/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $filename = uniqid() . '_' . basename($file['name']);
            $filepath = $upload_dir . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                $data['image_url'] = 'images/books/' . $filename;
            }
        }
        
        if (isset($_POST['book_id']) && !empty($_POST['book_id'])) {
            // Update existing book
            $book_id = filter_var($_POST['book_id'], FILTER_VALIDATE_INT);
            if (!$book_id) {
                throw new Exception('Invalid book ID');
            }
            
            $set_clauses = [];
            $params = [];
            
            foreach ($data as $key => $value) {
                if ($value !== '') {
                    $set_clauses[] = "$key = ?";
                    $params[] = $value;
                }
            }
            
            $params[] = $book_id;
            
            $db->query(
                "UPDATE books SET " . implode(', ', $set_clauses) . " WHERE book_id = ?",
                $params
            );
            
            echo json_encode(['success' => true, 'message' => 'Book updated successfully']);
        } else {
            // Add new book
            $columns = implode(', ', array_keys($data));
            $values = implode(', ', array_fill(0, count($data), '?'));
            
            $db->query(
                "INSERT INTO books ($columns) VALUES ($values)",
                array_values($data)
            );
            
            echo json_encode(['success' => true, 'message' => 'Book added successfully']);
        }
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?> 