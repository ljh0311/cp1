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

// Set cookie parameters BEFORE starting the session
session_set_cookie_params([
    'lifetime' => 7200,
    'path' => '/',
    'domain' => $domain, // Use the current domain
    'secure' => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Lax'
]);

// Set session name
session_name(SESSION_NAME);

// Set session parameters before starting the session
ini_set('session.save_handler', 'files');
ini_set('session.save_path', $sessionPath);
ini_set('session.gc_maxlifetime', 7200); // 2 hours
ini_set('session.cookie_lifetime', 7200); // 2 hours
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_path', '/');
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.gc_probability', 1);
ini_set('session.gc_divisor', 100);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Update last activity time
if (isset($_SESSION['user_id'])) {
    $_SESSION['last_activity'] = time();
}

// Check session expiration
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_LIFETIME)) {
    // Session has expired, destroy it
    session_unset();
    session_destroy();
    // Redirect to login page
    header('Location: /login.php');
    exit;
}

// Regenerate session ID periodically for security
if (!isset($_SESSION['last_regeneration']) || 
    time() - $_SESSION['last_regeneration'] > 300) { // 5 minutes
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}

// Debug session if needed
if (defined('DEBUG_MODE') && DEBUG_MODE) {
    error_log("Session status: " . session_status());
    error_log("Session ID: " . session_id());
    error_log("Session save path: " . session_save_path());
    error_log("Session domain: " . $domain);
    error_log("Session data: " . print_r($_SESSION, true));
} 