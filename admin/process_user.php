<?php
require_once '../inc/config.php';
require_once '../inc/session_config.php';
require_once '../database/DatabaseManager.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    http_response_code(403);
    die(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

header('Content-Type: application/json');

try {
    $db = DatabaseManager::getInstance();
    
    // Get user details
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get') {
        $user_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$user_id) {
            throw new Exception('Invalid user ID');
        }
        
        $user = $db->query(
            "SELECT user_id, username, email, full_name, status 
             FROM users 
             WHERE user_id = ? AND is_admin = 0",
            [$user_id]
        );
        
        $result = $db->fetch($user);
        if (!$result) {
            throw new Exception('User not found');
        }
        
        echo json_encode($result);
        exit;
    }
    
    // Handle POST requests
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'delete') {
            $user_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            if (!$user_id) {
                throw new Exception('Invalid user ID');
            }
            
            // Check if user exists and is not an admin
            $user = $db->query(
                "SELECT user_id FROM users WHERE user_id = ? AND is_admin = 0",
                [$user_id]
            );
            
            if (!$db->fetch($user)) {
                throw new Exception('User not found or cannot be deleted');
            }
            
            // Start transaction
            $db->beginTransaction();
            
            try {
                // Delete user's cart items
                $db->query(
                    "DELETE FROM cart_items WHERE user_id = ?",
                    [$user_id]
                );
                
                // Delete user's orders (and related order items via foreign key cascade)
                $db->query(
                    "DELETE FROM orders WHERE user_id = ?",
                    [$user_id]
                );
                
                // Finally, delete the user
                $db->query(
                    "DELETE FROM users WHERE user_id = ?",
                    [$user_id]
                );
                
                $db->commit();
                echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
            } catch (Exception $e) {
                $db->rollback();
                throw $e;
            }
        } else {
            // Update user status
            $user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
            $status = filter_input(INPUT_POST, 'status', FILTER_SANITIZE_STRING);
            
            if (!$user_id || !$status) {
                throw new Exception('Invalid user ID or status');
            }
            
            // Validate status
            $allowed_statuses = ['active', 'inactive', 'suspended'];
            if (!in_array($status, $allowed_statuses)) {
                throw new Exception('Invalid status');
            }
            
            // Update user status
            $db->query(
                "UPDATE users SET status = ? WHERE user_id = ? AND is_admin = 0",
                [$status, $user_id]
            );
            
            echo json_encode(['success' => true, 'message' => 'User status updated successfully']);
        }
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 