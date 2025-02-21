<?php
require_once '../inc/init.php';
requireAdmin();

header('Content-Type: application/json');

try {
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        throw new Exception('Invalid order ID');
    }

    $order_id = (int)$_GET['id'];
    $db = DatabaseManager::getInstance();
    
    // Get order details
    $order_query = "SELECT o.*, u.username, u.email 
                   FROM orders o
                   JOIN users u ON o.user_id = u.user_id
                   WHERE o.order_id = ?";
    
    $order_result = $db->query($order_query, [$order_id]);
    $order = $db->fetch($order_result);
    
    if (!$order) {
        throw new Exception('Order not found');
    }
    
    // Get order items
    $items_query = "SELECT oi.*, b.title, b.image_url 
                   FROM order_items oi
                   JOIN books b ON oi.book_id = b.book_id
                   WHERE oi.order_id = ?";
    
    $items_result = $db->query($items_query, [$order_id]);
    $items = $db->fetchAll($items_result);
    
    // Build HTML for order details
    $html = '
    <div class="order-details">
        <div class="row mb-4">
            <div class="col-md-6">
                <h6 class="mb-2">Order Information</h6>
                <p class="mb-1"><strong>Order ID:</strong> #' . $order['order_id'] . '</p>
                <p class="mb-1"><strong>Date:</strong> ' . date('M j, Y g:i A', strtotime($order['created_at'])) . '</p>
                <p class="mb-1"><strong>Status:</strong> <span class="badge bg-' . 
                    match($order['status']) {
                        'pending' => 'warning',
                        'processing' => 'info',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        default => 'secondary'
                    } . '">' . $order['status'] . '</span></p>
                <p class="mb-1"><strong>Total Amount:</strong> $' . number_format($order['total_amount'], 2) . '</p>
            </div>
            <div class="col-md-6">
                <h6 class="mb-2">Customer Information</h6>
                <p class="mb-1"><strong>Name:</strong> ' . htmlspecialchars($order['username']) . '</p>
                <p class="mb-1"><strong>Email:</strong> ' . htmlspecialchars($order['email']) . '</p>
            </div>
        </div>
        
        <h6 class="mb-3">Order Items</h6>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>';
    
    foreach ($items as $item) {
        $html .= '
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="' . htmlspecialchars($item['image_url']) . '" 
                                     alt="' . htmlspecialchars($item['title']) . '"
                                     class="me-2" style="width: 50px; height: auto;">
                                <span>' . htmlspecialchars($item['title']) . '</span>
                            </div>
                        </td>
                        <td>' . $item['quantity'] . '</td>
                        <td>$' . number_format($item['price'], 2) . '</td>
                        <td>$' . number_format($item['price'] * $item['quantity'], 2) . '</td>
                    </tr>';
    }
    
    $html .= '
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-end"><strong>Total:</strong></td>
                        <td><strong>$' . number_format($order['total_amount'], 2) . '</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>';
    
    echo json_encode([
        'success' => true,
        'html' => $html
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 