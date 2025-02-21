<?php
// Define the root path
define('ROOT_PATH', __DIR__);

// Start session before any output
session_start();

// Load autoloader and configuration
require_once ROOT_PATH . '/inc/autoload.php';
require_once ROOT_PATH . '/inc/config.php';
require_once ROOT_PATH . '/inc/ErrorHandler.php';
require_once ROOT_PATH . '/inc/default_data.php';
require_once ROOT_PATH . '/database/DatabaseManager.php';

// Initialize variables with default data
$featured_books = DefaultData::getFeaturedBooks();
$categories = DefaultData::getCategories();
$stats = DefaultData::getStats();
$db_connected = false;
$using_fallback = false;

try {
    $db = DatabaseManager::getInstance();
    $db_connected = true;
    $using_fallback = $db->isUsingFallback();

    // Get statistics
    $stats_query = "SELECT 
        (SELECT COUNT(DISTINCT user_id) FROM orders) as total_students,
        (SELECT COUNT(*) FROM books) as total_books,
        (SELECT COUNT(*) FROM orders) as total_orders";
    
    $result = $db->query($stats_query);
    if ($row = $db->fetch($result)) {
        $stats = array_merge($stats, $row);
    }

    // Get featured books
    $books_query = "SELECT * FROM books WHERE featured = 1 ORDER BY created_at DESC LIMIT 4";
    $result = $db->query($books_query);
    $featured_books = $db->fetchAll($result);

} catch (Exception $e) {
    ErrorHandler::logError("Database error: " . $e->getMessage(), __FILE__, __LINE__);
    $using_fallback = true;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title><?php echo SITE_NAME; ?> - Your Academic Book Haven</title>
    <?php require_once ROOT_PATH . '/inc/head.inc.php'; ?>
</head>

<body>
    <?php if (!$db_connected): ?>
    <div class="alert alert-warning alert-dismissible fade show m-0" role="alert">
        <div class="container">
            <i class="fas fa-exclamation-triangle me-2"></i>
            Database connection failed. Showing demo content only.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
    <?php elseif ($using_fallback): ?>
    <div class="alert alert-info alert-dismissible fade show m-0" role="alert">
        <div class="container">
            <i class="fas fa-info-circle me-2"></i>
            Using local database. Some features may be limited.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger m-3">
            <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>

    <?php require_once ROOT_PATH . '/inc/nav.inc.php'; ?>
    <?php ErrorHandler::displayErrors(); ?>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold mb-4">Discover Your Next Favorite Book</h1>
                    <p class="lead mb-4">Access a vast collection of academic books, study materials, and resources to
                        enhance your learning journey.</p>
                    <div class="d-flex gap-3">
                        <a href="books.php" class="btn btn-primary btn-lg rounded-pill">Browse Books</a>
                        <a href="#categories" class="btn btn-outline-light btn-lg rounded-pill">View Categories</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-3 col-6">
                    <div class="stats-card text-center">
                        <div class="display-4 fw-bold text-primary mb-2">
                            <span class="counter"><?php echo number_format($stats['total_students']); ?></span>+
                        </div>
                        <p class="text-muted mb-0">Happy Students</p>
                    </div>
                </div>
                <!-- ... other stats cards ... -->
            </div>
        </div>
    </section>

    <!-- Featured Books Section -->
    <section class="py-5 bg-white">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-5">
                <div>
                    <h2 class="fw-bold mb-0">Featured Books</h2>
                    <p class="text-muted">Hand-picked books for you</p>
                </div>
                <a href="books.php" class="btn btn-outline-primary rounded-pill">View All</a>
            </div>

            <?php if (empty($featured_books)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Featured books are currently unavailable. Please check back later.
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($featured_books as $book): ?>
                        <div class="col-md-3">
                            <div class="card book-card h-100">
                                <img src="<?php echo htmlspecialchars($book['image_url']); ?>" class="card-img-top"
                                    alt="<?php echo htmlspecialchars($book['title']); ?>">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($book['title']); ?></h5>
                                    <p class="card-text text-muted"><?php echo htmlspecialchars($book['author']); ?></p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="book-price">$<?php echo number_format($book['price'], 2); ?></span>
                                        <button class="btn btn-primary rounded-pill add-to-cart"
                                            data-book-id="<?php echo $book['book_id'] ?? ''; ?>">
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
    </section>

    <!-- Categories Section -->
    <section id="categories" class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Browse by Category</h2>
                <p class="text-muted">Find the perfect book for your needs</p>
            </div>
            <div class="row g-4">
                <?php foreach ($categories as $name => $data): ?>
                    <div class="col-md-3">
                        <a href="books.php?category=<?php echo urlencode(strtolower($name)); ?>" 
                           class="text-decoration-none">
                            <div class="card category-card h-100">
                                <img src="<?php echo htmlspecialchars($data['image']); ?>"
                                     class="card-img-top" alt="<?php echo htmlspecialchars($name); ?>">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($name); ?></h5>
                                    <p class="card-text text-muted"><?php echo htmlspecialchars($data['description']); ?></p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge bg-primary rounded-pill"><?php echo $data['count']; ?> Books</span>
                                        <i class="fas fa-arrow-right text-primary"></i>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Latest Books Section -->
    <section class="py-5">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold mb-0">Latest Additions</h2>
                    <p class="text-muted">Recently added to our collection</p>
                </div>
                <a href="books.php?sort=newest" class="btn btn-outline-primary rounded-pill">View All</a>
            </div>
            <div class="row g-4">
                <?php foreach (DefaultData::getLatestBooks() as $book): ?>
                    <div class="col-md-4">
                        <div class="card book-card h-100">
                            <img src="<?php echo htmlspecialchars($book['image_url']); ?>" 
                                 class="card-img-top" alt="<?php echo htmlspecialchars($book['title']); ?>">
                            <div class="card-body">
                                <div class="badge bg-primary mb-2"><?php echo htmlspecialchars($book['category']); ?></div>
                                <h5 class="card-title"><?php echo htmlspecialchars($book['title']); ?></h5>
                                <p class="card-text text-muted"><?php echo htmlspecialchars($book['description']); ?></p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="book-price">$<?php echo number_format($book['price'], 2); ?></span>
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
        </div>
    </section>

    <!-- Statistics Section with More Details -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Our Impact</h2>
                <p class="text-muted">Growing together with our community</p>
            </div>
            <div class="row g-4">
                <div class="col-md-3 col-6">
                    <div class="stats-card text-center p-4 bg-white rounded-4 shadow-sm">
                        <div class="stats-icon mb-3">
                            <i class="fas fa-users fa-2x text-primary"></i>
                        </div>
                        <div class="display-6 fw-bold text-primary mb-2">
                            <?php echo number_format($stats['total_students']); ?>+
                        </div>
                        <p class="text-muted mb-0">Happy Students</p>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stats-card text-center p-4 bg-white rounded-4 shadow-sm">
                        <div class="stats-icon mb-3">
                            <i class="fas fa-book fa-2x text-primary"></i>
                        </div>
                        <div class="display-6 fw-bold text-primary mb-2">
                            <?php echo number_format($stats['total_books']); ?>
                        </div>
                        <p class="text-muted mb-0">Books Available</p>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stats-card text-center p-4 bg-white rounded-4 shadow-sm">
                        <div class="stats-icon mb-3">
                            <i class="fas fa-star fa-2x text-primary"></i>
                        </div>
                        <div class="display-6 fw-bold text-primary mb-2">
                            <?php echo number_format($stats['satisfaction_rate']); ?>%
                        </div>
                        <p class="text-muted mb-0">Satisfaction Rate</p>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stats-card text-center p-4 bg-white rounded-4 shadow-sm">
                        <div class="stats-icon mb-3">
                            <i class="fas fa-shopping-cart fa-2x text-primary"></i>
                        </div>
                        <div class="display-6 fw-bold text-primary mb-2">
                            <?php echo number_format($stats['total_orders']); ?>+
                        </div>
                        <p class="text-muted mb-0">Orders Completed</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action Section -->
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-10">
                    <div class="card bg-primary text-white">
                        <div class="card-body p-5 text-center">
                            <h2 class="fw-bold mb-4">Start Your Learning Journey Today</h2>
                            <p class="lead mb-4">Join thousands of students who trust us for their educational needs</p>
                            <div class="d-flex justify-content-center gap-3">
                                <a href="register.php" class="btn btn-light btn-lg rounded-pill px-5">Get Started</a>
                                <a href="contactUs.php" class="btn btn-outline-light btn-lg rounded-pill px-5">Contact Us</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include "inc/footer.inc.php"; ?>

    <script>
        // Counter Animation with error handling
        document.addEventListener('DOMContentLoaded', () => {
            try {
                const counters = document.querySelectorAll('.counter');
                const speed = 200;

                const animateCounter = (counter) => {
                    const target = parseInt(counter.innerText.replace(/,/g, ''));
                    const count = 0;
                    const increment = target / speed;

                    const updateCount = () => {
                        const current = parseInt(counter.innerText.replace(/,/g, ''));
                        if (current < target) {
                            counter.innerText = Math.ceil(current + increment).toLocaleString();
                            setTimeout(updateCount, 1);
                        } else {
                            counter.innerText = target.toLocaleString();
                        }
                    };

                    updateCount();
                };

                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            animateCounter(entry.target);
                            observer.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.5 });

                counters.forEach(counter => observer.observe(counter));
            } catch (error) {
                console.error('Error in counter animation:', error);
            }
        });
    </script>
</body>

</html>