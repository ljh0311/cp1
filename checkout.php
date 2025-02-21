<?php
// Define root path if not already defined
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', __DIR__);
}

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load configuration and start session BEFORE any output
require_once 'inc/config.php';
require_once 'inc/session_config.php';
require_once ROOT_PATH . '/database/DatabaseManager.php';
require_once ROOT_PATH . '/inc/SessionManager.php';

// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
                            <div class="card">
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label for="card_number" class="form-label">Card Number</label>
                                        <input type="text" class="form-control" id="card_number" maxlength="16" required 
                               placeholder="Enter your 16-digit card number">
                                        <div class="form-text">Visa (starts with 4), Mastercard (starts with 5), or AMEX (starts with 6)</div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="expiry_date" class="form-label">Expiry Date</label>
                                            <input type="text" class="form-control" id="expiry_date" 
                                   placeholder="MM/YY" maxlength="5" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="cvv" class="form-label">CVV</label>
                                            <input type="text" class="form-control" id="cvv" 
                                   placeholder="123" maxlength="3" required>
                                        </div>
                                    </div>
                                    <div id="card-errors" class="alert alert-danger d-none" role="alert"></div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 mt-4" id="submit-button">
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
        // Handle form submission
        const form = document.getElementById('payment-form');
        const submitButton = document.getElementById('submit-button');
        const cardErrors = document.getElementById('card-errors');
        const cardNumberInput = document.getElementById('card_number');
        const expiryDateInput = document.getElementById('expiry_date');
        const cvvInput = document.getElementById('cvv');

        // Format expiry date as MM/YY
        expiryDateInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 2) {
                value = value.slice(0, 2) + '/' + value.slice(2);
            }
            e.target.value = value;
        });

        // Only allow numbers in card number input
        cardNumberInput.addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/\D/g, '');
        });

        // Only allow numbers in CVV
        cvvInput.addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/\D/g, '');
        });
        
        form.addEventListener('submit', async function(event) {
            event.preventDefault();
            
            submitButton.disabled = true;
            submitButton.textContent = 'Processing...';
            cardErrors.classList.add('d-none');

            // Validate card number
            const cardNumber = cardNumberInput.value.replace(/\s/g, '');
            if (cardNumber.length !== 16) {
                cardErrors.textContent = 'Card number must be 16 digits';
                cardErrors.classList.remove('d-none');
                submitButton.disabled = false;
                submitButton.textContent = 'Pay $<?php echo number_format($total, 2); ?>';
                return;
            }

            const firstDigit = cardNumber.charAt(0);
            let cardType = '';
            if (firstDigit === '4') {
                cardType = 'Visa';
            } else if (firstDigit === '5') {
                cardType = 'Mastercard';
            } else if (firstDigit === '6') {
                cardType = 'AMEX';
            } else {
                cardErrors.textContent = 'Invalid card type. Must start with 4 (Visa), 5 (Mastercard), or 6 (AMEX)';
                cardErrors.classList.remove('d-none');
                submitButton.disabled = false;
                submitButton.textContent = 'Pay $<?php echo number_format($total, 2); ?>';
                return;
            }
            
            const formData = {
                payment_method_id: cardNumber,
                shipping_details: {
                    first_name: document.getElementById('firstName').value,
                    last_name: document.getElementById('lastName').value,
                    email: document.getElementById('email').value,
                    address: document.getElementById('address').value,
                    city: document.getElementById('city').value,
                    state: document.getElementById('state').value,
                    zip: document.getElementById('zip').value
                }
            };

            try {
                const response = await fetch('process_payment.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify(formData)
                });

                let result;
                const responseText = await response.text();
                
                try {
                    result = JSON.parse(responseText);
                } catch (e) {
                    console.error('Server response:', responseText);
                    throw new Error('Invalid server response');
                }

                if (result.success) {
                    window.location.href = 'order_confirmation.php?order_id=' + result.order_id;
                } else {
                    throw new Error(result.message || 'Payment failed');
                }
                
            } catch (error) {
                console.error('Payment Error:', error);
                cardErrors.textContent = error.message || 'An error occurred while processing your payment.';
                cardErrors.classList.remove('d-none');
                submitButton.disabled = false;
                submitButton.textContent = 'Pay $<?php echo number_format($total, 2); ?>';
            }
        });
    </script>
</body>
</html> 