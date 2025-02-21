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
        "SELECT book_id, title, author, price, stock_quantity, image_url, description 
         FROM books 
         ORDER BY created_at DESC"
    );
    $books = $db->fetchAll($books);
} catch (Exception $e) {
    error_log("Error fetching books: " . $e->getMessage());
    $books = [];
}

include_once '../inc/head.inc.php';
?>

<div class="admin-container">
    <aside class="admin-sidebar">
        <h2>Dashboard</h2>
        <nav>
            <ul>
                <li><a href="#" class="active">Books</a></li>
                <li><a href="#">Orders</a></li>
                <li><a href="#">Users</a></li>
                <li><a href="#">Settings</a></li>
            </ul>
        </nav>
    </aside>

    <main class="admin-content">
        <header class="admin-header">
            <h1>Book Management</h1>
            <button class="btn btn-primary" onclick="showAddBookModal()">Add New Book</button>
        </header>

        <div class="books-table-container">
            <table class="books-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($books as $book): ?>
                    <tr>
                        <td><?php echo htmlspecialchars((string)$book['book_id']); ?></td>
                        <td><?php echo htmlspecialchars($book['title']); ?></td>
                        <td><?php echo htmlspecialchars($book['author']); ?></td>
                        <td>$<?php echo number_format((float)$book['price'], 2); ?></td>
                        <td><?php echo htmlspecialchars((string)$book['stock_quantity']); ?></td>
                        <td>
                            <button class="btn btn-sm btn-primary" onclick="editBook(<?php echo $book['book_id']; ?>)">Edit</button>
                            <button class="btn btn-sm btn-danger" onclick="deleteBook(<?php echo $book['book_id']; ?>)">Delete</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<!-- Add/Edit Book Modal -->
<div id="bookModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2 id="modalTitle">Add New Book</h2>
        <form id="bookForm" action="process_book.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="book_id" id="bookId">
            <div class="form-group">
                <label for="title">Title</label>
                <input type="text" id="title" name="title" required>
            </div>
            <div class="form-group">
                <label for="author">Author</label>
                <input type="text" id="author" name="author" required>
            </div>
            <div class="form-group">
                <label for="price">Price</label>
                <input type="number" id="price" name="price" step="0.01" required>
            </div>
            <div class="form-group">
                <label for="stock">Stock</label>
                <input type="number" id="stock" name="stock" required>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" required></textarea>
            </div>
            <div class="form-group">
                <label for="cover">Book Cover</label>
                <input type="file" id="cover" name="cover" accept="image/*">
            </div>
            <button type="submit" class="btn btn-primary">Save Book</button>
        </form>
    </div>
</div>

<script>
const modal = document.getElementById('bookModal');
const closeBtn = document.getElementsByClassName('close')[0];
const bookForm = document.getElementById('bookForm');

function showAddBookModal() {
    document.getElementById('modalTitle').textContent = 'Add New Book';
    bookForm.reset();
    document.getElementById('bookId').value = '';
    modal.style.display = 'block';
}

async function editBook(bookId) {
    try {
        const response = await fetch(`process_book.php?action=get&id=${bookId}`);
        const book = await response.json();
        
        document.getElementById('modalTitle').textContent = 'Edit Book';
        document.getElementById('bookId').value = book.id;
        document.getElementById('title').value = book.title;
        document.getElementById('author').value = book.author;
        document.getElementById('price').value = book.price;
        document.getElementById('stock').value = book.stock;
        document.getElementById('description').value = book.description;
        
        modal.style.display = 'block';
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

closeBtn.onclick = () => modal.style.display = 'none';

window.onclick = (event) => {
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}

bookForm.onsubmit = async (e) => {
    e.preventDefault();
    const formData = new FormData(bookForm);
    
    try {
        const response = await fetch('process_book.php', {
            method: 'POST',
            body: formData
        });
        
        if (response.ok) {
            location.reload();
        } else {
            throw new Error('Failed to save book');
        }
    } catch (error) {
        console.error('Error saving book:', error);
        alert('Failed to save book');
    }
};
</script>

<?php include_once '../inc/footer.inc.php'; ?>
