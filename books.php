<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define the root path if not already defined
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', __DIR__);
}

// Load configuration and start session
require_once 'inc/config.php';
require_once 'inc/session_config.php';

// Debug session information
error_log("=== Session Debug Info ===");
error_log("Session ID: " . session_id());
error_log("Session Status: " . session_status());
error_log("Session Data: " . print_r($_SESSION, true));
error_log("Cookie Data: " . print_r($_COOKIE, true));
error_log("=== End Session Debug ===");

// Load other required files
require_once 'inc/ErrorHandler.php';
require_once 'database/DatabaseManager.php';

try {
    $db = DatabaseManager::getInstance();
    
    // Get sorting parameters
    $sort = $_GET['sort'] ?? 'title';
    $order = $_GET['order'] ?? 'asc';
    
    // Validate sort and order
    $allowed_sorts = ['title', 'price', 'author'];
    $allowed_orders = ['asc', 'desc'];
    
    if (!in_array($sort, $allowed_sorts)) $sort = 'title';
    if (!in_array($order, $allowed_orders)) $order = 'asc';
    
    // Get books with sorting
    $books_query = $db->query(
        "SELECT b.*, c.name as category 
         FROM books b 
         LEFT JOIN categories c ON b.category_id = c.category_id 
         ORDER BY b.$sort $order"
    );
    
    $books = $db->fetchAll($books_query);
    
} catch (Exception $e) {
    error_log('Error in books.php: ' . $e->getMessage());
    $books = [];
}

// Debug session after database operations
if (DEBUG_MODE) {
    error_log("Session after DB operations - ID: " . session_id());
    error_log("Session data: " . print_r($_SESSION, true));
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Our Books</h1>
            <div class="d-flex align-items-center">
                <label for="sortBooks" class="me-2">Sort by:</label>
                <select id="sortBooks" class="form-select">
                    <option value="title-asc" <?php echo $sort === 'title' && $order === 'asc' ? 'selected' : ''; ?>>
                        Title (A-Z)
                    </option>
                    <option value="title-desc" <?php echo $sort === 'title' && $order === 'desc' ? 'selected' : ''; ?>>
                        Title (Z-A)
                    </option>
                    <option value="price-asc" <?php echo $sort === 'price' && $order === 'asc' ? 'selected' : ''; ?>>
                        Price (Low to High)
                    </option>
                    <option value="price-desc" <?php echo $sort === 'price' && $order === 'desc' ? 'selected' : ''; ?>>
                        Price (High to Low)
                    </option>
                    <option value="author-asc" <?php echo $sort === 'author' && $order === 'asc' ? 'selected' : ''; ?>>
                        Author (A-Z)
                    </option>
                    <option value="author-desc" <?php echo $sort === 'author' && $order === 'desc' ? 'selected' : ''; ?>>
                        Author (Z-A)
                    </option>
                </select>
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
                // Store the original button text
                const originalText = this.innerHTML;
                
                try {
                    // Disable button while processing
                    this.disabled = true;
                    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';

                    const response = await fetch('./cart/add.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            book_id: this.dataset.bookId,
                            quantity: 1
                        }),
                        credentials: 'same-origin'
                    });
                    
                    // Get the response text first for debugging
                    const responseText = await response.text();
                    console.log('Server response:', responseText);
                    
                    // Check if the response indicates we need to login
                    if (response.status === 401) {
                        window.location.href = 'login.php?redirect=' + encodeURIComponent(window.location.pathname);
                        return;
                    }
                    
                    // Try to parse the response as JSON
                    let data;
                    try {
                        data = JSON.parse(responseText);
                    } catch (parseError) {
                        console.error('Failed to parse server response:', responseText);
                        throw new Error('Server returned invalid JSON response');
                    }

                    if (data.success) {
                        // Show success message
                        const alert = document.createElement('div');
                        alert.className = 'alert alert-success alert-dismissible fade show position-fixed';
                        alert.style.top = '20px';
                        alert.style.right = '20px';
                        alert.style.zIndex = '1050';
                        alert.innerHTML = `
                            <div class="d-flex align-items-center">
                                <i class="fas fa-check-circle me-2"></i>
                                ${data.message || 'Book added to cart successfully!'}
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        `;
                        document.body.appendChild(alert);

                        // Update cart count if available
                        if (data.cart_count !== undefined) {
                            const cartCountElement = document.getElementById('cartCount');
                            if (cartCountElement) {
                                cartCountElement.textContent = data.cart_count;
                                cartCountElement.classList.add('cart-count-animation');
                                setTimeout(() => {
                                    cartCountElement.classList.remove('cart-count-animation');
                                }, 300);
                            }
                        }
                    } else {
                        throw new Error(data.message || 'Failed to add book to cart');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    // Show error message
                    const alert = document.createElement('div');
                    alert.className = 'alert alert-danger alert-dismissible fade show position-fixed';
                    alert.style.top = '20px';
                    alert.style.right = '20px';
                    alert.style.zIndex = '1050';
                    alert.innerHTML = `
                        <div class="d-flex align-items-center">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            ${error.message || 'Failed to add book to cart. Please try again.'}
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    `;
                    document.body.appendChild(alert);
                } finally {
                    // Re-enable button and restore original text
                    this.disabled = false;
                    this.innerHTML = originalText;

                    // Remove alerts after 3 seconds
                    setTimeout(() => {
                        document.querySelectorAll('.alert').forEach(alert => {
                            alert.remove();
                        });
                    }, 3000);
                }
            });
        });
    </script>
</body>
</html>
