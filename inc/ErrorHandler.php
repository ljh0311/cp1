<?php
class ErrorHandler {
    private static $instance = null;
    private $errors = [];
    
    private function __construct() {}
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function addError($error) {
        $this->errors[] = $error;
    }
    
    public function hasErrors() {
        return !empty($this->errors);
    }
    
    public function getErrors() {
        return $this->errors;
    }
    
    public function clearErrors() {
        $this->errors = [];
    }
    
    public function displayErrors() {
        if ($this->hasErrors()) {
            echo '<div class="alert alert-danger" role="alert">';
            echo '<div class="error-heading"><span>&#9888;</span> Error</div>';
            echo '<ul class="error-list">';
            foreach ($this->errors as $error) {
                if (is_array($error)) {
                    foreach ($error as $singleError) {
                        echo "<li>" . htmlspecialchars($singleError) . "</li>";
                    }
                } else {
                    echo "<li>" . htmlspecialchars($error) . "</li>";
                }
            }
            echo '</ul>';
            echo '</div>';
        }
    }
    
    public function logError($error, $file = null, $line = null) {
        $logMessage = date('[Y-m-d H:i:s] ') . $error;
        if ($file && $line) {
            $logMessage .= " in $file on line $line";
        }
        error_log($logMessage . PHP_EOL, 3, "C:/Users/user/Documents/SITstuffs/php/php_error.log");
    }
} 