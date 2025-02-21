<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="list-group">
    <a href="/admin/dashboard.php" 
       class="list-group-item list-group-item-action <?php echo $current_page === 'dashboard.php' ? 'active' : ''; ?>">
        <i class="fas fa-tachometer-alt me-2"></i>
        Dashboard
    </a>
    <a href="/admin/books.php" 
       class="list-group-item list-group-item-action <?php echo $current_page === 'books.php' ? 'active' : ''; ?>">
        <i class="fas fa-book me-2"></i>
        Books
    </a>
    <a href="/admin/orders.php" 
       class="list-group-item list-group-item-action <?php echo $current_page === 'orders.php' ? 'active' : ''; ?>">
        <i class="fas fa-shopping-cart me-2"></i>
        Orders
    </a>
    <a href="/admin/users.php" 
       class="list-group-item list-group-item-action <?php echo $current_page === 'users.php' ? 'active' : ''; ?>">
        <i class="fas fa-users me-2"></i>
        Users
    </a>
    <a href="/admin/categories.php" 
       class="list-group-item list-group-item-action <?php echo $current_page === 'categories.php' ? 'active' : ''; ?>">
        <i class="fas fa-tags me-2"></i>
        Categories
    </a>
    <a href="/admin/reports.php" 
       class="list-group-item list-group-item-action <?php echo $current_page === 'reports.php' ? 'active' : ''; ?>">
        <i class="fas fa-chart-bar me-2"></i>
        Reports
    </a>
    <a href="/admin/settings.php" 
       class="list-group-item list-group-item-action <?php echo $current_page === 'settings.php' ? 'active' : ''; ?>">
        <i class="fas fa-cog me-2"></i>
        Settings
    </a>
</div> 