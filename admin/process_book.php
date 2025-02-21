<?php
require_once '../inc/config.php';
require_once '../inc/session_config.php';
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

try {
    $db = new DatabaseManager();

    // Get book details
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get') {
        $bookId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$bookId) {
            throw new Exception('Invalid book ID');
        }

        $book = $db->query("SELECT * FROM books WHERE id = ?", [$bookId]);
        if (!$book) {
            throw new Exception('Book not found');
        }

        echo json_encode($book[0]);
        exit();
    }

    // Handle POST requests
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? 'create';

        switch ($action) {
            case 'create':
            case 'update':
                // Validate input
                $bookId = filter_input(INPUT_POST, 'book_id', FILTER_VALIDATE_INT);
                $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
                $author = filter_input(INPUT_POST, 'author', FILTER_SANITIZE_STRING);
                $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
                $stock = filter_input(INPUT_POST, 'stock', FILTER_VALIDATE_INT);
                $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);

                if (!$title || !$author || !$price || !$stock || !$description) {
                    throw new Exception('All fields are required');
                }

                // Handle file upload
                $imagePath = null;
                if (isset($_FILES['cover']) && $_FILES['cover']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = '../uploads/books/';
                    if (!file_exists($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }

                    $fileInfo = pathinfo($_FILES['cover']['name']);
                    $extension = strtolower($fileInfo['extension']);
                    
                    // Validate file type
                    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
                    if (!in_array($extension, $allowedTypes)) {
                        throw new Exception('Invalid file type. Only JPG, PNG, and GIF are allowed.');
                    }

                    // Generate unique filename
                    $filename = uniqid() . '.' . $extension;
                    $imagePath = 'uploads/books/' . $filename;

                    if (!move_uploaded_file($_FILES['cover']['tmp_name'], "../$imagePath")) {
                        throw new Exception('Failed to upload file');
                    }
                }

                if ($action === 'create') {
                    // Insert new book
                    $sql = "INSERT INTO books (title, author, price, stock, description, image_url, created_at) 
                            VALUES (?, ?, ?, ?, ?, ?, NOW())";
                    $params = [$title, $author, $price, $stock, $description, $imagePath];
                } else {
                    // Update existing book
                    if (!$bookId) {
                        throw new Exception('Book ID is required for updates');
                    }

                    if ($imagePath) {
                        $sql = "UPDATE books SET title = ?, author = ?, price = ?, stock = ?, 
                                description = ?, image_url = ? WHERE id = ?";
                        $params = [$title, $author, $price, $stock, $description, $imagePath, $bookId];
                    } else {
                        $sql = "UPDATE books SET title = ?, author = ?, price = ?, stock = ?, 
                                description = ? WHERE id = ?";
                        $params = [$title, $author, $price, $stock, $description, $bookId];
                    }
                }

                $db->query($sql, $params);
                echo json_encode(['success' => true]);
                break;

            case 'delete':
                $bookId = filter_input(INPUT_POST, 'book_id', FILTER_VALIDATE_INT);
                if (!$bookId) {
                    throw new Exception('Invalid book ID');
                }

                // Get the image path before deleting
                $book = $db->query("SELECT image_url FROM books WHERE id = ?", [$bookId]);
                if ($book && $book[0]['image_url']) {
                    $imagePath = "../" . $book[0]['image_url'];
                    if (file_exists($imagePath)) {
                        unlink($imagePath);
                    }
                }

                $db->query("DELETE FROM books WHERE id = ?", [$bookId]);
                echo json_encode(['success' => true]);
                break;

            default:
                throw new Exception('Invalid action');
        }
    }
} catch (Exception $e) {
    error_log("Error in process_book.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?> 