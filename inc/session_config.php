<?php
// Define session save path relative to the project root
$sessionPath = '/tmp/sessions';

// Create sessions directory if it doesn't exist
if (!file_exists($sessionPath)) {
    mkdir($sessionPath, 0777, true);
}

// Get the current domain
$domain = $_SERVER['HTTP_HOST'] ?? '';
$domain = preg_replace('/:\d+$/', '', $domain); // Remove port number if present

// Set session cookie parameters
session_set_cookie_params([
    'lifetime' => 7200, // 2 hours
    'path' => '/',
    'domain' => $domain,
    'secure' => false, // Set to true if using HTTPS
    'httponly' => true,
    'samesite' => 'Lax'
]);

// Set session name
session_name('BOOKSTORE_SESSID');

// Set session parameters
ini_set('session.save_handler', 'files');
ini_set('session.save_path', $sessionPath);
ini_set('session.gc_maxlifetime', 7200);
ini_set('session.cookie_lifetime', 7200);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Lax');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Debug session if needed
if (defined('DEBUG_MODE') && DEBUG_MODE) {
    error_log("=== Session Config Debug ===");
    error_log("Session Name: " . session_name());
    error_log("Session ID: " . session_id());
    error_log("Session Cookie Params: " . print_r(session_get_cookie_params(), true));
    error_log("Session Status: " . session_status());
    error_log("Session Data: " . print_r($_SESSION, true));
    error_log("=== End Session Config Debug ===");
}

// Update last activity time
if (isset($_SESSION['user_id'])) {
    $_SESSION['last_activity'] = time();
}

// Check session expiration
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 7200)) {
    session_unset();
    session_destroy();
    header('Location: /login.php');
    exit;
}

// Regenerate session ID periodically for security
if (!isset($_SESSION['last_regeneration']) || 
    time() - $_SESSION['last_regeneration'] > 300) {
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
} 