<?php
require_once '../inc/config.php';
require_once '../inc/session_config.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please login to add items to cart']);
    exit();
}

// Check if request is POST and has JSON content
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SERVER["CONTENT_TYPE"]) || strpos($_SERVER["CONTENT_TYPE"], "application/json") === false) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

// Get JSON data
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['book_id']) || !is_numeric($data['book_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid book ID']);
    exit();
}

$book_id = (int)$data['book_id'];
$user_id = $_SESSION['user_id'];
$quantity = isset($data['quantity']) ? (int)$data['quantity'] : 1;

try {
    $db = new DatabaseManager();
    
    // Check if book exists and has stock
    $book = $db->query(
        "SELECT book_id, stock_quantity, status FROM books WHERE book_id = ? AND status = 'available'",
        [$book_id]
    );
    
    if (empty($book)) {
        throw new Exception('Book not found or not available');
    }
    
    if ($book[0]['stock_quantity'] < $quantity) {
        throw new Exception('Not enough stock available');
    }
    
    // Check if item already exists in cart
    $existing_item = $db->query(
        "SELECT cart_item_id, quantity FROM cart_items WHERE user_id = ? AND book_id = ?",
        [$user_id, $book_id]
    );
    
    if (!empty($existing_item)) {
        // Update quantity if total doesn't exceed stock
        $new_quantity = $existing_item[0]['quantity'] + $quantity;
        if ($new_quantity > $book[0]['stock_quantity']) {
            throw new Exception('Cannot add more items than available in stock');
        }
        
        $db->query(
            "UPDATE cart_items SET quantity = ?, updated_at = CURRENT_TIMESTAMP WHERE cart_item_id = ?",
            [$new_quantity, $existing_item[0]['cart_item_id']]
        );
    } else {
        // Add new item to cart
        $db->query(
            "INSERT INTO cart_items (user_id, book_id, quantity) VALUES (?, ?, ?)",
            [$user_id, $book_id, $quantity]
        );
    }
    
    // Get updated cart count
    $cart_count = $db->query(
        "SELECT SUM(quantity) as total FROM cart_items WHERE user_id = ?",
        [$user_id]
    );
    
    echo json_encode([
        'success' => true,
        'message' => 'Item added to cart successfully',
        'cart_count' => $cart_count[0]['total'] ?? 0
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 