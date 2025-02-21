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
    $books = $db->query(
        "SELECT b.*, c.name as category_name 
         FROM books b 
         LEFT JOIN categories c ON b.category_id = c.category_id 
         ORDER BY b.created_at DESC"
    );
    $books = $db->fetchAll($books);

    // Get total number of books
    $total_books = count($books);

    // Get total number of orders
    $orders_count = $db->query("SELECT COUNT(*) as count FROM orders");
    $total_orders = $db->fetch($orders_count)['count'];

    // Get total number of users
    $users_count = $db->query("SELECT COUNT(*) as count FROM users WHERE is_admin = 0");
    $total_users = $db->fetch($users_count)['count'];

    // Get total revenue
    $revenue = $db->query("SELECT COALESCE(SUM(total_amount), 0) as total FROM orders WHERE status = 'completed'");
    $total_revenue = $db->fetch($revenue)['total'];

} catch (Exception $e) {
    error_log("Error fetching books: " . $e->getMessage());
    $books = [];
    $total_books = 0;
    $total_orders = 0;
    $total_users = 0;
    $total_revenue = 0;
}

$page_title = "Admin Dashboard";
include_once '../inc/head.inc.php';
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

    .admin-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        background-color: #fff;
        padding: 1.5rem;
        border-radius: 0.5rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
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

    .books-table-container {
        background-color: #fff;
        padding: 1.5rem;
        border-radius: 0.5rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .books-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .books-table th,
    .books-table td {
        padding: 1rem;
        border-bottom: 1px solid #dee2e6;
    }

    .books-table th {
        background-color: #f8f9fa;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
    }

    .books-table tbody tr:hover {
        background-color: #f8f9fa;
    }

    .book-image {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 0.25rem;
    }

    .btn-add-book {
        background-color: #7952b3;
        border-color: #7952b3;
        padding: 0.75rem 1.5rem;
    }

    .btn-add-book:hover {
        background-color: #614092;
        border-color: #614092;
    }

    .modal-content {
        border-radius: 0.5rem;
    }

    .modal-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
    }

    .modal-footer {
        background-color: #f8f9fa;
        border-top: 1px solid #dee2e6;
    }
</style>

<div class="admin-container">
    <aside class="admin-sidebar">
        <h2>Dashboard</h2>
        <nav>
            <ul>
                <li><a href="#" class="active"><i class="fas fa-book me-2"></i>Books</a></li>
                <li><a href="#"><i class="fas fa-shopping-cart me-2"></i>Orders</a></li>
                <li><a href="#"><i class="fas fa-users me-2"></i>Users</a></li>
                <li><a href="#"><i class="fas fa-cog me-2"></i>Settings</a></li>
            </ul>
        </nav>
    </aside>

    <main class="admin-content">
        <header class="admin-header">
            <h1 class="h3 mb-0">Book Management</h1>
            <button class="btn btn-primary btn-add-book" onclick="showAddBookModal()">
                <i class="fas fa-plus me-2"></i>Add New Book
            </button>
        </header>

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

        <div class="books-table-container">
            <table class="books-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($books as $book): ?>
                    <tr>
                        <td>
                            <img src="<?php echo htmlspecialchars($book['image_url'] ?? '../images/placeholders/book-placeholder.jpg'); ?>" 
                                 alt="<?php echo htmlspecialchars($book['title']); ?>"
                                 class="book-image">
                        </td>
                        <td><?php echo htmlspecialchars($book['title']); ?></td>
                        <td><?php echo htmlspecialchars($book['author']); ?></td>
                        <td><?php echo htmlspecialchars($book['category_name'] ?? 'Uncategorized'); ?></td>
                        <td>$<?php echo number_format($book['price'], 2); ?></td>
                        <td><?php echo number_format($book['stock_quantity']); ?></td>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="editBook(<?php echo $book['book_id']; ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="deleteBook(<?php echo $book['book_id']; ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<!-- Add/Edit Book Modal -->
<div class="modal fade" id="bookModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add New Book</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="bookForm" action="process_book.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="book_id" id="bookId">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="author" class="form-label">Author</label>
                            <input type="text" class="form-control" id="author" name="author" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="price" class="form-label">Price</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="price" name="price" step="0.01" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="stock" class="form-label">Stock</label>
                            <input type="number" class="form-control" id="stock" name="stock" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="cover" class="form-label">Book Cover</label>
                        <input type="file" class="form-control" id="cover" name="cover" accept="image/*">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="bookForm" class="btn btn-primary">Save Book</button>
            </div>
        </div>
    </div>
</div>

<script>
// Initialize Bootstrap modal
const bookModal = new bootstrap.Modal(document.getElementById('bookModal'));
const bookForm = document.getElementById('bookForm');

function showAddBookModal() {
    document.getElementById('modalTitle').textContent = 'Add New Book';
    bookForm.reset();
    document.getElementById('bookId').value = '';
    bookModal.show();
}

async function editBook(bookId) {
    try {
        const response = await fetch(`process_book.php?action=get&id=${bookId}`);
        const book = await response.json();
        
        document.getElementById('modalTitle').textContent = 'Edit Book';
        document.getElementById('bookId').value = book.book_id;
        document.getElementById('title').value = book.title;
        document.getElementById('author').value = book.author;
        document.getElementById('price').value = book.price;
        document.getElementById('stock').value = book.stock_quantity;
        document.getElementById('description').value = book.description;
        
        bookModal.show();
    } catch (error) {
        console.error('Error fetching book details:', error);
        alert('Failed to load book details');
    }
}

async function deleteBook(bookId) {
    if (confirm('Are you sure you want to delete this book?')) {
        try {
            const response = await fetch('process_book.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=delete&book_id=${bookId}`
            });
            
            if (response.ok) {
                location.reload();
            } else {
                throw new Error('Failed to delete book');
            }
        } catch (error) {
            console.error('Error deleting book:', error);
            alert('Failed to delete book');
        }
    }
}

bookForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(bookForm);
    
    try {
        const response = await fetch('process_book.php', {
            method: 'POST',
            body: formData
        });
        
        if (response.ok) {
            bookModal.hide();
            location.reload();
        } else {
            throw new Error('Failed to save book');
        }
    } catch (error) {
        console.error('Error saving book:', error);
        alert('Failed to save book');
    }
});
</script>

<?php include_once '../inc/footer.inc.php'; ?>
