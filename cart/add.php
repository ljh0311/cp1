<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define root path if not already defined
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

// Set JSON response header
header('Content-Type: application/json');

// Debug log
error_log("=== Cart Add Debug ===");
error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);
error_log("Request URI: " . $_SERVER['REQUEST_URI']);
error_log("Current Directory: " . __DIR__);
error_log("Root Path: " . ROOT_PATH);

try {
    // Load required files
    require_once ROOT_PATH . '/inc/config.php';
    require_once ROOT_PATH . '/inc/session_config.php';
    require_once ROOT_PATH . '/inc/ErrorHandler.php';
    require_once ROOT_PATH . '/database/DatabaseManager.php';

    // Debug session
    error_log("Session ID: " . session_id());
    error_log("Session Status: " . session_status());
    error_log("Session Data: " . print_r($_SESSION, true));

    // Verify session status
    if (session_status() !== PHP_SESSION_ACTIVE) {
        throw new Exception('Session is not active');
    }

    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Please login to add items to cart');
    }

    // Check if request is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Get JSON data
    $json = file_get_contents('php://input');
    if (!$json) {
        throw new Exception('No data received');
    }

    error_log("Received JSON data: " . $json);

    $data = json_decode($json, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON data: ' . json_last_error_msg());
    }

    // Validate required fields
    if (!isset($data['book_id'])) {
        throw new Exception('Book ID is required');
    }

    $book_id = (int)$data['book_id'];
    $quantity = isset($data['quantity']) ? (int)$data['quantity'] : 1;
    $user_id = (int)$_SESSION['user_id'];

    if ($quantity < 1) {
        throw new Exception('Quantity must be at least 1');
    }

    // Initialize database connection
    $db = DatabaseManager::getInstance();
    
    // Start transaction
    $db->query("START TRANSACTION");

    try {
        // Check book exists and has enough stock
        $book = $db->query(
            "SELECT book_id, stock_quantity FROM books WHERE book_id = ? FOR UPDATE",
            [$book_id]
        );
        
        $book_data = $db->fetch($book);
        if (!$book_data) {
            throw new Exception('Book not found');
        }
        
        if ($book_data['stock_quantity'] < $quantity) {
            throw new Exception('Not enough stock available');
        }

        // Check if item already exists in cart
        $existing_item = $db->query(
            "SELECT cart_item_id, quantity FROM cart_items WHERE user_id = ? AND book_id = ?",
            [$user_id, $book_id]
        );
        
        $existing_data = $db->fetch($existing_item);
        
        if ($existing_data) {
            // Update quantity if total doesn't exceed stock
            $new_quantity = $existing_data['quantity'] + $quantity;
            if ($new_quantity > $book_data['stock_quantity']) {
                throw new Exception('Cannot add more items than available in stock');
            }
            
            $db->query(
                "UPDATE cart_items SET quantity = ?, updated_at = CURRENT_TIMESTAMP WHERE cart_item_id = ?",
                [$new_quantity, $existing_data['cart_item_id']]
            );
        } else {
            // Add new item to cart
            $db->query(
                "INSERT INTO cart_items (user_id, book_id, quantity, created_at, updated_at) 
                 VALUES (?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)",
                [$user_id, $book_id, $quantity]
            );
        }
        
        // Get updated cart count
        $cart_count = $db->query(
            "SELECT SUM(quantity) as total FROM cart_items WHERE user_id = ?",
            [$user_id]
        );
        
        $count_data = $db->fetch($cart_count);
        
        $db->query("COMMIT");
        
        echo json_encode([
            'success' => true,
            'message' => 'Item added to cart successfully',
            'cart_count' => $count_data['total'] ?? 0
        ]);
        
    } catch (Exception $e) {
        $db->query("ROLLBACK");
        throw $e;
    }
    
} catch (Exception $e) {
    error_log("Error in cart/add.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 