<?php
require_once 'inc/config.php';
require_once 'inc/session_config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Check if order_id is provided
if (!isset($_GET['order_id'])) {
    header('Location: index.php');
    exit();
}

try {
    $db = DatabaseManager::getInstance();
    
    // Get order details
    $order_query = $db->query(
        "SELECT o.*, u.email 
         FROM orders o 
         JOIN users u ON o.user_id = u.user_id 
         WHERE o.order_id = ? AND o.user_id = ?",
        [$_GET['order_id'], $_SESSION['user_id']]
    );
    
    $order = $db->fetch($order_query);
    
    if (!$order) {
        throw new Exception('Order not found.');
    }
    
    // Get order items
    $items_query = $db->query(
        "SELECT oi.*, b.title, b.image_url 
         FROM order_items oi 
         JOIN books b ON oi.book_id = b.book_id 
         WHERE oi.order_id = ?",
        [$order['order_id']]
    );
    
    $items = $db->fetchAll($items_query);
    
    // Parse shipping details
    $shipping = json_decode($order['shipping_address'], true);
    
} catch (Exception $e) {
    ErrorHandler::logError($e->getMessage());
    header('Location: index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Order Confirmation - <?php echo SITE_NAME; ?></title>
    <?php require_once 'inc/head.inc.php'; ?>
</head>
<body>
    <?php require_once 'inc/nav.inc.php'; ?>
    
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="mb-4">
                            <i class="bi bi-check-circle text-success" style="font-size: 4rem;"></i>
                        </div>
                        <h1 class="card-title mb-4">Thank You for Your Order!</h1>
                        <p class="lead mb-4">Your order has been successfully placed and confirmed.</p>
                        <div class="text-muted mb-4">
                            Order #<?php echo str_pad($order['order_id'], 8, '0', STR_PAD_LEFT); ?>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h5 class="mb-3">Shipping Details</h5>
                                <address class="mb-0">
                                    <?php echo htmlspecialchars($shipping['first_name'] . ' ' . $shipping['last_name']); ?><br>
                                    <?php echo htmlspecialchars($shipping['address']); ?><br>
                                    <?php echo htmlspecialchars($shipping['city'] . ', ' . $shipping['state'] . ' ' . $shipping['zip']); ?><br>
                                    <?php echo htmlspecialchars($shipping['email']); ?>
                                </address>
                            </div>
                            <div class="col-md-6">
                                <h5 class="mb-3">Order Summary</h5>
                                <p class="mb-1">Status: <span class="badge bg-success">Paid</span></p>
                                <p class="mb-0">Total: $<?php echo number_format($order['total_amount'], 2); ?></p>
                            </div>
                        </div>
                        
                        <h5 class="mb-4">Order Items</h5>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Quantity</th>
                                        <th>Price</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($items as $item): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if ($item['image_url']): ?>
                                                        <img src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                                                             alt="<?php echo htmlspecialchars($item['title']); ?>"
                                                             class="me-3" style="width: 50px;">
                                                    <?php endif; ?>
                                                    <?php echo htmlspecialchars($item['title']); ?>
                                                </div>
                                            </td>
                                            <td><?php echo $item['quantity']; ?></td>
                                            <td>$<?php echo number_format($item['price'], 2); ?></td>
                                            <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                        <td><strong>$<?php echo number_format($order['total_amount'], 2); ?></strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        
                        <div class="mt-4">
                            <a href="index.php" class="btn btn-primary">Continue Shopping</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php require_once 'inc/footer.inc.php'; ?>
</body>
</html> 