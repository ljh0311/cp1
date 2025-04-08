<?php
class Validator {
    private static $instance = null;
    private $errorHandler;
    
    private function __construct() {
        $this->errorHandler = ErrorHandler::getInstance();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function sanitizeInput($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }
    
    public function validateEmail($email) {
        $email = $this->sanitizeInput($email);
        if (empty($email)) {
            $this->errorHandler->addError("Email is required.");
            return false;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errorHandler->addError("Invalid email format.");
            return false;
        }
        return $email;
    }
    
    public function validatePassword($password, $confirmPassword = null) {
        if (empty($password)) {
            $this->errorHandler->addError("Password is required.");
            return false;
        }
        
        if (strlen($password) < 12) {
            $this->errorHandler->addError("Password must be at least 12 characters long.");
            return false;
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            $this->errorHandler->addError("Password must contain at least one uppercase letter.");
            return false;
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            $this->errorHandler->addError("Password must contain at least one lowercase letter.");
            return false;
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            $this->errorHandler->addError("Password must contain at least one number.");
            return false;
        }
        
        if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
            $this->errorHandler->addError("Password must contain at least one special character.");
            return false;
        }
        
        if ($confirmPassword !== null && $password !== $confirmPassword) {
            $this->errorHandler->addError("Passwords do not match.");
            return false;
        }
        
        return $password;
    }
    
    public function validateUsername($username) {
        $username = $this->sanitizeInput($username);
        if (empty($username)) {
            $this->errorHandler->addError("Username is required.");
            return false;
        }
        
        if (strpos($username, ' ') !== false) {
            $this->errorHandler->addError("Username cannot contain spaces.");
            return false;
        }
        
        if (!preg_match('/^[a-zA-Z0-9]+$/', $username)) {
            $this->errorHandler->addError("Username can only contain letters and numbers.");
            return false;
        }
        
        return $username;
    }
    
    public function validateRequired($value, $fieldName) {
        $value = $this->sanitizeInput($value);
        if (empty($value)) {
            $this->errorHandler->addError("$fieldName is required.");
            return false;
        }
        return $value;
    }
    
    public function validateDate($date) {
        $date = $this->sanitizeInput($date);
        if (empty($date)) {
            $this->errorHandler->addError("Date is required.");
            return false;
        }
        
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $this->errorHandler->addError("Invalid date format. Use YYYY-MM-DD.");
            return false;
        }
        
        $dateTime = DateTime::createFromFormat('Y-m-d', $date);
        if (!$dateTime || $dateTime->format('Y-m-d') !== $date) {
            $this->errorHandler->addError("Invalid date.");
            return false;
        }
        
        return $date;
    }
    
    public function validateTime($time) {
        $time = $this->sanitizeInput($time);
        if (empty($time)) {
            $this->errorHandler->addError("Time is required.");
            return false;
        }
        
        if (!preg_match('/^\d{2}:\d{2}$/', $time)) {
            $this->errorHandler->addError("Invalid time format. Use HH:MM.");
            return false;
        }
        
        list($hours, $minutes) = explode(':', $time);
        if ($hours < 0 || $hours > 23 || $minutes < 0 || $minutes > 59) {
            $this->errorHandler->addError("Invalid time.");
            return false;
        }
        
        return $time;
    }
    
    public function validateNumber($number, $fieldName, $min = null, $max = null) {
        $number = $this->sanitizeInput($number);
        if (!is_numeric($number)) {
            $this->errorHandler->addError("$fieldName must be a number.");
            return false;
        }
        
        if ($min !== null && $number < $min) {
            $this->errorHandler->addError("$fieldName must be at least $min.");
            return false;
        }
        
        if ($max !== null && $number > $max) {
            $this->errorHandler->addError("$fieldName cannot exceed $max.");
            return false;
        }
        
        return $number;
    }
} 