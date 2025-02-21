<?php
spl_autoload_register(function ($class) {
    // Define the base directories to search for classes
    $directories = [
        __DIR__ . '/',              // inc directory
        __DIR__ . '/../database/',  // database directory
        __DIR__ . '/../models/',    // models directory if you have one
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