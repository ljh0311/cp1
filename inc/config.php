<?php
// Define root path if not already defined
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

// Constants
define('DEBUG_MODE', true);

// Database settings
define('DB_HOST', 'cloudbookdb.czsa24cac7y5.us-east-1.rds.amazonaws.com');
define('DB_NAME', 'MyBookDB');
define('DB_USER', 'admin');
define('DB_PASS', 'admin123');
define('DB_CHARSET', 'utf8mb4');

// Application settings
define('SITE_NAME', 'Academic Book Haven');
define('SITE_URL', 'http://18.208.109.129'); // Change this to your domain
define('ADMIN_EMAIL', 'admin@example.com');

// File upload settings
define('UPLOAD_DIR', ROOT_PATH . '/uploads');
define('MAX_FILE_SIZE', 5242880); // 5MB
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'gif']);

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

// Session configuration
if (!defined('SESSION_NAME')) define('SESSION_NAME', 'bookstore_session');
if (!defined('SESSION_LIFETIME')) define('SESSION_LIFETIME', 7200); // 2 hours
if (!defined('SESSION_PATH')) define('SESSION_PATH', '/');
if (!defined('SESSION_SECURE')) define('SESSION_SECURE', false);
if (!defined('SESSION_HTTPONLY')) define('SESSION_HTTPONLY', true);

// Cart configuration
define('CART_TIMEOUT', 7200); // 2 hours
define('MAX_CART_ITEMS', 10);

// Pagination configuration
define('ITEMS_PER_PAGE', 12);

// Security configuration
define('PASSWORD_MIN_LENGTH', 8);
define('PASSWORD_MAX_LENGTH', 72); // Max length for bcrypt
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_TIMEOUT', 900); // 15 minutes

// Email configuration
define('SMTP_HOST', 'smtp.example.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your_email@example.com');
define('SMTP_PASS', 'your_smtp_password');
define('SMTP_FROM', 'noreply@example.com');
define('SMTP_FROM_NAME', SITE_NAME); 