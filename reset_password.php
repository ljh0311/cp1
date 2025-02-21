<?php
require_once 'inc/config.php';
require_once 'database/DatabaseManager.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $new_password = $_POST['new_password'] ?? '';
    
    if (empty($username) || empty($new_password)) {
        $error = 'Both username and new password are required';
    } else {
        try {
            $db = DatabaseManager::getInstance();
            
            // Check if user exists
            $stmt = $db->query("SELECT user_id FROM users WHERE username = :username",
                             [':username' => $username]);
            $user = $db->fetch($stmt);
            
            if ($user) {
                // Hash the new password
                $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                
                // Update the password
                $db->query("UPDATE users SET password_hash = :password_hash WHERE user_id = :user_id",
                          [':password_hash' => $password_hash, ':user_id' => $user['user_id']]);
                
                $message = 'Password has been reset successfully. You can now login with your new password.';
                
                // For debugging - show the hash
                $debug_info = "New password hash: " . $password_hash;
            } else {
                $error = 'User not found';
            }
        } catch (Exception $e) {
            $error = 'Error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Reset Password</title>
    <?php require_once 'inc/head.inc.php'; ?>
</head>
<body>
    <?php require_once 'inc/nav.inc.php'; ?>
    
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-body p-5">
                        <h1 class="text-center mb-4">Reset Password</h1>
                        
                        <?php if ($message): ?>
                            <div class="alert alert-success">
                                <?php echo htmlspecialchars($message); ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="reset_password.php" novalidate>
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="new_password" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="new_password" name="new_password" required>
                                <div class="form-text">
                                    Password must be at least 8 characters and include uppercase, lowercase, number, and special character.
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">Reset Password</button>
                        </form>
                        
                        <?php if (isset($debug_info)): ?>
                            <div class="mt-4 p-3 bg-light">
                                <pre><?php echo htmlspecialchars($debug_info); ?></pre>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php require_once 'inc/footer.inc.php'; ?>
</body>
</html> 