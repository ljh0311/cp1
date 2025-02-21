<?php
// Only set session parameters if session hasn't started
if (session_status() == PHP_SESSION_NONE) {
    // Create sessions directory if it doesn't exist
    $sessionPath = dirname(__DIR__) . '/sessions';
    if (!file_exists($sessionPath)) {
        mkdir($sessionPath, 0777, true);
    }

    // Set session parameters
    ini_set('session.save_path', $sessionPath);
    ini_set('session.gc_maxlifetime', 7200); // 2 hours
    ini_set('session.cookie_lifetime', 7200); // 2 hours
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 0); // Set to 0 for HTTP
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_path', '/');
    
    // Start session if not already started
    session_start();
    
    // Regenerate session ID periodically for security
    if (!isset($_SESSION['last_regeneration']) || 
        time() - $_SESSION['last_regeneration'] > 300) { // 5 minutes
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
} 