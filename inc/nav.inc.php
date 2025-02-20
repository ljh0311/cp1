<?php
// Only start session if it hasn't been started already
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}

// Initialize error message variable
$error_message = null;
?>

<nav class="navbar navbar-expand-lg fixed-top bg-white">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="index.php">
            <img src="images/logo.png" alt="Logo" width="50" height="50" class="rounded-circle me-2">
            <span class="brand-text">BookStore</span>
        </a>
        
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mx-auto">
                <?php
                $nav_items = [
                    'Home' => 'index.php',
                    'Books' => 'books.php',
                    'Categories' => 'categories.php',
                    'About' => 'about.php'
                ];

                foreach ($nav_items as $name => $url) {
                    $is_active = basename($_SERVER['PHP_SELF']) === $url;
                    echo '<li class="nav-item">';
                    echo '<a class="nav-link px-3' . ($is_active ? ' active' : '') . '" href="' . $url . '">' . $name . '</a>';
                    echo '</li>';
                }
                ?>
            </ul>
            
            <div class="d-flex align-items-center gap-3">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="cart.php" class="nav-link position-relative">
                        <i class="fas fa-shopping-cart fa-lg"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary">
                            <span id="cartCount">0</span>
                        </span>
                    </a>
                    
                    <div class="dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" 
                           role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="avatar">
                                <img src="<?php echo isset($_SESSION['avatar']) ? htmlspecialchars($_SESSION['avatar']) : 'images/avatar-placeholder.png'; ?>" 
                                     alt="Avatar" class="rounded-circle" width="32" height="32">
                            </div>
                            <span><?php echo htmlspecialchars($_SESSION['first_name'] ?? 'User'); ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                            <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="orders.php"><i class="fas fa-shopping-bag me-2"></i>Orders</a></li>
                            <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                                <li><a class="dropdown-item" href="admin/"><i class="fas fa-cog me-2"></i>Admin Panel</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline-primary rounded-pill px-4">Login</a>
                    <a href="register.php" class="btn btn-primary rounded-pill px-4">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>