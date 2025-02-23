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
    
    // Improved statistics queries with error handling
    $statistics = [
        'total_books' => $db->query("SELECT COUNT(*) as count FROM books")->fetch()['count'] ?? 0,
        'total_orders' => $db->query("SELECT COUNT(*) as count FROM orders")->fetch()['count'] ?? 0,
        'total_users' => $db->query("SELECT COUNT(*) as count FROM users WHERE is_admin = 0")->fetch()['count'] ?? 0,
        'total_revenue' => $db->query("SELECT COALESCE(SUM(total_amount), 0) as total FROM orders WHERE status = 'completed'")->fetch()['total'] ?? 0
    ];
    
    // Improved data queries with pagination
    $page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
    $items_per_page = 10;
    $offset = ($page - 1) * $items_per_page;
    
    switch($current_page) {
        case 'books':
            $data = $db->query("
                SELECT b.*, c.name as category_name 
                FROM books b 
                LEFT JOIN categories c ON b.category_id = c.category_id 
                ORDER BY b.created_at DESC
                LIMIT ? OFFSET ?
            ", [$items_per_page, $offset])->fetchAll();
            
            $total_items = $db->query("SELECT COUNT(*) as count FROM books")->fetch()['count'];
            break;
            
        case 'orders':
            $data = $db->query("
                SELECT o.*, u.email, 
                       COUNT(oi.order_item_id) as item_count,
                       GROUP_CONCAT(CONCAT(oi.quantity, 'x ', b.title) SEPARATOR '\n') as items_list
                FROM orders o 
                JOIN users u ON o.user_id = u.user_id 
                LEFT JOIN order_items oi ON o.order_id = oi.order_id 
                LEFT JOIN books b ON oi.book_id = b.book_id
                GROUP BY o.order_id 
                ORDER BY o.created_at DESC
                LIMIT ? OFFSET ?
            ", [$items_per_page, $offset])->fetchAll();
            
            $total_items = $db->query("SELECT COUNT(*) as count FROM orders")->fetch()['count'];
            break;
            
        case 'users':
            $data = $db->query("
                SELECT u.*, 
                       COUNT(DISTINCT o.order_id) as total_orders,
                       SUM(o.total_amount) as total_spent
                FROM users u 
                LEFT JOIN orders o ON u.user_id = o.user_id
                WHERE u.is_admin = 0 
                GROUP BY u.user_id
                ORDER BY u.created_at DESC
                LIMIT ? OFFSET ?
            ", [$items_per_page, $offset])->fetchAll();
            
            $total_items = $db->query("SELECT COUNT(*) as count FROM users WHERE is_admin = 0")->fetch()['count'];
            break;
    }
    
    $total_pages = ceil($total_items / $items_per_page);
    
} catch (Exception $e) {
    error_log("Error in admin dashboard: " . $e->getMessage());
    $data = [];
    $total_pages = 0;
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
                    <div class="value"><?php echo number_format($statistics['total_books']); ?></div>
                </div>
                <div class="stat-card">
                    <h3>Total Orders</h3>
                    <div class="value"><?php echo number_format($statistics['total_orders']); ?></div>
                </div>
                <div class="stat-card">
                    <h3>Total Users</h3>
                    <div class="value"><?php echo number_format($statistics['total_users']); ?></div>
                </div>
                <div class="stat-card">
                    <h3>Total Revenue</h3>
                    <div class="value">$<?php echo number_format($statistics['total_revenue'], 2); ?></div>
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
    const userModal = new bootstrap.Modal(document.getElementById('userModal'));
    
    // Book Management
const bookForm = document.getElementById('bookForm');

    // Show Add Book Modal
    function showAddModal(type) {
        if (type === 'book') {
    bookForm.reset();
            document.getElementById('book_id').value = '';
            document.querySelector('#bookModal .modal-title').textContent = 'Add New Book';
            bookModal.show();
        }
    }
    
    // Edit Book
    function editBook(bookId) {
        fetch(`process_book.php?action=get&id=${bookId}`)
            .then(response => response.json())
            .then(book => {
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
            })
            .catch(error => {
                alert('Error loading book details: ' + error.message);
            });
    }
    
    // Save Book
    document.getElementById('saveBook')?.addEventListener('click', function() {
        const formData = new FormData(bookForm);
        const bookId = document.getElementById('book_id').value;
        
        if (bookId) {
            formData.append('book_id', bookId);
        }
        
        fetch('process_book.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                bookModal.hide();
                location.reload();
            } else {
                throw new Error(result.message || 'Failed to save book');
            }
        })
        .catch(error => {
            alert('Error: ' + error.message);
        });
    });
    
    // Delete Item (Book, Order, or User)
    function deleteItem(type, id) {
        const confirmMessage = {
            book: 'Are you sure you want to delete this book?',
            order: 'Are you sure you want to delete this order?',
            user: 'Are you sure you want to delete this user?'
        };
        
        if (!confirm(confirmMessage[type])) return;
        
        const endpoints = {
            book: 'process_book.php',
            order: 'process_order.php',
            user: 'process_user.php'
        };
        
        fetch(endpoints[type], {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
            body: `action=delete&id=${id}`
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                location.reload();
            } else {
                throw new Error(result.message || `Failed to delete ${type}`);
            }
        })
        .catch(error => {
            alert('Error: ' + error.message);
        });
    }
    
    // View Order
    function viewOrder(orderId) {
        fetch(`process_order.php?action=get&id=${orderId}`)
            .then(response => response.json())
            .then(order => {
                const modalBody = document.querySelector('#orderModal .modal-body');
                modalBody.innerHTML = `
                    <div class="mb-4">
                        <h6>Order #${String(order.order_id).padStart(8, '0')}</h6>
                        <p class="mb-1">Customer: ${order.email}</p>
                        <p class="mb-1">Date: ${new Date(order.created_at).toLocaleDateString()}</p>
                        <p class="mb-1">Status: 
                            <select class="form-select form-select-sm d-inline-block w-auto" 
                                    onchange="updateOrderStatus(${order.order_id}, this.value)">
                                <option value="pending" ${order.status === 'pending' ? 'selected' : ''}>Pending</option>
                                <option value="processing" ${order.status === 'processing' ? 'selected' : ''}>Processing</option>
                                <option value="completed" ${order.status === 'completed' ? 'selected' : ''}>Completed</option>
                                <option value="cancelled" ${order.status === 'cancelled' ? 'selected' : ''}>Cancelled</option>
                            </select>
                        </p>
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
            })
            .catch(error => {
                alert('Error loading order details: ' + error.message);
            });
    }
    
    // Update Order Status
    function updateOrderStatus(orderId, status) {
        fetch('process_order.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=update_status&id=${orderId}&status=${status}`
        })
        .then(response => response.json())
        .then(result => {
            if (!result.success) {
                throw new Error(result.message || 'Failed to update order status');
            }
        })
        .catch(error => {
            alert('Error: ' + error.message);
            location.reload(); // Reload to reset the status
        });
    }
    
    // Edit User
    function editUser(userId) {
        fetch(`process_user.php?action=get&id=${userId}`)
            .then(response => response.json())
            .then(user => {
                document.getElementById('userId').value = user.user_id;
                document.querySelector('#userModal select[name="status"]').value = user.status;
                userModal.show();
            })
            .catch(error => {
                alert('Error loading user details: ' + error.message);
            });
    }
    
    // Save User
    function saveUser() {
        const userId = document.getElementById('userId').value;
        const status = document.querySelector('#userModal select[name="status"]').value;
        
        fetch('process_user.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=update_status&user_id=${userId}&status=${status}`
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                userModal.hide();
            location.reload();
        } else {
                throw new Error(result.message || 'Failed to update user');
            }
        })
        .catch(error => {
            alert('Error: ' + error.message);
        });
    }
    
    // Make functions globally available
    window.showAddModal = showAddModal;
    window.editBook = editBook;
    window.deleteItem = deleteItem;
    window.viewOrder = viewOrder;
    window.updateOrderStatus = updateOrderStatus;
    window.editUser = editUser;
    window.saveUser = saveUser;
});
</script>

<!-- Add pagination controls -->
<?php if ($total_pages > 1): ?>
    <nav aria-label="Page navigation" class="mt-4">
        <ul class="pagination justify-content-center">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $current_page; ?>&p=<?php echo $i; ?>">
                        <?php echo $i; ?>
                    </a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
<?php endif; ?>
</body>
</html>
