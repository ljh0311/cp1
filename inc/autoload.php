<?php
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

spl_autoload_register(function ($class) {
    // Define the directories to search for classes
    $directories = [
        ROOT_PATH . '/inc/',
        ROOT_PATH . '/database/',
        ROOT_PATH . '/models/',
        ROOT_PATH . '/controllers/'
    ];
    
    // Loop through directories
    foreach ($directories as $directory) {
        $file = $directory . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
}); 