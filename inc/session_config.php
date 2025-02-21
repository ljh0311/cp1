<?php
// Define session save path relative to the project root
$sessionPath = dirname(__DIR__) . '/sessions';

// Create sessions directory if it doesn't exist
if (!file_exists($sessionPath)) {
    mkdir($sessionPath, 0777, true);
}

// Set session parameters before starting the session
ini_set('session.save_handler', 'files');
ini_set('session.save_path', $sessionPath);
ini_set('session.gc_maxlifetime', 7200); // 2 hours
ini_set('session.cookie_lifetime', 7200); // 2 hours
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS'])); // Secure if HTTPS
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_path', '/');
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.gc_probability', 1);
ini_set('session.gc_divisor', 100);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Regenerate session ID periodically for security
if (!isset($_SESSION['last_regeneration']) || 
    time() - $_SESSION['last_regeneration'] > 300) { // 5 minutes
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}

// Set session cookie parameters
session_set_cookie_params([
    'lifetime' => 7200,
    'path' => '/',
    'domain' => '',
    'secure' => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Lax'
]);

// Debug session if needed
if (defined('DEBUG_MODE') && DEBUG_MODE) {
    error_log("Session status: " . session_status());
    error_log("Session ID: " . session_id());
    error_log("Session save path: " . session_save_path());
    error_log("Session data: " . print_r($_SESSION, true));
} 