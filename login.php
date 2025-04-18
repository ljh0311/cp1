<?php
require_once 'inc/config.php';
require_once 'inc/session_config.php';
require_once 'inc/ErrorHandler.php';
require_once 'database/DatabaseManager.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'index.php';
    header('Location: ' . $redirect);
    exit;
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember_me = isset($_POST['remember_me']);

    // Debug information
    error_log("Login attempt - Username/Email: " . $username);

    // Validate input
    if (empty($username)) {
        $errors['username'] = 'Username or Email is required';
    }
    if (empty($password)) {
        $errors['password'] = 'Password is required';
    }

    if (empty($errors)) {
        try {
            $db = DatabaseManager::getInstance();
            
            // Get user by username or email
            $query = "SELECT user_id, username, email, password_hash, full_name, is_admin, status 
                     FROM users 
                     WHERE (username = :username OR email = :email) 
                     AND status = 'active'";
            
            $params = [
                ':username' => $username,
                ':email' => $username // Same value, different parameter name
            ];
            
            error_log("Executing query with parameters: " . print_r($params, true));
            
            $stmt = $db->query($query, $params);
            $user = $db->fetch($stmt);

            // Debug information
            error_log("User found: " . ($user ? 'Yes' : 'No'));
            if ($user) {
                error_log("Password verification result: " . (password_verify($password, $user['password_hash']) ? 'Success' : 'Failed'));
            }

            if ($user && password_verify($password, $user['password_hash'])) {
                // Start session and set user data
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['is_admin'] = $user['is_admin'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['last_activity'] = time();

                // Set remember me cookie if requested
                if ($remember_me) {
                    $token = bin2hex(random_bytes(32));
                    setcookie('remember_token', $token, time() + 30*24*60*60, '/', '', true, true);
                    
                    // Store token in database
                    $db->query(
                        "INSERT INTO remember_tokens (user_id, token, expires_at) 
                         VALUES (:user_id, :token, DATE_ADD(NOW(), INTERVAL 30 DAY))",
                        [':user_id' => $user['user_id'], ':token' => $token]
                    );
                }

                // Log successful login
                error_log("User {$user['username']} logged in successfully");

                // Redirect to requested page or home
                $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'index.php';
                header('Location: ' . $redirect);
                exit;
            } else {
                $errors['login'] = 'Invalid username/email or password';
                
                // Log failed login attempt
                error_log("Failed login attempt for username/email: $username");
            }
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            $errors['general'] = 'Login failed. Please try again later.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Sign In - <?php echo SITE_NAME; ?></title>
    <?php require_once 'inc/head.inc.php'; ?>
</head>
<body>
    <?php require_once 'inc/nav.inc.php'; ?>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-body p-5">
                        <h1 class="text-center mb-4">Sign In</h1>
                        
                        <?php if (isset($errors['general'])): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <?php echo htmlspecialchars($errors['general']); ?>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($errors['login'])): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <?php echo htmlspecialchars($errors['login']); ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>" novalidate>
                            <div class="mb-3">
                                <label for="username" class="form-label">Username or Email</label>
                                <input type="text" 
                                       class="form-control <?php echo isset($errors['username']) ? 'is-invalid' : ''; ?>"
                                       id="username" 
                                       name="username" 
                                       value="<?php echo htmlspecialchars($username ?? ''); ?>"
                                       required>
                                <?php if (isset($errors['username'])): ?>
                                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['username']); ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" 
                                       class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>"
                                       id="password" 
                                       name="password" 
                                       required>
                                <?php if (isset($errors['password'])): ?>
                                    <div class="invalid-feedback"><?php echo htmlspecialchars($errors['password']); ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="remember_me" name="remember_me">
                                    <label class="form-check-label" for="remember_me">
                                        Remember me
                                    </label>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100 mb-3">Sign In</button>
                            
                            <div class="text-center">
                                <p class="mb-2">
                                    <a href="forgot-password.php">Forgot your password?</a>
                                </p>
                                <p class="mb-0">
                                    Don't have an account? <a href="register.php">Register here</a>
                                </p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php require_once 'inc/footer.inc.php'; ?>
</body>
</html> 