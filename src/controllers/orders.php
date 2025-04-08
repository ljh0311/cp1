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
    $sessionManager->setFlash('error', 'Please log in to view your orders.');
    header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

try {
    $db = DatabaseManager::getInstance();
    
    // Get all orders for the user with their items
    $orders_query = $db->query(
        "SELECT o.*, 
                COUNT(oi.order_item_id) as total_items,
                u.email
         FROM orders o 
         JOIN users u ON o.user_id = u.user_id
         LEFT JOIN order_items oi ON o.order_id = oi.order_id
         WHERE o.user_id = ?
         GROUP BY o.order_id
         ORDER BY o.created_at DESC",
        [$sessionManager->getUserId()]
    );
    
    $orders = $db->fetchAll($orders_query);
    
} catch (Exception $e) {
    error_log("Error in orders.php: " . $e->getMessage());
    $sessionManager->setFlash('error', 'Error retrieving orders.');
    $orders = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>My Orders - <?php echo SITE_NAME; ?></title>
    <?php require_once 'inc/head.inc.php'; ?>
    <style>
        .orders-container {
            background-color: #f8f9fa;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .order-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            transition: transform 0.2s;
        }
        .order-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .order-number {
            color: #6c757d;
            font-size: 0.9rem;
        }
        .status-badge {
            font-size: 0.8rem;
            padding: 0.4rem 0.8rem;
        }
        .order-date {
            color: #6c757d;
            font-size: 0.9rem;
        }
        .view-details-btn {
            font-size: 0.9rem;
        }
        .empty-orders {
            text-align: center;
            padding: 3rem;
        }
        .empty-orders i {
            font-size: 4rem;
            color: #6c757d;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <?php require_once 'inc/nav.inc.php'; ?>
    
    <div class="container py-5">
        <div class="orders-container p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">My Orders</h1>
                <a href="books.php" class="btn btn-primary">
                    <i class="bi bi-cart me-2"></i>Continue Shopping
                </a>
            </div>

            <?php if (empty($orders)): ?>
                <div class="empty-orders">
                    <i class="bi bi-bag-x"></i>
                    <h3>No Orders Yet</h3>
                    <p class="text-muted">You haven't placed any orders yet.</p>
                    <a href="books.php" class="btn btn-primary mt-3">Start Shopping</a>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($orders as $order): ?>
                        <div class="col-12">
                            <div class="order-card p-4">
                                <div class="row align-items-center">
                                    <div class="col-md-3">
                                        <span class="order-number d-block mb-2">
                                            Order #<?php echo str_pad($order['order_id'], 8, '0', STR_PAD_LEFT); ?>
                                        </span>
                                        <span class="order-date">
                                            <?php echo date('F j, Y', strtotime($order['created_at'])); ?>
                                        </span>
                                    </div>
                                    <div class="col-md-3">
                                        <span class="text-muted d-block mb-2">Status</span>
                                        <span class="badge bg-<?php echo $order['status'] === 'completed' ? 'success' : 'warning'; ?> status-badge">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </div>
                                    <div class="col-md-3">
                                        <span class="text-muted d-block mb-2">Order Summary</span>
                                        <div>
                                            <strong><?php echo $order['total_items']; ?></strong> items
                                            <br>
                                            <strong>$<?php echo number_format($order['total_amount'], 2); ?></strong>
                                        </div>
                                    </div>
                                    <div class="col-md-3 text-md-end mt-3 mt-md-0">
                                        <a href="order_confirmation.php?order_id=<?php echo $order['order_id']; ?>" 
                                           class="btn btn-outline-primary view-details-btn">
                                            View Details
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php require_once 'inc/footer.inc.php'; ?>
</body>
</html> 