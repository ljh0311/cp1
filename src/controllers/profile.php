<?php
// Define root path if not already defined
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', __DIR__);
}

require_once 'inc/config.php';
require_once 'inc/session_config.php';
require_once ROOT_PATH . '/inc/SessionManager.php';
require_once ROOT_PATH . '/database/DatabaseManager.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get session manager instance
$sessionManager = SessionManager::getInstance();

// Check if user is logged in
if (!$sessionManager->isLoggedIn()) {
    $sessionManager->setFlash('error', 'Please log in to view your profile.');
    header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

$success_message = $sessionManager->getFlash('success');
$error_message = $sessionManager->getFlash('error');

try {
    $db = DatabaseManager::getInstance();
    
    // Get user details
    $user_query = $db->query(
        "SELECT user_id, username, email, full_name 
         FROM users 
         WHERE user_id = ?",
        [$sessionManager->getUserId()]
    );
    
    $user = $db->fetch($user_query);
    
    if (!$user) {
        throw new Exception('User not found.');
    }
    
    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $updates = [];
        $params = [];
        $error = false;
        
        // Validate full name
        if (!empty($_POST['full_name'])) {
            if (strlen($_POST['full_name']) < 2 || strlen($_POST['full_name']) > 100) {
                $error_message = 'Full name must be between 2 and 100 characters.';
                $error = true;
            } else {
                $updates[] = "full_name = ?";
                $params[] = trim($_POST['full_name']);
            }
        }
        
        // Validate username
        if (!empty($_POST['username']) && $_POST['username'] !== $user['username']) {
            if (!preg_match('/^[a-zA-Z0-9_]{3,50}$/', $_POST['username'])) {
                $error_message = 'Username must be between 3 and 50 characters and can only contain letters, numbers, and underscores.';
                $error = true;
            } else {
                // Check if username is already taken
                $check_username = $db->query(
                    "SELECT user_id FROM users WHERE username = ? AND user_id != ?",
                    [trim($_POST['username']), $user['user_id']]
                );
                if ($db->fetch($check_username)) {
                    $error_message = 'Username is already taken.';
                    $error = true;
                } else {
                    $updates[] = "username = ?";
                    $params[] = trim($_POST['username']);
                }
            }
        }
        
        // Validate email
        if (!empty($_POST['email']) && $_POST['email'] !== $user['email']) {
            if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                $error_message = 'Please enter a valid email address.';
                $error = true;
            } else {
                // Check if email is already taken
                $check_email = $db->query(
                    "SELECT user_id FROM users WHERE email = ? AND user_id != ?",
                    [trim($_POST['email']), $user['user_id']]
                );
                if ($db->fetch($check_email)) {
                    $error_message = 'Email is already registered.';
                    $error = true;
                } else {
                    $updates[] = "email = ?";
                    $params[] = trim($_POST['email']);
                }
            }
        }
        
        // Handle password change
        if (!empty($_POST['new_password'])) {
            // Validate current password
            if (empty($_POST['current_password'])) {
                $error_message = 'Current password is required to change password.';
                $error = true;
            } else {
                // Verify current password
                $check_password = $db->query(
                    "SELECT password_hash FROM users WHERE user_id = ?",
                    [$user['user_id']]
                );
                $current_user = $db->fetch($check_password);
                
                if (!password_verify($_POST['current_password'], $current_user['password_hash'])) {
                    $error_message = 'Current password is incorrect.';
                    $error = true;
                } else {
                    // Validate new password
                    if (strlen($_POST['new_password']) < PASSWORD_MIN_LENGTH) {
                        $error_message = 'New password must be at least ' . PASSWORD_MIN_LENGTH . ' characters.';
                        $error = true;
                    } else if ($_POST['new_password'] !== $_POST['confirm_password']) {
                        $error_message = 'New passwords do not match.';
                        $error = true;
                    } else {
                        $updates[] = "password_hash = ?";
                        $params[] = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
                    }
                }
            }
        }
        
        // Update user if there are changes and no errors
        if (!empty($updates) && !$error) {
            $params[] = $user['user_id'];
            $update_query = "UPDATE users SET " . implode(", ", $updates) . " WHERE user_id = ?";
            
            try {
                $db->query($update_query, $params);
                $success_message = 'Profile updated successfully.';
                
                // Refresh user data
                $user_query = $db->query(
                    "SELECT user_id, username, email, full_name 
                     FROM users 
                     WHERE user_id = ?",
                    [$user['user_id']]
                );
                $user = $db->fetch($user_query);
                
            } catch (Exception $e) {
                error_log("Error updating profile: " . $e->getMessage());
                $error_message = 'An error occurred while updating your profile.';
            }
        }
    }
    
} catch (Exception $e) {
    error_log("Error in profile.php: " . $e->getMessage());
    $error_message = 'An error occurred while loading your profile.';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>My Profile - <?php echo SITE_NAME; ?></title>
    <?php require_once 'inc/head.inc.php'; ?>
    <style>
        .profile-container {
            background-color: #f8f9fa;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .profile-form {
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .form-label {
            font-weight: 500;
        }
        .password-section {
            border-top: 1px solid #dee2e6;
            margin-top: 2rem;
            padding-top: 2rem;
        }
    </style>
</head>
<body>
    <?php require_once 'inc/nav.inc.php'; ?>
    
    <div class="container py-5">
        <div class="profile-container p-4">
            <h1 class="h3 mb-4">My Profile</h1>
            
            <?php if ($success_message): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>
                    <?php echo htmlspecialchars($success_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-circle me-2"></i>
                    <?php echo htmlspecialchars($error_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <div class="profile-form p-4">
                <form method="POST" action="profile.php" class="needs-validation" novalidate>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="full_name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" 
                                   value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                            <div class="invalid-feedback">
                                Please enter your full name.
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" 
                                   value="<?php echo htmlspecialchars($user['username']); ?>" required>
                            <div class="invalid-feedback">
                                Please enter a valid username.
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        <div class="invalid-feedback">
                            Please enter a valid email address.
                        </div>
                    </div>
                    
                    <div class="password-section">
                        <h4 class="h5 mb-3">Change Password</h4>
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <input type="password" class="form-control" id="current_password" name="current_password">
                            <div class="form-text">Required only if you want to change your password.</div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="new_password" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="new_password" name="new_password" 
                                       minlength="<?php echo PASSWORD_MIN_LENGTH; ?>">
                                <div class="form-text">
                                    Minimum <?php echo PASSWORD_MIN_LENGTH; ?> characters
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                                <div class="invalid-feedback">
                                    Passwords do not match.
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check2 me-2"></i>Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <?php require_once 'inc/footer.inc.php'; ?>
    
    <script>
        // Form validation
        (function() {
            'use strict';
            
            const forms = document.querySelectorAll('.needs-validation');
            const newPassword = document.getElementById('new_password');
            const confirmPassword = document.getElementById('confirm_password');
            
            // Password match validation
            function validatePasswordMatch() {
                if (newPassword.value && confirmPassword.value) {
                    if (newPassword.value !== confirmPassword.value) {
                        confirmPassword.setCustomValidity('Passwords do not match');
                    } else {
                        confirmPassword.setCustomValidity('');
                    }
                }
            }
            
            newPassword.addEventListener('input', validatePasswordMatch);
            confirmPassword.addEventListener('input', validatePasswordMatch);
            
            // Form submission handling
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        })();
    </script>
</body>
</html> 