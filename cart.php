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
        .quantity-input {
            max-width: 70px;
        }
        .update-quantity {
            min-width: 38px;
        }
        .quantity-controls {
            width: 120px;
        }
        .loading {
            opacity: 0.5;
            pointer-events: none;
        }
        .quantity-error {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
        .stock-warning {
            color: #856404;
            background-color: #fff3cd;
            border: 1px solid #ffeeba;
            padding: 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.875rem;
            margin-top: 0.5rem;
        }
        .cart-alert {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050;
            min-width: 300px;
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
                                <div class="cart-item mb-3 pb-3 border-bottom" data-item-id="<?php echo $item['cart_item_id']; ?>">
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
                                        <div class="col-3">
                                            <div class="quantity-controls">
                                                <div class="input-group">
                                                    <button class="btn btn-outline-secondary update-quantity" 
                                                            data-item-id="<?php echo $item['cart_item_id']; ?>"
                                                            data-action="decrease"
                                                            <?php echo $item['quantity'] <= 1 ? 'disabled' : ''; ?>>
                                                        <i class="fas fa-minus"></i>
                                                    </button>
                                                    <input type="number" class="form-control text-center quantity-input" 
                                                           value="<?php echo $item['quantity']; ?>"
                                                           min="1" 
                                                           max="<?php echo $item['stock_quantity']; ?>"
                                                           data-item-id="<?php echo $item['cart_item_id']; ?>"
                                                           data-stock="<?php echo $item['stock_quantity']; ?>">
                                                    <button class="btn btn-outline-secondary update-quantity"
                                                            data-item-id="<?php echo $item['cart_item_id']; ?>"
                                                            data-action="increase"
                                                            <?php echo $item['quantity'] >= $item['stock_quantity'] ? 'disabled' : ''; ?>>
                                                        <i class="fas fa-plus"></i>
                                                    </button>
                                                </div>
                                                <?php if ($item['stock_quantity'] <= 5): ?>
                                                    <div class="stock-warning">
                                                        <i class="fas fa-exclamation-triangle me-1"></i>
                                                        Only <?php echo $item['stock_quantity']; ?> left in stock
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="col-2 text-end">
                                            <span class="fw-bold item-total">
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
                                <span class="cart-subtotal">$<?php echo number_format($total, 2); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span>Shipping</span>
                                <span>Free</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-4">
                                <span class="fw-bold">Total</span>
                                <span class="fw-bold cart-total">$<?php echo number_format($total, 2); ?></span>
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
            let updateTimeout;
            const showAlert = (message, type = 'success') => {
                const alert = document.createElement('div');
                alert.className = `alert alert-${type} alert-dismissible fade show cart-alert`;
                alert.innerHTML = `
                    <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                document.body.appendChild(alert);
                setTimeout(() => alert.remove(), 3000);
            };

            const updateCartItem = async (itemId, quantity) => {
                const cartItem = document.querySelector(`.cart-item[data-item-id="${itemId}"]`);
                const input = cartItem.querySelector('.quantity-input');
                const decreaseBtn = cartItem.querySelector('[data-action="decrease"]');
                const increaseBtn = cartItem.querySelector('[data-action="increase"]');
                const stock = parseInt(input.dataset.stock);

                try {
                    cartItem.classList.add('loading');
                    const response = await fetch('/cart/update.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            cart_item_id: itemId,
                            quantity: quantity
                        })
                    });

                    const data = await response.json();
                    if (data.success) {
                        // Update UI
                        input.value = quantity;
                        decreaseBtn.disabled = quantity <= 1;
                        increaseBtn.disabled = quantity >= stock;
                        
                        // Update totals
                        const itemTotal = cartItem.querySelector('.item-total');
                        itemTotal.textContent = `$${(data.item_price * quantity).toFixed(2)}`;
                        
                        document.querySelector('.cart-subtotal').textContent = `$${data.cart_total.toFixed(2)}`;
                        document.querySelector('.cart-total').textContent = `$${data.cart_total.toFixed(2)}`;
                        
                        // Update cart count in nav
                        const cartCount = document.getElementById('cartCount');
                        if (cartCount) {
                            cartCount.textContent = data.cart_count;
                        }

                        showAlert('Cart updated successfully');
                    } else {
                        throw new Error(data.message || 'Failed to update cart');
                    }
                } catch (error) {
                    showAlert(error.message, 'danger');
                    // Revert to previous value
                    input.value = input.defaultValue;
                } finally {
                    cartItem.classList.remove('loading');
                }
            };

            // Quantity input handling
            document.querySelectorAll('.quantity-input').forEach(input => {
                input.addEventListener('change', function() {
                    clearTimeout(updateTimeout);
                    const quantity = parseInt(this.value);
                    const stock = parseInt(this.dataset.stock);
                    const itemId = this.dataset.itemId;

                    if (quantity < 1) {
                        this.value = 1;
                    } else if (quantity > stock) {
                        this.value = stock;
                        showAlert(`Only ${stock} items available in stock`, 'warning');
                    }

                    updateTimeout = setTimeout(() => {
                        updateCartItem(itemId, parseInt(this.value));
                    }, 500);
                });
            });

            // Update quantity buttons
            document.querySelectorAll('.update-quantity').forEach(button => {
                button.addEventListener('click', function() {
                    const itemId = this.dataset.itemId;
                    const input = document.querySelector(`.quantity-input[data-item-id="${itemId}"]`);
                    const currentQuantity = parseInt(input.value);
                    const newQuantity = this.dataset.action === 'increase' ? currentQuantity + 1 : currentQuantity - 1;
                    
                    if (newQuantity >= 1 && newQuantity <= parseInt(input.dataset.stock)) {
                        updateCartItem(itemId, newQuantity);
                    }
                });
            });

            // Remove item
            document.querySelectorAll('.remove-item').forEach(button => {
                button.addEventListener('click', async function() {
                    const itemId = this.dataset.itemId;
                    const cartItem = document.querySelector(`.cart-item[data-item-id="${itemId}"]`);
                    
                    if (confirm('Are you sure you want to remove this item?')) {
                        try {
                            cartItem.classList.add('loading');
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
                                cartItem.remove();
                                showAlert('Item removed from cart');
                                
                                // Update totals
                                document.querySelector('.cart-subtotal').textContent = `$${data.cart_total.toFixed(2)}`;
                                document.querySelector('.cart-total').textContent = `$${data.cart_total.toFixed(2)}`;
                                
                                // Update cart count in nav
                                const cartCount = document.getElementById('cartCount');
                                if (cartCount) {
                                    cartCount.textContent = data.cart_count;
                                }

                                // If cart is empty, reload the page
                                if (data.cart_count === 0) {
                                    location.reload();
                                }
                            } else {
                                throw new Error(data.message || 'Failed to remove item');
                            }
                        } catch (error) {
                            showAlert(error.message, 'danger');
                        } finally {
                            cartItem.classList.remove('loading');
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