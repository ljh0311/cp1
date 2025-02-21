<?php
require_once '../inc/config.php';
require_once '../inc/session_config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please login to remove items']);
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

if (!isset($data['cart_item_id']) || !is_numeric($data['cart_item_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
    exit();
}

$cart_item_id = (int)$data['cart_item_id'];
$user_id = $_SESSION['user_id'];

try {
    $db = DatabaseManager::getInstance();
    
    // Verify cart item belongs to user
    $item = $db->query(
        "SELECT cart_item_id FROM cart_items WHERE cart_item_id = ? AND user_id = ?",
        [$cart_item_id, $user_id]
    );
    
    if (!$db->fetch($item)) {
        throw new Exception('Cart item not found');
    }
    
    // Remove item
    $db->query(
        "DELETE FROM cart_items WHERE cart_item_id = ?",
        [$cart_item_id]
    );
    
    echo json_encode([
        'success' => true,
        'message' => 'Item removed successfully'
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 