<?php
require_once '../inc/config.php';
require_once '../inc/session_config.php';
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

try {
    $db = new DatabaseManager();
    $books = $db->query("SELECT * FROM books ORDER BY created_at DESC");
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
                        <td><?= htmlspecialchars($book['id']) ?></td>
                        <td><?= htmlspecialchars($book['title']) ?></td>
                        <td><?= htmlspecialchars($book['author']) ?></td>
                        <td>$<?= number_format($book['price'], 2) ?></td>
                        <td><?= htmlspecialchars($book['stock']) ?></td>
                        <td>
                            <button class="btn btn-small" onclick="editBook(<?= $book['id'] ?>)">Edit</button>
                            <button class="btn btn-small btn-danger" onclick="deleteBook(<?= $book['id'] ?>)">Delete</button>
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
