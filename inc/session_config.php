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
    ini_set('session.gc_maxlifetime', 3600);
    ini_set('session.cookie_lifetime', 3600);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
    ini_set('session.use_only_cookies', 1);
}
?> 