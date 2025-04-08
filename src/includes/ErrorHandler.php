<?php
class ErrorHandler {
    private static $errors = [];
    private static $hasDbConnection = false;
    private static $logFile = 'error.log';
    
    public static function setDbStatus($status) {
        self::$hasDbConnection = $status;
    }
    
    public static function hasDbConnection() {
        return self::$hasDbConnection;
    }
    
    public static function setError($message) {
        self::$errors[] = $message;
        error_log($message . "\n", 3, self::$logFile);
    }
    
    public static function getErrors() {
        return self::$errors;
    }
    
    public static function hasErrors() {
        return !empty(self::$errors);
    }
    
    public static function clearErrors() {
        self::$errors = [];
    }
    
    public static function displayErrors() {
        if (self::hasErrors()) {
            foreach (self::$errors as $error) {
                echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
                echo htmlspecialchars($error);
                echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
                echo '</div>';
            }
            self::clearErrors();
        }
    }
    
    public static function handleException($e) {
        error_log($e->getMessage());
        
        if (DEBUG_MODE) {
            self::setError($e->getMessage());
        } else {
            self::setError('An unexpected error occurred. Please try again later.');
        }
    }
    
    public static function getFallbackData($type) {
        switch ($type) {
            case 'featured_books':
                return [
                    [
                        'book_id' => 1,
                        'title' => 'Sample Book 1',
                        'author' => 'Author 1',
                        'price' => 29.99,
                        'image_url' => 'images/books/placeholder.jpg'
                    ],
                    [
                        'book_id' => 2,
                        'title' => 'Sample Book 2',
                        'author' => 'Author 2',
                        'price' => 24.99,
                        'image_url' => 'images/books/placeholder.jpg'
                    ],
                ];
            case 'categories':
                return [
                    'Fiction' => 'images/categories/fiction.jpg',
                    'Non-Fiction' => 'images/categories/non-fiction.jpg',
                    'Academic' => 'images/categories/academic.jpg',
                    'Children' => 'images/categories/children.jpg'
                ];
            default:
                return [];
        }
    }
    
    public static function logError($message, $file = null, $line = null) {
        $logMessage = date('[Y-m-d H:i:s] ') . $message;
        if ($file && $line) {
            $logMessage .= " in $file on line $line";
        }
        error_log($logMessage . "\n", 3, self::$logFile);
    }
} 