<?php
// Only configure session if it hasn't started yet
if (session_status() === PHP_SESSION_NONE) {
    // Set session save path to our sessions directory
    $session_path = __DIR__ . '/../sessions';
    if (!file_exists($session_path)) {
        mkdir($session_path, 0700, true);
    }
    session_save_path($session_path);

    // Set secure session parameters
    ini_set('session.gc_maxlifetime', 7200);  // 2 hours
    ini_set('session.cookie_lifetime', 7200);  // 2 hours
    ini_set('session.cookie_httponly', 1);     // Protect against XSS
    ini_set('session.use_only_cookies', 1);    // Force cookies only
    ini_set('session.cookie_samesite', 'Lax'); // Protect against CSRF
    ini_set('session.use_strict_mode', 1);     // Enforce strict session id validation
    ini_set('session.gc_probability', 1);
    ini_set('session.gc_divisor', 100);        // 1% chance of cleanup on each request

    // Custom session name for your application
    session_name('BOOKSTORE_SESSID');

    // Start session
    session_start();
}

// Set last activity time for session timeout
if (!isset($_SESSION['LAST_ACTIVITY'])) {
    $_SESSION['LAST_ACTIVITY'] = time();
}

// Check session expiration (2 hours)
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 7200)) {
    session_unset();
    session_destroy();
    header('Location: /login.php');
    exit();
}

// Update last activity time
$_SESSION['LAST_ACTIVITY'] = time();

// Regenerate session ID periodically (every 30 minutes)
if (!isset($_SESSION['CREATED'])) {
    $_SESSION['CREATED'] = time();
} else if (time() - $_SESSION['CREATED'] > 1800) {
    session_regenerate_id(true);
    $_SESSION['CREATED'] = time();
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