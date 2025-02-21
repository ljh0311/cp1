<?php
require_once 'inc/config.php';
require_once 'inc/session_config.php';
require_once 'vendor/autoload.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please log in to continue.']);
    exit();
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit();
}

// Get JSON data
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data || !isset($data['payment_method_id']) || !isset($data['shipping_details'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request data.']);
    exit();
}

try {
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
    $payment_intent = (object) [
        'id' => 'MOCK_' . time() . '_' . rand(1000,9999)
    ];
    
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
        ['paid', $payment_intent->id, $order_id]
    );
    
    // Commit transaction
    $db->commit();
    
    echo json_encode([
        'success' => true,
        'order_id' => $order_id,
        'message' => 'Payment successful!'
    ]);
    
} catch (\Stripe\Exception\CardException $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    ErrorHandler::logError($e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while processing your payment.'
    ]);
} 