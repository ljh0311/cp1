<?php
require_once 'inc/config.php';
require_once 'inc/session_config.php';
require_once 'inc/ErrorHandler.php';
require_once 'database/DatabaseManager.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?redirect=cart.php');
    exit();
}

try {
    $db = DatabaseManager::getInstance();
    
    // Get cart items with book details
    $cart_items = $db->query(
        "SELECT ci.*, b.title, b.price, b.image_url, b.stock_quantity 
         FROM cart_items ci 
         JOIN books b ON ci.book_id = b.book_id 
         WHERE ci.user_id = ?",
        [$_SESSION['user_id']]
    );
    
    $items = $db->fetchAll($cart_items);
    
    // Calculate total
    $total = 0;
    foreach ($items as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    
} catch (Exception $e) {
    ErrorHandler::logError($e->getMessage());
    $items = [];
    $total = 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Shopping Cart - <?php echo SITE_NAME; ?></title>
    <?php require_once 'inc/head.inc.php'; ?>
    <style>
        .cart-item {
            transition: background-color 0.3s ease;
        }
        .cart-item:hover {
            background-color: #f8f9fa;
        }
        .quantity-display {
            background-color: #e9ecef;
            padding: 0.5rem 1rem;
            border-radius: 0.25rem;
            font-weight: 500;
        }
        .remove-item {
            transition: all 0.2s ease;
        }
        .remove-item:hover {
            transform: scale(1.1);
        }
    </style>
</head>
<body>
    <?php require_once 'inc/nav.inc.php'; ?>
    
    <div class="container py-5">
        <h1 class="mb-4">Shopping Cart</h1>
        
        <?php if (empty($items)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                Your cart is empty. <a href="books.php" class="alert-link">Continue shopping</a>
            </div>
        <?php else: ?>
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-body">
                            <?php foreach ($items as $item): ?>
                                <div class="cart-item mb-3 pb-3 border-bottom">
                                    <div class="row align-items-center">
                                        <div class="col-2">
                                            <img src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                                                 class="img-fluid rounded" 
                                                 alt="<?php echo htmlspecialchars($item['title']); ?>">
                                        </div>
                                        <div class="col-4">
                                            <h5 class="mb-1"><?php echo htmlspecialchars($item['title']); ?></h5>
                                            <p class="text-muted mb-0">$<?php echo number_format($item['price'], 2); ?></p>
                                        </div>
                                        <div class="col-2">
                                            <div class="quantity-display">
                                                Qty: <?php echo $item['quantity']; ?>
                                            </div>
                                        </div>
                                        <div class="col-3 text-end">
                                            <span class="fw-bold">
                                                $<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                                            </span>
                                        </div>
                                        <div class="col-1 text-end">
                                            <button class="btn btn-link text-danger remove-item" 
                                                    data-item-id="<?php echo $item['cart_item_id']; ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title mb-4">Order Summary</h5>
                            <div class="d-flex justify-content-between mb-3">
                                <span>Subtotal</span>
                                <span>$<?php echo number_format($total, 2); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span>Shipping</span>
                                <span>Free</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-4">
                                <span class="fw-bold">Total</span>
                                <span class="fw-bold">$<?php echo number_format($total, 2); ?></span>
                            </div>
                            <button class="btn btn-primary w-100 proceed-to-checkout">
                                Proceed to Checkout
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php require_once 'inc/footer.inc.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Remove item functionality
            document.querySelectorAll('.remove-item').forEach(button => {
                button.addEventListener('click', async function() {
                    if (confirm('Are you sure you want to remove this item?')) {
                        const itemId = this.dataset.itemId;
                        try {
                            const response = await fetch('/cart/remove.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                },
                                body: JSON.stringify({
                                    cart_item_id: itemId
                                })
                            });

                            const data = await response.json();
                            if (data.success) {
                                location.reload();
                            } else {
                                alert(data.message || 'Failed to remove item');
                            }
                        } catch (error) {
                            alert('Failed to remove item. Please try again.');
                        }
                    }
                });
            });

            // Proceed to checkout
            document.querySelector('.proceed-to-checkout')?.addEventListener('click', function() {
                window.location.href = 'checkout.php';
            });
        });
    </script>
</body>
</html> 