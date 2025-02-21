<?php
// Session configuration - must be included before any session starts
$sessionPath = dirname(__DIR__) . '/sessions';
if (!file_exists($sessionPath)) {
    mkdir($sessionPath, 0777, true);
}

if (session_status() == PHP_SESSION_NONE) {
    ini_set('session.save_path', $sessionPath);
    ini_set('session.gc_maxlifetime', 3600);
    ini_set('session.cookie_lifetime', 3600);
} 