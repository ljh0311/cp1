<?php
// Define root path if not already defined
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

require_once ROOT_PATH . '/inc/config.php';
require_once ROOT_PATH . '/inc/session_config.php';
require_once ROOT_PATH . '/database/DatabaseManager.php';

// Set JSON response header
header('Content-Type: application/json');

try {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('Please log in to remove items from cart');
    }

    // Get and validate JSON data
    $json = file_get_contents('php://input');
    if (!$json) {
        throw new Exception('No data received');
    }

    $data = json_decode($json, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON data');
    }

    // Validate cart_item_id
    if (!isset($data['cart_item_id'])) {
        throw new Exception('Cart item ID is required');
    }

    $cart_item_id = filter_var($data['cart_item_id'], FILTER_VALIDATE_INT);
    if (!$cart_item_id) {
        throw new Exception('Invalid cart item ID');
    }

    $db = DatabaseManager::getInstance();
    
    // Verify the item belongs to the current user
    $item = $db->query(
        "SELECT cart_item_id FROM cart_items WHERE cart_item_id = ? AND user_id = ?",
        [$cart_item_id, $_SESSION['user_id']]
    )->fetch();

    if (!$item) {
        throw new Exception('Item not found in your cart');
    }

    // Delete the item
    $db->query(
        "DELETE FROM cart_items WHERE cart_item_id = ? AND user_id = ?",
        [$cart_item_id, $_SESSION['user_id']]
    );

    // Get updated cart count for the response
    $cart_count = $db->query(
        "SELECT SUM(quantity) as total FROM cart_items WHERE user_id = ?",
        [$_SESSION['user_id']]
    )->fetch();

    echo json_encode([
        'success' => true,
        'message' => 'Item removed successfully',
        'cart_count' => $cart_count['total'] ?? 0
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 