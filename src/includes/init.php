<?php
// Define root path if not already defined
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

// Common includes
require_once ROOT_PATH . '/inc/config.php';
require_once ROOT_PATH . '/inc/session_config.php';
require_once ROOT_PATH . '/inc/ErrorHandler.php';
require_once ROOT_PATH . '/database/DatabaseManager.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Function to check admin access
function requireAdmin() {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        header('Location: /login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit();
    }
}

// Function to check user login
function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit();
    }
} 