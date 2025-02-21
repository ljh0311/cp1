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
        );
        
        $result = $db->fetch($order);
        if (!$result) {
            throw new Exception('Order not found');
        }
        
        // Get order items
        $items = $db->query(
            "SELECT oi.*, b.title 
             FROM order_items oi 
             JOIN books b ON oi.book_id = b.book_id 
             WHERE oi.order_id = ?",
            [$order_id]
        );
        
        $result['items'] = $db->fetchAll($items);
        
        echo json_encode($result);
        exit;
    }
    
    // Handle POST requests
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'delete') {
            $order_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            if (!$order_id) {
                throw new Exception('Invalid order ID');
            }
            
            // Start transaction
            $db->beginTransaction();
            
            try {
                // Delete order items first
                $db->query(
                    "DELETE FROM order_items WHERE order_id = ?",
                    [$order_id]
                );
                
                // Then delete the order
                $db->query(
                    "DELETE FROM orders WHERE order_id = ?",
                    [$order_id]
                );
                
                $db->commit();
                echo json_encode(['success' => true, 'message' => 'Order deleted successfully']);
            } catch (Exception $e) {
                $db->rollback();
                throw $e;
            }
        } elseif ($action === 'update_status') {
            $order_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);
            
            if (!$order_id || !$status) {
                throw new Exception('Invalid order ID or status');
            }
            
            // Update order status
            $db->query(
                "UPDATE orders SET status = ? WHERE order_id = ?",
                [$status, $order_id]
            );
            
            echo json_encode(['success' => true, 'message' => 'Order status updated successfully']);
        } else {
            throw new Exception('Invalid action');
        }
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 