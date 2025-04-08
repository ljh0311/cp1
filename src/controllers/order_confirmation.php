<?php
// Define root path if not already defined
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', __DIR__);
}

require_once 'inc/config.php';
require_once 'inc/session_config.php';
require_once ROOT_PATH . '/inc/SessionManager.php';
require_once ROOT_PATH . '/database/DatabaseManager.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get session manager instance
$sessionManager = SessionManager::getInstance();

// Check if user is logged in
if (!$sessionManager->isLoggedIn()) {
    $sessionManager->setFlash('error', 'Please log in to view your order.');
    header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

// Check if order_id is provided
if (!isset($_GET['order_id'])) {
    // Check if we have a last_order_id in session
    if (isset($_SESSION['last_order_id'])) {
        header('Location: ' . $_SERVER['PHP_SELF'] . '?order_id=' . $_SESSION['last_order_id']);
        exit();
    }
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
        [$_GET['order_id'], $sessionManager->getUserId()]
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
    
} catch (Exception $e) {
    error_log("Error in order_confirmation.php: " . $e->getMessage());
    $sessionManager->setFlash('error', 'Error retrieving order details.');
    header('Location: index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Order Confirmation - <?php echo SITE_NAME; ?></title>
    <?php require_once 'inc/head.inc.php'; ?>
    <style>
        .order-confirmation {
            background-color: #f8f9fa;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .success-icon {
            color: #28a745;
            font-size: 5rem;
            margin-bottom: 1.5rem;
        }
        .order-number {
            font-size: 1.2rem;
            color: #6c757d;
            background: #e9ecef;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            display: inline-block;
        }
        .order-details {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .item-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
        }
        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        .status-badge {
            font-size: 0.9rem;
            padding: 0.5rem 1rem;
        }
        .order-summary {
            background: #fff;
            border-radius: 8px;
            padding: 1.5rem;
        }
        .total-row {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .continue-shopping {
            margin-top: 2rem;
            padding: 0.75rem 2rem;
        }
    </style>
</head>
<body>
    <?php require_once 'inc/nav.inc.php'; ?>
    
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="order-confirmation p-4 p-md-5">
                    <!-- Success Header -->
                    <div class="text-center mb-5">
                        <i class="bi bi-check-circle-fill success-icon"></i>
                        <h1 class="mb-3">Thank You for Your Order!</h1>
                        <p class="lead text-muted mb-4">Your order has been successfully placed and confirmed.</p>
                        <div class="order-number mb-4">
                            Order #<?php echo str_pad($order['order_id'], 8, '0', STR_PAD_LEFT); ?>
                        </div>
                    </div>

                    <!-- Order Information -->
                    <div class="row mb-4">
                        <div class="col-md-6 mb-4 mb-md-0">
                            <div class="order-details">
                                <h5 class="mb-4">Order Details</h5>
                                <div class="row g-3">
                                    <div class="col-6">
                                        <p class="text-muted mb-1">Order Date</p>
                                        <p class="fw-bold"><?php echo date('F j, Y', strtotime($order['created_at'])); ?></p>
                                    </div>
                                    <div class="col-6">
                                        <p class="text-muted mb-1">Status</p>
                                        <span class="badge bg-success status-badge">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </div>
                                    <div class="col-12">
                                        <p class="text-muted mb-1">Email</p>
                                        <p class="fw-bold"><?php echo htmlspecialchars($order['email']); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="order-summary">
                                <h5 class="mb-4">Order Summary</h5>
                                <div class="d-flex justify-content-between mb-3">
                                    <span class="text-muted">Subtotal</span>
                                    <span>$<?php echo number_format($order['total_amount'], 2); ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-3">
                                    <span class="text-muted">Shipping</span>
                                    <span>Free</span>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between fw-bold">
                                    <span>Total</span>
                                    <span>$<?php echo number_format($order['total_amount'], 2); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Items -->
                    <div class="order-details">
                        <h5 class="mb-4">Order Items</h5>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th class="text-center">Quantity</th>
                                        <th class="text-end">Price</th>
                                        <th class="text-end">Total</th>
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
                                                             class="item-image me-3">
                                                    <?php endif; ?>
                                                    <span><?php echo htmlspecialchars($item['title']); ?></span>
                                                </div>
                                            </td>
                                            <td class="text-center"><?php echo $item['quantity']; ?></td>
                                            <td class="text-end">$<?php echo number_format($item['price_at_time'], 2); ?></td>
                                            <td class="text-end">$<?php echo number_format($item['price_at_time'] * $item['quantity'], 2); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr class="total-row">
                                        <td colspan="3" class="text-end">Total:</td>
                                        <td class="text-end">$<?php echo number_format($order['total_amount'], 2); ?></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="text-center">
                        <a href="index.php" class="btn btn-primary continue-shopping">
                            <i class="bi bi-cart me-2"></i>Continue Shopping
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php require_once 'inc/footer.inc.php'; ?>
</body>
</html> 