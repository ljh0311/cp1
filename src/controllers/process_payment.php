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

    // Get user ID
    $user_id = $sessionManager->getUserId();
    if (!$user_id) {
        error_log('Payment Error: No user ID found in session');
        sendJsonResponse(false, 'Session error: No user ID found.');
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

    try {
        $db = DatabaseManager::getInstance();
        
        // Test the connection with a simple query
        $test = $db->query("SELECT 1");
        if (!$test) {
            error_log('Payment Error: Database connection test failed');
            throw new Exception('Database connection test failed');
        }
    } catch (Exception $e) {
        error_log('Payment Error: Database connection failed - ' . $e->getMessage());
        sendJsonResponse(false, 'Database connection failed. Please try again later.');
    }
    
    try {
        error_log('Payment Debug: Starting payment process for user ' . $user_id);
        
        // First check if user has items in cart
        $cart_check = $db->query(
            "SELECT COUNT(*) as count FROM cart_items WHERE user_id = ?",
            [$user_id]
        );
        
        if (!$cart_check) {
            error_log('Payment Error: Failed to check cart items');
            throw new Exception('Failed to check cart items');
        }
        
        $count = $db->fetch($cart_check);
        if ($count['count'] == 0) {
            error_log('Payment Error: Cart is empty for user ' . $user_id);
            sendJsonResponse(false, 'Your cart is empty.');
        }
        
        // Get cart items
        error_log('Payment Debug: Fetching cart items');
        $cart_items = $db->query(
            "SELECT ci.*, b.title, b.price, b.stock_quantity as stock 
             FROM cart_items ci 
             INNER JOIN books b ON ci.book_id = b.book_id 
             WHERE ci.user_id = ?",
            [$user_id]
        );
        
        if (!$cart_items) {
            error_log('Payment Error: Cart query failed for user ' . $user_id);
            throw new Exception('Failed to retrieve cart items');
        }
        
        $items = $db->fetchAll($cart_items);
        error_log('Payment Debug: Found ' . count($items) . ' items in cart');
        
        if (empty($items)) {
            sendJsonResponse(false, 'Cart is empty.');
        }
        
        // Calculate total and validate stock
        $total = 0;
        foreach ($items as $item) {
            if ($item['stock'] < $item['quantity']) {
                error_log('Payment Error: Insufficient stock for book ID ' . $item['book_id']);
                sendJsonResponse(false, "Insufficient stock for {$item['title']}.");
            }
            $total += $item['price'] * $item['quantity'];
        }
        error_log('Payment Debug: Total calculated: ' . $total);
        
        // Validate credit card
        $card_number = $data['payment_method_id'];
        if (!preg_match('/^\d{16}$/', $card_number)) {
            error_log('Payment Error: Invalid card number format');
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
                error_log('Payment Error: Invalid card type: ' . $first_digit);
                sendJsonResponse(false, 'Invalid card type. Must start with 4 (Visa), 5 (Mastercard), or 6 (AMEX).');
        }
        
        error_log('Payment Debug: Starting transaction');
        // Process order
        $result = $db->query("START TRANSACTION");
        if (!$result) {
            error_log('Payment Error: Failed to start transaction');
            throw new Exception('Failed to start transaction');
        }
        
        try {
            // Create order
            error_log('Payment Debug: Creating order');
            $shipping_json = json_encode($data['shipping_details']);
            error_log('Payment Debug: Shipping details: ' . $shipping_json);
            
            $result = $db->query(
                "INSERT INTO orders (user_id, total_amount, status) 
                 VALUES (?, ?, ?)",
                [
                    $user_id,
                    $total,
                    'pending'
                ]
            );
            
            if (!$result) {
                error_log('Payment Error: Failed to create order - Query failed');
                throw new Exception('Failed to create order');
            }
            
            $order_id = $db->lastInsertId();
            if (!$order_id) {
                error_log('Payment Error: Failed to get last insert ID');
                throw new Exception('Failed to get order ID');
            }
            error_log('Payment Debug: Order created with ID: ' . $order_id);
            
            // Create order items and update stock
            foreach ($items as $item) {
                error_log('Payment Debug: Processing item ' . $item['book_id']);
                $result = $db->query(
                    "INSERT INTO order_items (order_id, book_id, quantity, price_at_time) 
                     VALUES (?, ?, ?, ?)",
                    [$order_id, $item['book_id'], $item['quantity'], $item['price']]
                );
                
                if (!$result) {
                    error_log('Payment Error: Failed to create order item for book ID ' . $item['book_id']);
                    throw new Exception('Failed to create order item');
                }
                
                $result = $db->query(
                    "UPDATE books SET stock_quantity = stock_quantity - ? WHERE book_id = ?",
                    [$item['quantity'], $item['book_id']]
                );
                
                if (!$result) {
                    error_log('Payment Error: Failed to update stock for book ID ' . $item['book_id']);
                    throw new Exception('Failed to update book stock');
                }
            }
            
            // Clear cart
            error_log('Payment Debug: Clearing cart');
            $result = $db->query(
                "DELETE FROM cart_items WHERE user_id = ?",
                [$user_id]
            );
            
            if (!$result) {
                error_log('Payment Error: Failed to clear cart');
                throw new Exception('Failed to clear cart');
            }
            
            // Update order status
            error_log('Payment Debug: Updating order status');
            $result = $db->query(
                "UPDATE orders SET status = ? WHERE order_id = ?",
                ['completed', $order_id]
            );
            
            if (!$result) {
                error_log('Payment Error: Failed to update order status');
                throw new Exception('Failed to update order status');
            }
            
            // Commit the transaction
            error_log('Payment Debug: Committing transaction');
            $result = $db->query("COMMIT");
            if (!$result) {
                error_log('Payment Error: Failed to commit transaction');
                throw new Exception('Failed to commit transaction');
            }
            
            // Store success message
            $sessionManager->setFlash('success', 'Order placed successfully!');
            $_SESSION['last_order_id'] = $order_id;
            
            // Make sure session is saved
            session_write_close();
            
            error_log('Payment Debug: Payment successful for order ID: ' . $order_id);
            sendJsonResponse(true, 'Payment successful!', ['order_id' => $order_id]);
            
        } catch (Exception $e) {
            error_log('Payment Error: Transaction operation failed - ' . $e->getMessage());
            $db->query("ROLLBACK");
            throw $e;
        }
        
    } catch (Exception $e) {
        error_log('Payment Error: Transaction failed - ' . $e->getMessage());
        sendJsonResponse(false, 'Database error: ' . $e->getMessage());
    }
    
} catch (Exception $e) {
    error_log('Payment Error: ' . $e->getMessage());
    sendJsonResponse(false, 'An unexpected error occurred. Please try again later.');
} 