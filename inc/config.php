<?php
// Load session configuration first
require_once __DIR__ . '/session_start.php';

// Define root path if not already defined
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

// Constants
define('SESSION_LIFETIME', 3600); // 1 hour
define('DEBUG_MODE', true);

// Database settings
define('DB_HOST', '3.88.232.167');  // Your provided IP address
define('DB_NAME', 'book-db');
define('DB_USER', 'bookadmin');
define('DB_PASS', 'BookAdmin395');

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
$required_dirs = [
    'uploads',
    'uploads/books',
    'uploads/avatars',
    'logs'
];

foreach ($required_dirs as $dir) {
    $path = ROOT_PATH . '/' . $dir;
    if (!file_exists($path)) {
        mkdir($path, 0777, true);
    }
}

// Initialize error logging
ini_set('log_errors', 1);
ini_set('error_log', ROOT_PATH . '/logs/php_errors.log'); 