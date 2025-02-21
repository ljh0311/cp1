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
        )->fetch();
        
        if (!$book) {
            throw new Exception('Book not found');
        }

        echo json_encode($book);
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
            
            // Get current image path
            $current_image = $db->query(
                "SELECT image_url FROM books WHERE book_id = ?",
                [$book_id]
            )->fetch()['image_url'];
            
            // Delete book from database
            $db->query(
                "DELETE FROM books WHERE book_id = ?",
                [$book_id]
            );
            
            // Delete image file if it exists
            if ($current_image && file_exists("../public/images/books/$current_image")) {
                unlink("../public/images/books/$current_image");
            }
            
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
            'category_id' => filter_var($_POST['category_id'], FILTER_VALIDATE_INT) ?: null,
            'isbn' => trim($_POST['isbn'] ?? '')
        ];
        
        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['image'];
            $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
            
            if (!in_array($file['type'], $allowed_types)) {
                throw new Exception('Invalid image type. Only JPG, PNG, and WebP are allowed.');
            }
            
            $max_size = 5 * 1024 * 1024; // 5MB
            if ($file['size'] > $max_size) {
                throw new Exception('Image size too large. Maximum size is 5MB.');
            }
            
            $filename = uniqid() . '_' . basename($file['name']);
            $upload_path = "../public/images/books/$filename";
            
            if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
                throw new Exception('Failed to upload image');
            }
            
            $data['image_url'] = $filename;
        }
        
        // Update or insert book
        if (isset($_POST['book_id']) && !empty($_POST['book_id'])) {
            $book_id = filter_var($_POST['book_id'], FILTER_VALIDATE_INT);
            if (!$book_id) {
                    throw new Exception('Invalid book ID');
                }

            // Get current image if updating
            if (!isset($data['image_url'])) {
                $current_image = $db->query(
                    "SELECT image_url FROM books WHERE book_id = ?",
                    [$book_id]
                )->fetch()['image_url'];
                if ($current_image) {
                    $data['image_url'] = $current_image;
                }
            }
            
            // Build update query
            $set_clause = implode(', ', array_map(fn($key) => "$key = ?", array_keys($data)));
            $values = array_values($data);
            $values[] = $book_id;
            
            $db->query(
                "UPDATE books SET $set_clause WHERE book_id = ?",
                $values
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