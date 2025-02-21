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
    
    // Get order details
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get') {
        $order_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$order_id) {
            throw new Exception('Invalid order ID');
        }
        
        // Get order details
        $order = $db->query(
            "SELECT o.*, u.email 
             FROM orders o 
             JOIN users u ON o.user_id = u.user_id 
             WHERE o.order_id = ?",
            [$order_id]
        )->fetch();
        
        if (!$order) {
            throw new Exception('Order not found');
        }
        
        // Get order items
        $items = $db->query(
            "SELECT oi.*, b.title 
             FROM order_items oi 
             JOIN books b ON oi.book_id = b.book_id 
             WHERE oi.order_id = ?",
            [$order_id]
        )->fetchAll();
        
        $order['items'] = $items;
        
        echo json_encode($order);
        exit;
    }
    
    // Handle POST requests
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        $order_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        
        if (!$order_id) {
            throw new Exception('Invalid order ID');
        }
        
        switch ($action) {
            case 'delete':
                // First delete order items
                $db->query(
                    "DELETE FROM order_items WHERE order_id = ?",
                    [$order_id]
                );
                
                // Then delete the order
                $db->query(
                    "DELETE FROM orders WHERE order_id = ?",
                    [$order_id]
                );
                
                echo json_encode(['success' => true, 'message' => 'Order deleted successfully']);
                break;
                
            case 'update_status':
                $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);
                $allowed_statuses = ['pending', 'processing', 'completed', 'cancelled'];
                
                if (!in_array($status, $allowed_statuses)) {
                    throw new Exception('Invalid status');
                }
                
                $db->query(
                    "UPDATE orders SET status = ? WHERE order_id = ?",
                    [$status, $order_id]
                );
                
                echo json_encode(['success' => true, 'message' => 'Order status updated successfully']);
                break;
                
            default:
                throw new Exception('Invalid action');
        }
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 