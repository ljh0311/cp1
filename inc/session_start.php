<?php
// This file must be included first, before any other includes
if (session_status() === PHP_SESSION_NONE) {
    // Set session settings before starting the session
    ini_set('session.save_path', dirname(__DIR__) . '/sessions');
    ini_set('session.gc_maxlifetime', 3600);
    ini_set('session.cookie_lifetime', 3600);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
    ini_set('session.use_only_cookies', 1);
    
    // Start the session
    session_start();
} 