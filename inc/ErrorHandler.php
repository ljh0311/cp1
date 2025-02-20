<?php
class ErrorHandler {
    private static $errors = [];
    private static $hasDbConnection = false;
    
    public static function setDbStatus($status) {
        self::$hasDbConnection = $status;
    }
    
    public static function hasDbConnection() {
        return self::$hasDbConnection;
    }
    
    public static function addError($error) {
        self::$errors[] = $error;
    }
    
    public static function getErrors() {
        return self::$errors;
    }
    
    public static function hasErrors() {
        return !empty(self::$errors);
    }
    
    public static function displayErrors() {
        if (self::hasErrors()) {
            foreach (self::$errors as $error) {
                echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                        ' . htmlspecialchars($error) . '
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                      </div>';
            }
        }
    }
    
    public static function handleException($e) {
        error_log($e->getMessage());
        
        if (DEBUG_MODE) {
            self::addError($e->getMessage());
        } else {
            self::addError('An unexpected error occurred. Please try again later.');
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
} 