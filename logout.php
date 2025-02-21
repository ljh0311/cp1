<?php
require_once 'inc/config.php';
require_once 'inc/session_start.php';
require_once 'inc/ErrorHandler.php';
require_once 'database/DatabaseManager.php';

// Clear remember-me token if it exists
if (isset($_COOKIE['remember_token'])) {
    try {
        $db = DatabaseManager::getInstance();
        $db->query("DELETE FROM remember_tokens WHERE token = :token", 
                  [':token' => $_COOKIE['remember_token']]);
    } catch (Exception $e) {
        ErrorHandler::logError("Failed to delete remember token: " . $e->getMessage());
    }
    
    // Delete the cookie
    setcookie('remember_token', '', time() - 3600, '/', '', true, true);
}

// Log the logout
if (isset($_SESSION['username'])) {
    ErrorHandler::logError("User {$_SESSION['username']} logged out", __FILE__, __LINE__, 'INFO');
}

// Clear all session data
$_SESSION = array();

// Delete the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Destroy the session
session_destroy();

// Redirect to login page
header('Location: login.php');
exit; 