<?php
require_once 'inc/config.php';
require_once 'inc/session_config.php';
require_once 'inc/SessionManager.php';
require_once 'inc/ErrorHandler.php';
require_once 'database/DatabaseManager.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$sessionManager = SessionManager::getInstance();

// If user is already logged in, redirect to home page or requested page
if ($sessionManager->isLoggedIn()) {
    $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'index.php';
    header("Location: " . htmlspecialchars($redirect));
    exit();
}

$error = null;
$success = null;

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            throw new Exception('Please enter both email and password.');
        }

        $db = DatabaseManager::getInstance();
        
        // Get user by email
        $result = $db->query(
            "SELECT user_id, password_hash, email, name FROM users WHERE email = ?",
            [$email]
        );
        
        $user = $db->fetch($result);
        
        if (!$user || !password_verify($password, $user['password_hash'])) {
            throw new Exception('Invalid email or password.');
        }

        // Set user session
        $sessionManager->setUser($user['user_id'], [
            'email' => $user['email'],
            'name' => $user['name']
        ]);

        // Redirect to requested page or home
        $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'index.php';
        header("Location: " . htmlspecialchars($redirect));
        exit();

    } catch (Exception $e) {
        $error = $e->getMessage();
        ErrorHandler::logError($e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Login - <?php echo SITE_NAME; ?></title>
    <?php require_once 'inc/head.inc.php'; ?>
</head>
<body>
    <?php require_once 'inc/nav.inc.php'; ?>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h1 class="card-title text-center mb-4">Login</h1>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <?php echo htmlspecialchars($success); ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email address</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Login</button>
                            </div>
                        </form>
                        
                        <div class="text-center mt-3">
                            <p class="mb-0">Don't have an account? <a href="register.php">Register here</a></p>
                            <p class="mt-2"><a href="forgot-password.php">Forgot your password?</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php require_once 'inc/footer.inc.php'; ?>
</body>
</html> 