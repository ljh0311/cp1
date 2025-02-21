<?php
// Suppress all errors and warnings
error_reporting(0);
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
ini_set('html_errors', 0);

// Buffer control
ob_start();
if (ob_get_level()) {
    ob_end_clean();
}

// Set JSON header
header('Content-Type: application/json; charset=utf-8');

// Define root path if not already defined
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', __DIR__);
}

function sendJsonResponse($success, $message, $data = []) {
    $response = array_merge([
        'success' => $success,
        'message' => $message
    ], $data);
    
    die(json_encode($response));
}

try {
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
        sendJsonResponse(false, 'Please log in to continue.');
    }

    // Update last activity time
    $_SESSION['LAST_ACTIVITY'] = time();

    // Check request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendJsonResponse(false, 'Invalid request method.');
    }

    // Get and validate JSON data
    $jsonInput = file_get_contents('php://input');
    if (empty($jsonInput)) {
        sendJsonResponse(false, 'No data received.');
    }

    $data = json_decode($jsonInput, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        sendJsonResponse(false, 'Invalid JSON data: ' . json_last_error_msg());
    }

    if (!isset($data['payment_method_id']) || !isset($data['shipping_details'])) {
        sendJsonResponse(false, 'Missing required fields.');
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
        sendJsonResponse(false, 'Cart is empty.');
    }
    
    // Calculate total and validate stock
    $total = 0;
    foreach ($items as $item) {
        if ($item['stock'] < $item['quantity']) {
            sendJsonResponse(false, "Insufficient stock for {$item['title']}.");
        }
        $total += $item['price'] * $item['quantity'];
    }
    
    // Validate credit card
    $card_number = $data['payment_method_id'];
    if (!preg_match('/^\d{16}$/', $card_number)) {
        sendJsonResponse(false, 'Card number must be 16 digits.');
    }
    
    $first_digit = substr($card_number, 0, 1);
    switch($first_digit) {
        case '4':
            $card_type = 'Visa';
            break;
        case '5':
            $card_type = 'Mastercard';
            break;
        case '6':
            $card_type = 'AMEX';
            break;
        default:
            sendJsonResponse(false, 'Invalid card type. Must start with 4 (Visa), 5 (Mastercard), or 6 (AMEX).');
    }
    
    // Process order
    try {
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
            $db->query(
                "INSERT INTO order_items (order_id, book_id, quantity, price) 
                 VALUES (?, ?, ?, ?)",
                [$order_id, $item['book_id'], $item['quantity'], $item['price']]
            );
            
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
        $payment_intent = 'MOCK_' . time() . '_' . rand(1000,9999);
        $db->query(
            "UPDATE orders SET status = ?, payment_intent_id = ? WHERE order_id = ?",
            ['paid', $payment_intent, $order_id]
        );
        
        $db->commit();
        
        // Store success message
        $sessionManager->setFlash('success', 'Order placed successfully!');
        $_SESSION['last_order_id'] = $order_id;
        
        // Make sure session is saved
        session_write_close();
        
        sendJsonResponse(true, 'Payment successful!', ['order_id' => $order_id]);
        
    } catch (Exception $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        throw $e;
    }
    
} catch (Exception $e) {
    error_log('Payment Error: ' . $e->getMessage());
    sendJsonResponse(false, $e->getMessage());
} 