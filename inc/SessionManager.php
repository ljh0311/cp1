<?php
class SessionManager {
    private static $instance = null;
    
    private function __construct() {
        // Private constructor to prevent direct instantiation
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Check if user is logged in
     * @return bool
     */
    public function isLoggedIn() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
    
    /**
     * Get logged in user ID
     * @return int|null
     */
    public function getUserId() {
        return $_SESSION['user_id'] ?? null;
    }
    
    /**
     * Set user session
     * @param int $userId
     * @param array $userData Additional user data to store in session
     */
    public function setUser($userId, array $userData = []) {
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_data'] = $userData;
        $_SESSION['last_activity'] = time();
    }
    
    /**
     * Clear user session
     */
    public function clearUser() {
        unset($_SESSION['user_id']);
        unset($_SESSION['user_data']);
        unset($_SESSION['last_activity']);
    }
    
    /**
     * Check if session has expired
     * @param int $maxLifetime Maximum session lifetime in seconds
     * @return bool
     */
    public function hasExpired($maxLifetime = 7200) {
        if (!isset($_SESSION['last_activity'])) {
            return true;
        }
        
        if (time() - $_SESSION['last_activity'] > $maxLifetime) {
            $this->clearUser();
            return true;
        }
        
        $_SESSION['last_activity'] = time();
        return false;
    }
    
    /**
     * Require user to be logged in
     * @param string $redirectUrl URL to redirect to if not logged in
     */
    public function requireLogin($redirectUrl = 'login.php') {
        if (!$this->isLoggedIn() || $this->hasExpired()) {
            $current_url = urlencode($_SERVER['REQUEST_URI']);
            header("Location: $redirectUrl?redirect=$current_url");
            exit();
        }
    }
    
    /**
     * Get flash message and clear it
     * @param string $key
     * @return string|null
     */
    public function getFlash($key) {
        $message = $_SESSION['flash'][$key] ?? null;
        unset($_SESSION['flash'][$key]);
        return $message;
    }
    
    /**
     * Set flash message
     * @param string $key
     * @param string $message
     */
    public function setFlash($key, $message) {
        $_SESSION['flash'][$key] = $message;
    }
    
    /**
     * Debug session information
     * @return array
     */
    public function getDebugInfo() {
        return [
            'session_id' => session_id(),
            'session_status' => session_status(),
            'session_data' => $_SESSION,
            'session_save_path' => session_save_path(),
            'session_cookie_params' => session_get_cookie_params()
        ];
    }
} 