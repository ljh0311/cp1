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
        <a class="navbar-brand" href="/">
            <img src="/images/logo.png" alt="Logo" width="40" height="40" class="d-inline-block align-text-top">
            <?php echo SITE_NAME; ?>
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item">
                    <a class="nav-link" href="/">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/books.php">Books</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/categories.php">Categories</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/about.php">About</a>
                </li>
            </ul>
            
            <div class="d-flex align-items-center">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="/cart.php" class="btn btn-outline-primary me-2">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="badge bg-primary rounded-pill" id="cartCount">0</span>
                    </a>
                    <div class="dropdown">
                        <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i>
                            <?php echo htmlspecialchars($_SESSION['username'] ?? 'Account'); ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                                <li><a class="dropdown-item" href="/admin/admin_dashboard.php">Admin Dashboard</a></li>
                                <li><hr class="dropdown-divider"></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item" href="/profile.php">Profile</a></li>
                            <li><a class="dropdown-item" href="/orders.php">Orders</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/logout.php">Logout</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="/login.php" class="btn btn-outline-primary me-2">Login</a>
                    <a href="/register.php" class="btn btn-primary">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>