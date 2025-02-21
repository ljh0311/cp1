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
    <style>
        .book-card {
            transition: transform 0.2s, box-shadow 0.2s;
            height: 100%;
        }
        .book-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .book-card .card-img-top {
            height: 300px;
            object-fit: cover;
            border-top-left-radius: calc(0.375rem - 1px);
            border-top-right-radius: calc(0.375rem - 1px);
        }
        .book-price {
            font-size: 1.25rem;
            font-weight: 600;
            color: #0d6efd;
        }
        .category-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 1;
        }
        .book-title {
            font-size: 1.1rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            height: 2.8rem;
        }
        .book-author {
            font-size: 0.9rem;
            color: #6c757d;
            margin-bottom: 1rem;
            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .add-to-cart {
            transition: all 0.2s;
        }
        .add-to-cart:hover {
            transform: scale(1.05);
        }
        .add-to-cart:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }
        .book-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 2rem;
            padding: 1rem;
        }
    </style>
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
            <div class="book-grid">
                <?php foreach ($books as $book): ?>
                    <div class="book-card card h-100 position-relative">
                        <?php if ($book['category']): ?>
                            <div class="category-badge">
                                <span class="badge bg-primary">
                                    <?php echo htmlspecialchars($book['category']); ?>
                                </span>
                            </div>
                        <?php endif; ?>
                        
                        <img src="<?php echo !empty($book['image_url']) ? htmlspecialchars($book['image_url']) : 'images/placeholders/book-placeholder.jpg'; ?>" 
                             class="card-img-top" 
                             alt="<?php echo htmlspecialchars($book['title']); ?>"
                             onerror="this.src='images/placeholders/book-placeholder.jpg'">
                             
                        <div class="card-body d-flex flex-column">
                            <h5 class="book-title">
                                <?php echo htmlspecialchars($book['title']); ?>
                            </h5>
                            <p class="book-author mb-2">
                                By <?php echo htmlspecialchars($book['author']); ?>
                            </p>
                            <div class="mt-auto">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="book-price">
                                        $<?php echo number_format($book['price'], 2); ?>
                                    </span>
                                    <button class="btn btn-primary rounded-pill add-to-cart" 
                                            data-book-id="<?php echo $book['book_id']; ?>"
                                            <?php echo ($book['stock_quantity'] <= 0) ? 'disabled' : ''; ?>>
                                        <?php if ($book['stock_quantity'] <= 0): ?>
                                            Out of Stock
                                        <?php else: ?>
                                            Add to Cart
                                        <?php endif; ?>
                                    </button>
                                </div>
                                <?php if ($book['stock_quantity'] > 0 && $book['stock_quantity'] <= 5): ?>
                                    <small class="text-danger d-block mt-2">
                                        Only <?php echo $book['stock_quantity']; ?> left in stock!
                                    </small>
                                <?php endif; ?>
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
                const originalText = this.innerHTML;
                
                try {
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
                    
                    const responseText = await response.text();
                    console.log('Server response:', responseText);
                    
                    if (response.status === 401) {
                        window.location.href = 'login.php?redirect=' + encodeURIComponent(window.location.pathname);
                        return;
                    }
                    
                    let data;
                    try {
                        data = JSON.parse(responseText);
                    } catch (parseError) {
                        console.error('Failed to parse server response:', responseText);
                        throw new Error('Server returned invalid JSON response');
                    }

                    if (data.success) {
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
                    this.disabled = false;
                    this.innerHTML = originalText;

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
