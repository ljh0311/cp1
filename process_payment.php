<?php
ob_clean(); // Clear any previous output
// Prevent any output
error_reporting(0);
ini_set('display_errors', 0);
ini_set('html_errors', 0);

// Set JSON header first
header('Content-Type: application/json');

// Define root path if not already defined
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', __DIR__);
}

try {
    require_once 'inc/config.php';
    require_once 'inc/session_config.php';
    require_once ROOT_PATH . '/inc/SessionManager.php';
    require_once ROOT_PATH . '/database/DatabaseManager.php';
    require_once 'vendor/autoload.php';

    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Get session manager instance
    $sessionManager = SessionManager::getInstance();

    // Check if user is logged in using SessionManager
    if (!$sessionManager->isLoggedIn()) {
        throw new Exception('Please log in to continue.');
    }

    // Update last activity time
    $_SESSION['LAST_ACTIVITY'] = time();

    // Check if request method is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method.');
    }

    // Get JSON data
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!$data || !isset($data['payment_method_id']) || !isset($data['shipping_details'])) {
        throw new Exception('Invalid request data.');
    }

    $db = DatabaseManager::getInstance();
    
    // Get cart items
    $cart_items = $db->query(
        "SELECT ci.*, b.title, b.price, b.stock 
         FROM cart_items ci 
         JOIN books b ON ci.book_id = b.book_id 
         WHERE ci.user_id = ?",
        [$_SESSION['user_id']]
    );
    
    $items = $db->fetchAll($cart_items);
    
    if (empty($items)) {
        throw new Exception('Cart is empty.');
    }
    
    // Calculate total
    $total = 0;
    foreach ($items as $item) {
        // Check stock
        if ($item['stock'] < $item['quantity']) {
            throw new Exception("Insufficient stock for {$item['title']}.");
        }
        $total += $item['price'] * $item['quantity'];
    }
    
    // Validate credit card number
    $card_number = $data['payment_method_id'];
    if (!preg_match('/^\d{16}$/', $card_number)) {
        throw new Exception('Invalid card number format.');
    }
    
    $first_digit = substr($card_number, 0, 1);
    
    // Check card type based on first digit
    $valid_card = false;
    $card_type = '';
    switch($first_digit) {
        case '4':
            $valid_card = true;
            $card_type = 'Visa';
            break;
        case '5':
            $valid_card = true; 
            $card_type = 'Mastercard';
            break;
        case '6':
            $valid_card = true;
            $card_type = 'AMEX';
            break;
        default:
            throw new Exception('Invalid card type. Must be Visa, Mastercard or AMEX.');
    }
    
    if (!$valid_card) {
        throw new Exception('Invalid credit card number.');
    }
    
    // Mock payment intent ID for order tracking
    $payment_intent = 'MOCK_' . time() . '_' . rand(1000,9999);
    
    // Start transaction
    $db->beginTransaction();
    
    // Create order
    $db->query(
        "INSERT INTO orders (user_id, total_amount, status, shipping_address, created_at) 
         VALUES (?, ?, ?, ?, NOW())",
        [
            $_SESSION['user_id'],
            $total,
            'pending',
            json_encode($data['shipping_details'])
        ]
    );
    
    $order_id = $db->lastInsertId();
    
    // Create order items and update stock
    foreach ($items as $item) {
        // Add order item
        $db->query(
            "INSERT INTO order_items (order_id, book_id, quantity, price) 
             VALUES (?, ?, ?, ?)",
            [
                $order_id,
                $item['book_id'],
                $item['quantity'],
                $item['price']
            ]
        );
        
        // Update stock
        $db->query(
            "UPDATE books SET stock = stock - ? WHERE book_id = ?",
            [$item['quantity'], $item['book_id']]
        );
    }
    
    // Clear cart
    $db->query(
        "DELETE FROM cart_items WHERE user_id = ?",
        [$_SESSION['user_id']]
    );
    
    // Update order status
    $db->query(
        "UPDATE orders SET status = ?, payment_intent_id = ? WHERE order_id = ?",
        ['paid', $payment_intent, $order_id]
    );
    
    // Commit transaction
    $db->commit();
    
    // Store success message and order ID in session
    $sessionManager->setFlash('success', 'Order placed successfully!');
    $_SESSION['last_order_id'] = $order_id;
    
    // Make sure session data is written
    session_write_close();
    
    echo json_encode([
        'success' => true,
        'order_id' => $order_id,
        'message' => 'Payment successful!'
    ]);
    
} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    
    error_log('Payment Error: ' . $e->getMessage());
    
    die(json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]));
} 