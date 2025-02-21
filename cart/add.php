<?php
require_once '../inc/config.php';
require_once '../inc/session_config.php';
require_once '../inc/ErrorHandler.php';
require_once '../database/DatabaseManager.php';

// Set JSON response header early
header('Content-Type: application/json');

// Ensure all errors are caught and returned as JSON
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Function to handle errors
function handleError($errno, $errstr, $errfile, $errline) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $errstr,
        'debug' => defined('DEBUG_MODE') && DEBUG_MODE ? [
            'error' => $errstr,
            'file' => $errfile,
            'line' => $errline,
            'session_id' => session_id(),
            'session_status' => session_status(),
            'session_data' => $_SESSION
        ] : null
    ]);
    exit();
}

// Set error handler
set_error_handler('handleError');

try {
    // Debug session information
    if (defined('DEBUG_MODE') && DEBUG_MODE) {
        error_log("Session ID: " . session_id());
        error_log("Session status: " . session_status());
        error_log("Session data: " . print_r($_SESSION, true));
        error_log("Request headers: " . print_r(getallheaders(), true));
    }

    // Verify session status
    if (session_status() !== PHP_SESSION_ACTIVE) {
        throw new Exception('Session is not active');
    }

    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode([
            'success' => false, 
            'message' => 'Please login to add items to cart',
            'debug' => defined('DEBUG_MODE') && DEBUG_MODE ? [
                'session_id' => session_id(),
                'session_status' => session_status(),
                'session_data' => $_SESSION
            ] : null
        ]);
        exit();
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

    $data = json_decode($json, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON data: ' . json_last_error_msg());
    }

    if (!isset($data['book_id']) || !is_numeric($data['book_id'])) {
        throw new Exception('Invalid book ID');
    }

    $book_id = (int)$data['book_id'];
    $user_id = (int)$_SESSION['user_id'];
    $quantity = isset($data['quantity']) ? (int)$data['quantity'] : 1;

    if ($quantity < 1) {
        throw new Exception('Invalid quantity');
    }

    // Initialize database connection
    $db = DatabaseManager::getInstance();
    
    // Check if book exists and has stock
    $book = $db->query(
        "SELECT book_id, stock_quantity, status FROM books WHERE book_id = ? AND status = 'available'",
        [$book_id]
    );
    
    $book_data = $db->fetch($book);
    if (!$book_data) {
        throw new Exception('Book not found or not available');
    }
    
    if ($book_data['stock_quantity'] < $quantity) {
        throw new Exception('Not enough stock available');
    }
    
    // Start transaction
    $db->beginTransaction();
    
    try {
        // Check if item already exists in cart
        $existing_item = $db->query(
            "SELECT cart_item_id, quantity FROM cart_items WHERE user_id = ? AND book_id = ? FOR UPDATE",
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
        
        $db->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Item added to cart successfully',
            'cart_count' => $cart_count[0]['total'] ?? 0
        ]);
        
    } catch (Exception $e) {
        $db->rollBack();
        throw $e;
    }
    
} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug' => defined('DEBUG_MODE') && DEBUG_MODE ? [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'session_id' => session_id(),
            'session_status' => session_status(),
            'session_data' => $_SESSION
        ] : null
    ]);
} 