<?php
require_once 'inc/config.php';
require_once 'inc/session_config.php';
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
    setcookie('remember_token', '', time() - 3600, '/', '', isset($_SERVER['HTTPS']), true);
}

// Log the logout
if (isset($_SESSION['username'])) {
    error_log("User {$_SESSION['username']} logged out");
}

// Clear all session data
$_SESSION = array();

// Delete the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/', '', isset($_SERVER['HTTPS']), true);
}

// Destroy the session
session_destroy();

// Set flash message in a temporary cookie
setcookie('flash_message', 'You have been successfully logged out.', time() + 30, '/', '', isset($_SERVER['HTTPS']), true);

// Redirect to login page
header('Location: login.php');
exit; 