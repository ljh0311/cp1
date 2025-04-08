<?php
// Initialize error message variable
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../database/DatabaseManager.php';
$error_message = null;

// Get cart count if user is logged in
$cart_count = 0;
if (isset($_SESSION['user_id'])) {
    try {
        $db = DatabaseManager::getInstance();
        $cart_query = $db->query(
            "SELECT SUM(quantity) as total FROM cart_items WHERE user_id = ?",
            [$_SESSION['user_id']]
        );
        $count_data = $db->fetch($cart_query);
        $cart_count = $count_data['total'] ?? 0;
    } catch (Exception $e) {
        error_log('Error fetching cart count: ' . $e->getMessage());
    }
}
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
                    <a class="nav-link" href="/about.php">About</a>
                </li>
            </ul>
            
            <div class="d-flex align-items-center">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="/cart.php" class="btn btn-outline-primary me-2">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="badge bg-primary rounded-pill" id="cartCount"><?php echo $cart_count; ?></span>
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
                    <a href="/signup.php" class="btn btn-primary">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>