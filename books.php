<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define the root path
define('ROOT_PATH', __DIR__);

// Load configuration first
require_once ROOT_PATH . '/inc/config.php';
require_once ROOT_PATH . '/inc/session_config.php';

if (DEBUG_MODE) {
    error_log("Session status: " . session_status());
    error_log("Session ID: " . session_id());
    error_log("Session data: " . print_r($_SESSION, true));
}

// Initialize session
require_once ROOT_PATH . '/inc/session_start.php';

// Load other required files
require_once ROOT_PATH . '/inc/ErrorHandler.php';
require_once ROOT_PATH . '/database/DatabaseManager.php';

try {
    $db = DatabaseManager::getInstance();
    $category = isset($_GET['category']) ? $_GET['category'] : null;
    $sort = isset($_GET['sort']) ? $_GET['sort'] : 'title';
    $order = isset($_GET['order']) ? $_GET['order'] : 'ASC';
    
    // Build query
    $query = "SELECT b.*, c.name as category 
              FROM books b 
              LEFT JOIN categories c ON b.category_id = c.category_id";
    if ($category) {
        $query .= " WHERE c.name = :category";
    }
    $query .= " ORDER BY b.$sort $order";
    
    // Execute query
    if ($category) {
        $result = $db->query($query, [':category' => $category]);
    } else {
        $result = $db->query($query);
    }
    
    $books = $db->fetchAll($result);
} catch (Exception $e) {
    ErrorHandler::logError($e->getMessage());
    $books = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Books - <?php echo SITE_NAME; ?></title>
    <?php require_once 'inc/head.inc.php'; ?>
</head>
<body>
    <?php require_once 'inc/nav.inc.php'; ?>
    <?php ErrorHandler::displayErrors(); ?>

    <div class="container py-5">
        <div class="row mb-4">
            <div class="col-md-8">
                <h1 class="fw-bold mb-0">Our Books</h1>
                <p class="text-muted">Browse our collection of quality books</p>
            </div>
            <div class="col-md-4">
                <div class="d-flex justify-content-end align-items-center h-100">
                    <select class="form-select w-auto" id="sortBooks">
                        <option value="title-ASC" <?php echo $sort == 'title' && $order == 'ASC' ? 'selected' : ''; ?>>Title (A-Z)</option>
                        <option value="title-DESC" <?php echo $sort == 'title' && $order == 'DESC' ? 'selected' : ''; ?>>Title (Z-A)</option>
                        <option value="price-ASC" <?php echo $sort == 'price' && $order == 'ASC' ? 'selected' : ''; ?>>Price (Low to High)</option>
                        <option value="price-DESC" <?php echo $sort == 'price' && $order == 'DESC' ? 'selected' : ''; ?>>Price (High to Low)</option>
                    </select>
                </div>
            </div>
        </div>

        <?php if (empty($books)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                No books found. Please check back later.
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($books as $book): ?>
                    <div class="col-md-3">
                        <div class="card book-card h-100">
                            <img src="<?php echo htmlspecialchars($book['image_url']); ?>" 
                                 class="card-img-top" 
                                 alt="<?php echo htmlspecialchars($book['title']); ?>">
                            <div class="card-body">
                                <div class="badge bg-primary mb-2">
                                    <?php echo htmlspecialchars($book['category']); ?>
                                </div>
                                <h5 class="card-title">
                                    <?php echo htmlspecialchars($book['title']); ?>
                                </h5>
                                <p class="card-text text-muted">
                                    <?php echo htmlspecialchars($book['author']); ?>
                                </p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="book-price">
                                        $<?php echo number_format($book['price'], 2); ?>
                                    </span>
                                    <button class="btn btn-primary rounded-pill add-to-cart" 
                                            data-book-id="<?php echo $book['book_id']; ?>">
                                        Add to Cart
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php require_once 'inc/footer.inc.php'; ?>

    <script>
        document.getElementById('sortBooks')?.addEventListener('change', function() {
            const [sort, order] = this.value.split('-');
            const url = new URL(window.location);
            url.searchParams.set('sort', sort);
            url.searchParams.set('order', order);
            window.location = url;
        });

        document.querySelectorAll('.add-to-cart').forEach(button => {
            button.addEventListener('click', async function() {
                try {
                    const response = await fetch('/cart/add.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            book_id: this.dataset.bookId
                        })
                    });
                    
                    const data = await response.json();
                    if (data.success) {
                        alert('Book added to cart!');
                    } else {
                        throw new Error(data.message);
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Failed to add book to cart. Please try again.');
                }
            });
        });
    </script>
</body>
</html>
