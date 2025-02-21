<?php
require_once '../inc/config.php';
require_once '../inc/session_config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please login to update cart']);
    exit();
}

// Check if request is POST and has JSON content
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Get JSON data
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!isset($data['cart_item_id']) || !isset($data['quantity']) || 
    !is_numeric($data['cart_item_id']) || !is_numeric($data['quantity'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
    exit();
}

$cart_item_id = (int)$data['cart_item_id'];
$quantity = (int)$data['quantity'];
$user_id = $_SESSION['user_id'];

try {
    $db = DatabaseManager::getInstance();
    
    // Verify cart item belongs to user and check stock
    $item = $db->query(
        "SELECT ci.cart_item_id, b.stock_quantity 
         FROM cart_items ci 
         JOIN books b ON ci.book_id = b.book_id 
         WHERE ci.cart_item_id = ? AND ci.user_id = ?",
        [$cart_item_id, $user_id]
    );
    
    $item_data = $db->fetch($item);
    
    if (!$item_data) {
        throw new Exception('Cart item not found');
    }
    
    if ($quantity > $item_data['stock_quantity']) {
        throw new Exception('Not enough stock available');
    }
    
    // Update quantity
    $db->query(
        "UPDATE cart_items SET quantity = ?, updated_at = CURRENT_TIMESTAMP WHERE cart_item_id = ?",
        [$quantity, $cart_item_id]
    );
    
    echo json_encode([
        'success' => true,
        'message' => 'Cart updated successfully'
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 