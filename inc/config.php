<?php
// Define root path if not already defined
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

// Session settings - must be set before any session starts
if (session_status() == PHP_SESSION_NONE) {
    // Create sessions directory if it doesn't exist
    $sessionPath = ROOT_PATH . '/sessions';
    if (!file_exists($sessionPath)) {
        mkdir($sessionPath, 0777, true);
    }
    
    // Set session configurations
    ini_set('session.save_path', $sessionPath);
    ini_set('session.gc_maxlifetime', 3600);
    ini_set('session.cookie_lifetime', 3600);
}

// Constants
define('SESSION_LIFETIME', 3600); // 1 hour
define('DEBUG_MODE', true);

// Database settings
define('DB_HOST', 'database1.czsa24cac7y5.us-east-1.rds.amazonaws.com');
define('DB_NAME', 'tutoring_system');
define('DB_USER', 'admin');
define('DB_PASS', 'KappyAdmin');

// Application settings
define('SITE_NAME', 'BookStore');
define('SITE_URL', isset($_SERVER['HTTP_HOST']) ? 
    ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://") . $_SERVER['HTTP_HOST']) : 
    'http://localhost:8000'
);
define('ADMIN_EMAIL', 'admin@example.com');

// File upload settings
define('UPLOAD_DIR', ROOT_PATH . '/uploads');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif']);

// Error reporting
if (DEBUG_MODE) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
}

// Time zone
date_default_timezone_set('Asia/Singapore');

// Create required directories
$directories = [
    'sessions',
    'uploads',
    'uploads/books',
    'uploads/avatars',
    'logs'
];

foreach ($directories as $dir) {
    $path = ROOT_PATH . '/' . $dir;
    if (!file_exists($path)) {
        mkdir($path, 0777, true);
    }
}

// Initialize error logging
ini_set('log_errors', 1);
ini_set('error_log', ROOT_PATH . '/logs/php_errors.log'); 