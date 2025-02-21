<?php
session_start();
require_once '../inc/config.php';
require_once '../database/DatabaseManager.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: ../login.php');
    exit;
}

try {
    $db = DatabaseManager::getInstance();
    $books_query = "SELECT * FROM books ORDER BY created_at DESC";
    $result = $db->query($books_query);
    $books = $db->fetchAll($result);
} catch (Exception $e) {
    $error = "Database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo SITE_NAME; ?></title>
    <?php require_once '../inc/head.inc.php'; ?>
    <style>
        .admin-sidebar { min-height: 100vh; background: #f8f9fa; }
        .admin-content { padding: 2rem; }
    </style>
</head>
<body>
    <?php require_once '../inc/nav.inc.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block admin-sidebar sidebar collapse">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="admin_dashboard.php">
                                <i class="fas fa-home me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="books.php">
                                <i class="fas fa-book me-2"></i>Books
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="users.php">
                                <i class="fas fa-users me-2"></i>Users
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="orders.php">
                                <i class="fas fa-shopping-cart me-2"></i>Orders
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 admin-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1>Book Management</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBookModal">
                        <i class="fas fa-plus me-2"></i>Add New Book
                    </button>
                </div>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <!-- Books Table -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Image</th>
                                <th>Title</th>
                                <th>Author</th>
                                <th>Price</th>
                                <th>Category</th>
                                <th>Featured</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($books as $book): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($book['book_id']); ?></td>
                                <td>
                                    <img src="<?php echo htmlspecialchars($book['image_url']); ?>" 
                                         alt="Book cover" style="width: 50px; height: 50px; object-fit: cover;">
                                </td>
                                <td><?php echo htmlspecialchars($book['title']); ?></td>
                                <td><?php echo htmlspecialchars($book['author']); ?></td>
                                <td>$<?php echo number_format($book['price'], 2); ?></td>
                                <td><?php echo htmlspecialchars($book['category']); ?></td>
                                <td>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input featured-toggle" type="checkbox" 
                                               data-book-id="<?php echo $book['book_id']; ?>"
                                               <?php echo $book['featured'] ? 'checked' : ''; ?>>
                                    </div>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-primary edit-book" 
                                            data-book-id="<?php echo $book['book_id']; ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger delete-book" 
                                            data-book-id="<?php echo $book['book_id']; ?>">
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
    </div>

    <!-- Add Book Modal -->
    <div class="modal fade" id="addBookModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Book</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addBookForm" action="process_book.php" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" class="form-control" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Author</label>
                            <input type="text" class="form-control" name="author" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Price</label>
                            <input type="number" class="form-control" name="price" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <select class="form-select" name="category" required>
                                <option value="Programming">Programming</option>
                                <option value="Web Development">Web Development</option>
                                <option value="Database">Database</option>
                                <option value="Cloud Computing">Cloud Computing</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Image</label>
                            <input type="file" class="form-control" name="image" accept="image/*" required>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" name="featured" value="1">
                            <label class="form-check-label">Featured</label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" form="addBookForm" class="btn btn-primary">Add Book</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Book management JavaScript
        document.addEventListener('DOMContentLoaded', function() {
            // Featured toggle
            document.querySelectorAll('.featured-toggle').forEach(toggle => {
                toggle.addEventListener('change', function() {
                    const bookId = this.dataset.bookId;
                    const featured = this.checked ? 1 : 0;
                    
                    fetch('process_book.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `action=toggle_featured&book_id=${bookId}&featured=${featured}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (!data.success) {
                            alert('Failed to update featured status');
                            this.checked = !this.checked;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred');
                        this.checked = !this.checked;
                    });
                });
            });

            // Delete book
            document.querySelectorAll('.delete-book').forEach(button => {
                button.addEventListener('click', function() {
                    if (confirm('Are you sure you want to delete this book?')) {
                        const bookId = this.dataset.bookId;
                        
                        fetch('process_book.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: `action=delete&book_id=${bookId}`
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                this.closest('tr').remove();
                            } else {
                                alert('Failed to delete book');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('An error occurred');
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>
