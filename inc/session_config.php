<?php
// Define session save path relative to the project root
$sessionPath = dirname(__DIR__) . '/sessions';

// Create sessions directory if it doesn't exist with proper permissions
if (!file_exists($sessionPath)) {
    mkdir($sessionPath, 0733, true);
    chmod($sessionPath, 0733); // Ensure proper permissions
}

// Ensure session directory is writable
if (!is_writable($sessionPath)) {
    error_log("Session directory is not writable: $sessionPath");
    // Try to fix permissions
    chmod($sessionPath, 0733);
}

// Set session handler and path
ini_set('session.save_handler', 'files');
ini_set('session.save_path', $sessionPath);

// Security settings
ini_set('session.use_strict_mode', 1);
ini_set('session.use_cookies', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.gc_maxlifetime', 7200); // 2 hours
ini_set('session.cookie_lifetime', 7200); // 2 hours

// Session garbage collection
ini_set('session.gc_probability', 1);
ini_set('session.gc_divisor', 100);

// Set session name
session_name('bookstore_session');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Session security measures
if (!isset($_SESSION['created'])) {
    $_SESSION['created'] = time();
} elseif (time() - $_SESSION['created'] > 7200) {
    // Regenerate session ID every 2 hours
    session_regenerate_id(true);
    $_SESSION['created'] = time();
}

// Check for session hijacking
if (isset($_SESSION['user_agent'])) {
    if ($_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
        // Possible session hijacking attempt
        session_destroy();
        session_start();
        $_SESSION['error'] = 'Security violation detected. Please login again.';
        header('Location: /login.php');
        exit();
    }
} else {
    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
}

// Debug session if needed
if (defined('DEBUG_MODE') && DEBUG_MODE) {
    error_log("Session status: " . session_status());
    error_log("Session ID: " . session_id());
    error_log("Session save path: " . session_save_path());
    error_log("Session data: " . print_r($_SESSION, true));
} 