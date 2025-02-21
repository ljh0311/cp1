<?php
require_once 'inc/config.php';
require_once 'inc/session_config.php';
require_once ROOT_PATH . '/database/DatabaseManager.php';
require_once ROOT_PATH . '/inc/SessionManager.php';

// Get session manager instance
$sessionManager = SessionManager::getInstance();

// Require user to be logged in
$sessionManager->requireLogin('login.php?redirect=checkout.php');

try {
    $db = DatabaseManager::getInstance();
    
    // Get cart items with book details
    $cart_items = $db->query(
        "SELECT ci.*, b.title, b.price, b.image_url 
         FROM cart_items ci 
         JOIN books b ON ci.book_id = b.book_id 
         WHERE ci.user_id = ?",
        [$sessionManager->getUserId()]
    );
    
    $items = $db->fetchAll($cart_items);
    
    // Calculate total
    $total = 0;
    foreach ($items as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    
    if (empty($items)) {
        header('Location: cart.php');
        exit();
    }
    
} catch (Exception $e) {
    ErrorHandler::logError($e->getMessage());
    $sessionManager->setFlash('error', 'An error occurred while loading your cart. Please try again.');
    header('Location: cart.php');
    exit();
}

// Get any flash messages
$error_message = $sessionManager->getFlash('error');
$success_message = $sessionManager->getFlash('success');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Checkout - <?php echo SITE_NAME; ?></title>
    <?php require_once 'inc/head.inc.php'; ?>
    <!-- Add Stripe.js -->
    <script src="https://js.stripe.com/v3/"></script>
</head>
<body>
    <?php require_once 'inc/nav.inc.php'; ?>
    
    <?php if ($error_message): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($error_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <?php if ($success_message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($success_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <div class="container py-5">
        <h1 class="mb-4">Checkout</h1>
        
        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Shipping Information</h5>
                        <form id="payment-form">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="firstName" class="form-label">First Name</label>
                                    <input type="text" class="form-control" id="firstName" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="lastName" class="form-label">Last Name</label>
                                    <input type="text" class="form-control" id="lastName" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="address" class="form-label">Address</label>
                                <input type="text" class="form-control" id="address" required>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="city" class="form-label">City</label>
                                    <input type="text" class="form-control" id="city" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="state" class="form-label">State</label>
                                    <input type="text" class="form-control" id="state" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="zip" class="form-label">ZIP Code</label>
                                    <input type="text" class="form-control" id="zip" required>
                                </div>
                            </div>
                            
                            <hr class="my-4">
                            
                            <h5 class="mb-4">Payment Information</h5>
                            <div id="card-element" class="form-control mb-3">
                                <!-- Stripe Card Element will be inserted here -->
                            </div>
                            <div id="card-errors" class="alert alert-danger d-none" role="alert"></div>
                            
                            <button type="submit" class="btn btn-primary w-100" id="submit-button">
                                Pay $<?php echo number_format($total, 2); ?>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Order Summary</h5>
                        <?php foreach ($items as $item): ?>
                            <div class="d-flex justify-content-between mb-3">
                                <div>
                                    <h6 class="mb-0"><?php echo htmlspecialchars($item['title']); ?></h6>
                                    <small class="text-muted">Quantity: <?php echo $item['quantity']; ?></small>
                                </div>
                                <span>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                            </div>
                        <?php endforeach; ?>
                        
                        <hr>
                        
                        <div class="d-flex justify-content-between mb-3">
                            <span>Subtotal</span>
                            <span>$<?php echo number_format($total, 2); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span>Shipping</span>
                            <span>Free</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-0">
                            <span class="fw-bold">Total</span>
                            <span class="fw-bold">$<?php echo number_format($total, 2); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php require_once 'inc/footer.inc.php'; ?>

    <script>
        // Initialize Stripe
        const stripe = Stripe('<?php echo STRIPE_PUBLISHABLE_KEY; ?>');
        const elements = stripe.elements();
        
        // Create card Element
        const card = elements.create('card');
        card.mount('#card-element');
        
        // Handle form submission
        const form = document.getElementById('payment-form');
        const submitButton = document.getElementById('submit-button');
        
        form.addEventListener('submit', async function(event) {
            event.preventDefault();
            
            submitButton.disabled = true;
            submitButton.textContent = 'Processing...';
            
            try {
                const {paymentMethod, error} = await stripe.createPaymentMethod({
                    type: 'card',
                    card: card,
                    billing_details: {
                        name: document.getElementById('firstName').value + ' ' + 
                             document.getElementById('lastName').value,
                        email: document.getElementById('email').value,
                        address: {
                            line1: document.getElementById('address').value,
                            city: document.getElementById('city').value,
                            state: document.getElementById('state').value,
                            postal_code: document.getElementById('zip').value
                        }
                    }
                });
                
                if (error) {
                    throw error;
                }
                
                const response = await fetch('/process_payment.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        payment_method_id: paymentMethod.id,
                        shipping_details: {
                            first_name: document.getElementById('firstName').value,
                            last_name: document.getElementById('lastName').value,
                            email: document.getElementById('email').value,
                            address: document.getElementById('address').value,
                            city: document.getElementById('city').value,
                            state: document.getElementById('state').value,
                            zip: document.getElementById('zip').value
                        }
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    window.location.href = 'order_confirmation.php?order_id=' + result.order_id;
                } else {
                    throw new Error(result.message);
                }
                
            } catch (error) {
                const errorElement = document.getElementById('card-errors');
                errorElement.textContent = error.message;
                errorElement.classList.remove('d-none');
                submitButton.disabled = false;
                submitButton.textContent = 'Pay $<?php echo number_format($total, 2); ?>';
            }
        });
    </script>
</body>
</html> 