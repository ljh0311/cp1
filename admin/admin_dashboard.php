<?php
// Define root path if not already defined
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

require_once '../inc/config.php';
require_once '../inc/session_config.php';
require_once '../database/DatabaseManager.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: ../login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

try {
    $db = DatabaseManager::getInstance();
    
    // Get current page from URL
    $current_page = isset($_GET['page']) ? $_GET['page'] : 'books';
    
    // Get statistics
    $total_books = $db->query("SELECT COUNT(*) as count FROM books")->fetch()['count'];
    $total_orders = $db->query("SELECT COUNT(*) as count FROM orders")->fetch()['count'];
    $total_users = $db->query("SELECT COUNT(*) as count FROM users WHERE is_admin = 0")->fetch()['count'];
    $total_revenue = $db->query("SELECT COALESCE(SUM(total_amount), 0) as total FROM orders WHERE status = 'completed'")->fetch()['total'];
    
    // Get data based on current page
    switch($current_page) {
        case 'orders':
            $data = $db->query(
                "SELECT o.*, u.email, COUNT(oi.order_item_id) as item_count 
                 FROM orders o 
                 JOIN users u ON o.user_id = u.user_id 
                 LEFT JOIN order_items oi ON o.order_id = oi.order_id 
                 GROUP BY o.order_id 
                 ORDER BY o.created_at DESC"
            )->fetchAll();
            break;
            
        case 'users':
            $data = $db->query(
                "SELECT user_id, username, email, full_name, created_at, status 
                 FROM users 
                 WHERE is_admin = 0 
                 ORDER BY created_at DESC"
            )->fetchAll();
            break;
            
        case 'books':
        default:
            $data = $db->query(
                "SELECT b.*, c.name as category_name 
                 FROM books b 
                 LEFT JOIN categories c ON b.category_id = c.category_id 
                 ORDER BY b.created_at DESC"
            )->fetchAll();
            break;
    }
    
} catch (Exception $e) {
    error_log("Error in admin dashboard: " . $e->getMessage());
    $data = [];
}

$page_title = "Admin Dashboard";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title><?php echo $page_title; ?> - <?php echo SITE_NAME; ?></title>
    <?php 
    require_once '../inc/head.inc.php';
    require_once '../inc/nav.inc.php';
    ?>
    <style>
        .admin-container {
            display: flex;
            min-height: 100vh;
            background-color: #f8f9fa;
        }
        .admin-sidebar {
            width: 250px;
            background-color: #212529;
            color: #fff;
            padding: 2rem 1rem;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }
        .admin-sidebar h2 {
            font-size: 1.5rem;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .admin-sidebar nav ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .admin-sidebar nav ul li {
            margin-bottom: 0.5rem;
        }
        .admin-sidebar nav ul li a {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            padding: 0.75rem 1rem;
            display: block;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }
        .admin-sidebar nav ul li a:hover,
        .admin-sidebar nav ul li a.active {
            background-color: rgba(255,255,255,0.1);
            color: #fff;
        }
        .admin-content {
            flex: 1;
            margin-left: 250px;
            padding: 2rem;
        }
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background-color: #fff;
            padding: 1.5rem;
            border-radius: 0.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .stat-card h3 {
            color: #6c757d;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }
        .stat-card .value {
            font-size: 2rem;
            font-weight: 600;
            color: #212529;
        }
        .data-table {
            background-color: #fff;
            border-radius: 0.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .data-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
        }
        .action-buttons .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <aside class="admin-sidebar">
            <h2>Dashboard</h2>
            <nav>
                <ul>
                    <li>
                        <a href="?page=books" class="<?php echo $current_page === 'books' ? 'active' : ''; ?>">
                            <i class="fas fa-book me-2"></i>Books
                        </a>
                    </li>
                    <li>
                        <a href="?page=orders" class="<?php echo $current_page === 'orders' ? 'active' : ''; ?>">
                            <i class="fas fa-shopping-cart me-2"></i>Orders
                        </a>
                    </li>
                    <li>
                        <a href="?page=users" class="<?php echo $current_page === 'users' ? 'active' : ''; ?>">
                            <i class="fas fa-users me-2"></i>Users
                        </a>
                    </li>
                    <li>
                        <a href="../index.php">
                            <i class="fas fa-home me-2"></i>Back to Site
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <main class="admin-content">
            <div class="stats-cards">
                <div class="stat-card">
                    <h3>Total Books</h3>
                    <div class="value"><?php echo number_format($total_books); ?></div>
                </div>
                <div class="stat-card">
                    <h3>Total Orders</h3>
                    <div class="value"><?php echo number_format($total_orders); ?></div>
                </div>
                <div class="stat-card">
                    <h3>Total Users</h3>
                    <div class="value"><?php echo number_format($total_users); ?></div>
                </div>
                <div class="stat-card">
                    <h3>Total Revenue</h3>
                    <div class="value">$<?php echo number_format($total_revenue, 2); ?></div>
                </div>
            </div>

            <?php if ($current_page === 'books'): ?>
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="h4 mb-0">Manage Books</h2>
                    <button class="btn btn-primary" onclick="showAddModal('book')">
                        <i class="fas fa-plus me-2"></i>Add New Book
                    </button>
                </div>
                <div class="data-table table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Author</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data as $book): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($book['title']); ?></td>
                                    <td><?php echo htmlspecialchars($book['author']); ?></td>
                                    <td><?php echo htmlspecialchars($book['category_name'] ?? 'Uncategorized'); ?></td>
                                    <td>$<?php echo number_format($book['price'], 2); ?></td>
                                    <td><?php echo number_format($book['stock_quantity']); ?></td>
                                    <td class="action-buttons">
                                        <button class="btn btn-sm btn-primary" onclick="editBook(<?php echo $book['book_id']; ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="deleteItem('book', <?php echo $book['book_id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            <?php elseif ($current_page === 'orders'): ?>
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="h4 mb-0">Manage Orders</h2>
                </div>
                <div class="data-table table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data as $order): ?>
                                <tr>
                                    <td>#<?php echo str_pad($order['order_id'], 8, '0', STR_PAD_LEFT); ?></td>
                                    <td><?php echo htmlspecialchars($order['email']); ?></td>
                                    <td><?php echo $order['item_count']; ?> items</td>
                                    <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $order['status'] === 'completed' ? 'success' : 'warning'; ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                                    <td class="action-buttons">
                                        <button class="btn btn-sm btn-primary" onclick="viewOrder(<?php echo $order['order_id']; ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="deleteItem('order', <?php echo $order['order_id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            <?php elseif ($current_page === 'users'): ?>
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="h4 mb-0">Manage Users</h2>
                </div>
                <div class="data-table table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data as $user): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $user['status'] === 'active' ? 'success' : 'danger'; ?>">
                                            <?php echo ucfirst($user['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                    <td class="action-buttons">
                                        <button class="btn btn-sm btn-primary" onclick="editUser(<?php echo $user['user_id']; ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="deleteItem('user', <?php echo $user['user_id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <!-- Book Modal -->
    <div class="modal fade" id="bookModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Book</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="bookForm" enctype="multipart/form-data">
                        <input type="hidden" name="book_id" id="book_id">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Title</label>
                                <input type="text" class="form-control" name="title" id="title" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Author</label>
                                <input type="text" class="form-control" name="author" id="author" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Category</label>
                                <select class="form-select" name="category_id" id="category_id">
                                    <option value="">Select Category</option>
                                    <?php
                                    $categories = $db->query("SELECT * FROM categories ORDER BY name")->fetchAll();
                                    foreach ($categories as $category) {
                                        echo "<option value='{$category['category_id']}'>{$category['name']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">ISBN</label>
                                <input type="text" class="form-control" name="isbn" id="isbn">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Price</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" name="price" id="price" step="0.01" min="0" required>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Stock Quantity</label>
                                <input type="number" class="form-control" name="stock_quantity" id="stock_quantity" min="0" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" id="description" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Book Cover</label>
                            <input type="file" class="form-control" name="image" id="image" accept="image/*">
                            <small class="text-muted">Leave empty to keep existing image when editing</small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveBook">Save Changes</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Order View Modal -->
    <div class="modal fade" id="orderModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Order Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Order details will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-success" onclick="updateOrderStatus()">Update Status</button>
                </div>
            </div>
        </div>
    </div>

    <!-- User Modal -->
    <div class="modal fade" id="userModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="userForm">
                        <input type="hidden" name="user_id" id="userId">
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="suspended">Suspended</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="saveUser()">Save</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Bootstrap modals
            const bookModal = new bootstrap.Modal(document.getElementById('bookModal'));
            const orderModal = new bootstrap.Modal(document.getElementById('orderModal'));
            
            // Book Management
            const bookForm = document.getElementById('bookForm');
            
            // Add new book button
            const addBookBtn = document.querySelector('[data-bs-target="#bookModal"]');
            if (addBookBtn) {
                addBookBtn.addEventListener('click', function() {
                    bookForm.reset();
                    document.getElementById('book_id').value = '';
                    document.querySelector('#bookModal .modal-title').textContent = 'Add New Book';
                });
            }
            
            // Edit book buttons
            document.querySelectorAll('.edit-book').forEach(button => {
                button.addEventListener('click', async function() {
                    const bookId = this.dataset.bookId;
                    try {
                        const response = await fetch(`process_book.php?action=get&id=${bookId}`);
                        if (!response.ok) throw new Error('Failed to fetch book details');
                        
                        const book = await response.json();
                        document.getElementById('book_id').value = book.book_id;
                        document.getElementById('title').value = book.title;
                        document.getElementById('author').value = book.author;
                        document.getElementById('category_id').value = book.category_id || '';
                        document.getElementById('isbn').value = book.isbn || '';
                        document.getElementById('price').value = book.price;
                        document.getElementById('stock_quantity').value = book.stock_quantity;
                        document.getElementById('description').value = book.description;
                        
                        document.querySelector('#bookModal .modal-title').textContent = 'Edit Book';
                        bookModal.show();
                    } catch (error) {
                        alert('Error loading book details: ' + error.message);
                    }
                });
            });
            
            // Save book
            document.getElementById('saveBook')?.addEventListener('click', async function() {
                try {
                    const formData = new FormData(bookForm);
                    const response = await fetch('process_book.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    if (!response.ok) throw new Error('Failed to save book');
                    
                    const result = await response.json();
                    if (result.success) {
                        location.reload();
                    } else {
                        throw new Error(result.message || 'Failed to save book');
                    }
                } catch (error) {
                    alert('Error: ' + error.message);
                }
            });
            
            // Delete book
            document.querySelectorAll('.delete-book').forEach(button => {
                button.addEventListener('click', async function() {
                    if (!confirm(`Are you sure you want to delete "${this.dataset.bookTitle}"?`)) return;
                    
                    try {
                        const response = await fetch('process_book.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: `action=delete&id=${this.dataset.bookId}`
                        });
                        
                        if (!response.ok) throw new Error('Failed to delete book');
                        
                        const result = await response.json();
                        if (result.success) {
                            location.reload();
                        } else {
                            throw new Error(result.message || 'Failed to delete book');
                        }
                    } catch (error) {
                        alert('Error: ' + error.message);
                    }
                });
            });
            
            // Order Management
            // Update order status
            document.querySelectorAll('.order-status').forEach(select => {
                select.addEventListener('change', async function() {
                    const originalValue = this.value;
                    try {
                        const response = await fetch('process_order.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: `action=update_status&id=${this.dataset.orderId}&status=${this.value}`
                        });
                        
                        if (!response.ok) throw new Error('Failed to update order status');
                        
                        const result = await response.json();
                        if (!result.success) {
                            throw new Error(result.message || 'Failed to update order status');
                        }
                    } catch (error) {
                        alert('Error: ' + error.message);
                        this.value = originalValue;
                    }
                });
            });
            
            // View order
            document.querySelectorAll('.view-order').forEach(button => {
                button.addEventListener('click', async function() {
                    try {
                        const response = await fetch(`process_order.php?action=get&id=${this.dataset.orderId}`);
                        if (!response.ok) throw new Error('Failed to load order details');
                        
                        const order = await response.json();
                        const modalBody = document.querySelector('#orderModal .modal-body');
                        modalBody.innerHTML = `
                            <div class="mb-4">
                                <h6>Order #${String(order.order_id).padStart(8, '0')}</h6>
                                <p class="mb-1">Customer: ${order.email}</p>
                                <p class="mb-1">Date: ${new Date(order.created_at).toLocaleDateString()}</p>
                                <p class="mb-1">Status: ${order.status}</p>
                            </div>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Item</th>
                                            <th>Quantity</th>
                                            <th>Price</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${order.items.map(item => `
                                            <tr>
                                                <td>${item.title}</td>
                                                <td>${item.quantity}</td>
                                                <td>$${Number(item.price_at_time).toFixed(2)}</td>
                                                <td>$${(item.quantity * item.price_at_time).toFixed(2)}</td>
                                            </tr>
                                        `).join('')}
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                            <td><strong>$${Number(order.total_amount).toFixed(2)}</strong></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        `;
                        
                        orderModal.show();
                    } catch (error) {
                        alert('Error: ' + error.message);
                    }
                });
            });
            
            // Delete order
            document.querySelectorAll('.delete-order').forEach(button => {
                button.addEventListener('click', async function() {
                    if (!confirm('Are you sure you want to delete this order?')) return;
                    
                    try {
                        const response = await fetch('process_order.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: `action=delete&id=${this.dataset.orderId}`
                        });
                        
                        if (!response.ok) throw new Error('Failed to delete order');
                        
                        const result = await response.json();
                        if (result.success) {
                            location.reload();
                        } else {
                            throw new Error(result.message || 'Failed to delete order');
                        }
                    } catch (error) {
                        alert('Error: ' + error.message);
                    }
                });
            });
            
            // User Management
            // Update user status
            document.querySelectorAll('.user-status').forEach(select => {
                select.addEventListener('change', async function() {
                    const originalValue = this.value;
                    try {
                        const response = await fetch('process_user.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: `action=update_status&user_id=${this.dataset.userId}&status=${this.value}`
                        });
                        
                        if (!response.ok) throw new Error('Failed to update user status');
                        
                        const result = await response.json();
                        if (!result.success) {
                            throw new Error(result.message || 'Failed to update user status');
                        }
                    } catch (error) {
                        alert('Error: ' + error.message);
                        this.value = originalValue;
                    }
                });
            });
            
            // Delete user
            document.querySelectorAll('.delete-user').forEach(button => {
                button.addEventListener('click', async function() {
                    if (!confirm(`Are you sure you want to delete user "${this.dataset.username}"?`)) return;
                    
                    try {
                        const response = await fetch('process_user.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: `action=delete&id=${this.dataset.userId}`
                        });
                        
                        if (!response.ok) throw new Error('Failed to delete user');
                        
                        const result = await response.json();
                        if (result.success) {
                            location.reload();
                        } else {
                            throw new Error(result.message || 'Failed to delete user');
                        }
                    } catch (error) {
                        alert('Error: ' + error.message);
                    }
                });
            });
        });
    </script>
</body>
</html>
