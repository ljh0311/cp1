<?php
// Session configuration - must be included before any session starts
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

$sessionPath = ROOT_PATH . '/sessions';
if (!file_exists($sessionPath)) {
    mkdir($sessionPath, 0777, true);
}

if (session_status() == PHP_SESSION_NONE) {
    ini_set('session.save_path', $sessionPath);
    ini_set('session.gc_maxlifetime', 3600);
    ini_set('session.cookie_lifetime', 3600);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
    ini_set('session.use_only_cookies', 1);
} 