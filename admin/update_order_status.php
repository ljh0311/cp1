<?php
require_once '../inc/init.php';
requireAdmin();

header('Content-Type: application/json');

try {
    // Get JSON data
    $json = file_get_contents('php://input');
    if (!$json) {
        throw new Exception('No data received');
    }

    $data = json_decode($json, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON data');
    }

    if (!isset($data['order_id']) || !is_numeric($data['order_id'])) {
        throw new Exception('Invalid order ID');
    }

    if (!isset($data['status']) || !in_array($data['status'], ['pending', 'processing', 'completed', 'cancelled'])) {
        throw new Exception('Invalid status');
    }

    $order_id = (int)$data['order_id'];
    $status = $data['status'];

    $db = DatabaseManager::getInstance();
    
    // Start transaction
    $db->beginTransaction();
    
    try {
        // Get current order status
        $order_query = "SELECT status FROM orders WHERE order_id = ? FOR UPDATE";
        $order_result = $db->query($order_query, [$order_id]);
        $order = $db->fetch($order_result);
        
        if (!$order) {
            throw new Exception('Order not found');
        }
        
        // Validate status transition
        $current_status = $order['status'];
        $valid_transition = match($current_status) {
            'pending' => ['processing', 'completed', 'cancelled'],
            'processing' => ['completed', 'cancelled'],
            'completed' => [],
            'cancelled' => [],
            default => []
        };
        
        if (!in_array($status, $valid_transition)) {
            throw new Exception('Invalid status transition from ' . $current_status . ' to ' . $status);
        }
        
        // Update order status
        $update_query = "UPDATE orders SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE order_id = ?";
        $db->query($update_query, [$status, $order_id]);
        
        // If cancelling order, restore stock
        if ($status === 'cancelled') {
            $restore_stock_query = "
                UPDATE books b
                JOIN order_items oi ON b.book_id = oi.book_id
                SET b.stock_quantity = b.stock_quantity + oi.quantity
                WHERE oi.order_id = ?
            ";
            $db->query($restore_stock_query, [$order_id]);
        }
        
        $db->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Order status updated successfully'
        ]);
        
    } catch (Exception $e) {
        $db->rollBack();
        throw $e;
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 