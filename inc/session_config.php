<?php
// Define session save path - try system directory first, then fallback to local
$systemSessionPath = '/var/lib/php/sessions';
$localSessionPath = dirname(__DIR__) . '/sessions';

// Determine which session path to use
if (is_writable($systemSessionPath)) {
    $sessionPath = $systemSessionPath;
} else {
    $sessionPath = $localSessionPath;
    
    // Create local sessions directory if it doesn't exist
    if (!file_exists($sessionPath)) {
        @mkdir($sessionPath, 0733, true);
        @chmod($sessionPath, 0733);
    }
}

// Verify session directory is writable
if (!is_writable($sessionPath)) {
    error_log("ERROR: Session directory is not writable: $sessionPath");
    // Try to create a temporary session directory as last resort
    $tempPath = sys_get_temp_dir() . '/php_sessions';
    if (!file_exists($tempPath)) {
        @mkdir($tempPath, 0733, true);
    }
    if (is_writable($tempPath)) {
        $sessionPath = $tempPath;
    } else {
        error_log("CRITICAL: No writable session directory available!");
    }
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

// Set session name with unique prefix for your application
session_name('bookstore_' . substr(md5($_SERVER['HTTP_HOST']), 0, 6));

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

// Set session cookie parameters
session_set_cookie_params([
    'lifetime' => 7200,
    'path' => '/',
    'domain' => $_SERVER['HTTP_HOST'],
    'secure' => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Lax'
]);

// Debug session if needed
if (defined('DEBUG_MODE') && DEBUG_MODE) {
    error_log("Session Configuration:");
    error_log("Session Path: " . $sessionPath);
    error_log("Session Writable: " . (is_writable($sessionPath) ? 'Yes' : 'No'));
    error_log("Session Status: " . session_status());
    error_log("Session ID: " . session_id());
    error_log("Session Name: " . session_name());
    error_log("Session Data: " . print_r($_SESSION, true));
} 