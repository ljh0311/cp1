<?php
// router.php
if (preg_match('/\.(?:png|jpg|jpeg|gif|css|js)$/', $_SERVER["REQUEST_URI"])) {
    return false; // Serve the requested file as-is
} else {
    // URL cleanup
    $url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $url = ltrim($url, '/');
    
    // Default to index.php if no file specified
    if (empty($url)) {
        $url = 'index.php';
    }
    
    // Check if the PHP file exists
    if (file_exists($url) && is_file($url)) {
        require $url;
    } else {
        // Handle 404
        header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
        echo '404 - Page not found';
    }
}
?> 