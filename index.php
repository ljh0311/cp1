<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define root path if not already defined
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', __DIR__);
}

// Load configuration and start session BEFORE any output
require_once 'inc/config.php';
require_once 'inc/session_config.php';

// Debug session if needed
if (DEBUG_MODE) {
    error_log("Session status: " . session_status());
    error_log("Session ID: " . session_id());
    error_log("Session data: " . print_r($_SESSION, true));
}

// Load other required files
require_once 'inc/ErrorHandler.php';
require_once 'database/DatabaseManager.php';

// Initialize empty variables
$featured_books = [];
$categories = [];
$stats = [
    'total_students' => 0,
    'total_books' => 0,
    'total_orders' => 0,
    'satisfaction_rate' => 0
];
$db_connected = false;

try {
    $db = DatabaseManager::getInstance();
    $db_connected = true;

    // Get statistics
    $stats_query = "SELECT 
        (SELECT COUNT(DISTINCT user_id) FROM orders) as total_students,
        (SELECT COUNT(*) FROM books) as total_books,
        (SELECT COUNT(*) FROM orders) as total_orders,
        (SELECT COALESCE(ROUND((COUNT(CASE WHEN status = 'completed' THEN 1 END) * 100.0 / 
            NULLIF(COUNT(*), 0)), 0), 0) FROM orders) as satisfaction_rate";
    
    $result = $db->query($stats_query);
    if ($row = $db->fetch($result)) {
        $stats = array_merge($stats, $row);
    }

    // Get categories with book counts
    $categories_query = "SELECT 
        c.name,
        c.image_url as image,
        c.description,
        COUNT(b.book_id) as count
        FROM categories c
        LEFT JOIN books b ON b.category_id = c.category_id
        GROUP BY c.category_id, c.name, c.image_url, c.description
        ORDER BY c.name";
    
    $result = $db->query($categories_query);
    $categories_data = $db->fetchAll($result);
    
    // Format categories data
    foreach ($categories_data as $category) {
        $categories[$category['name']] = [
            'image' => $category['image'],
            'description' => $category['description'],
            'count' => (int)$category['count']
        ];
    }

    // Get featured books
    $books_query = "SELECT b.*, c.name as category 
                    FROM books b 
                    LEFT JOIN categories c ON b.category_id = c.category_id 
                    WHERE b.featured = 1 
                    ORDER BY b.created_at DESC LIMIT 4";
    $result = $db->query($books_query);
    $featured_books = $db->fetchAll($result);

    // Get latest books
    $latest_books = $db->query("SELECT b.*, c.name as category 
                               FROM books b 
                               LEFT JOIN categories c ON b.category_id = c.category_id 
                               ORDER BY b.created_at DESC 
                               LIMIT 6");

} catch (Exception $e) {
    ErrorHandler::logError("Database error: " . $e->getMessage(), __FILE__, __LINE__);
    $featured_books = [];
    $latest_books = [];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title><?php echo SITE_NAME; ?> - Your Academic Book Haven</title>
    <?php require_once 'inc/head.inc.php'; ?>
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
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger m-3">
            <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>

    <?php require_once 'inc/nav.inc.php'; ?>
    <?php ErrorHandler::displayErrors(); ?>

    <!-- Hero Section -->
    <style>
        .hero {
            background: linear-gradient(rgba(13, 110, 253, 0.8), rgba(13, 110, 253, 0.9)), url('images/hero-bg.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            min-height: 80vh;
            display: flex;
            align-items: center;
            position: relative;
            margin-top: -56px; /* Negative margin to pull up under navbar */
            padding-top: 76px; /* Add padding to account for navbar */
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.2);
            pointer-events: none;
        }

        .hero .container {
            position: relative;
            z-index: 1;
        }

        .hero h1 {
            font-size: 4rem;
            font-weight: 800;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            margin-bottom: 1.5rem;
        }

        .hero p {
            font-size: 1.5rem;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
            margin-bottom: 2.5rem;
            opacity: 0.9;
        }

        .hero .btn {
            padding: 1rem 2.5rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }

        .hero .btn-light {
            background: white;
            color: #0d6efd;
        }

        .hero .btn-light:hover {
            background: #f8f9fa;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .hero .btn-outline-light {
            border-width: 2px;
        }

        .hero .btn-outline-light:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .hero {
                min-height: 60vh;
            }
            .hero h1 {
                font-size: 2.5rem;
            }
            .hero p {
                font-size: 1.1rem;
            }
            .hero .btn {
                padding: 0.75rem 1.5rem;
            }
        }
    </style>

    <!-- Hero Section -->
    <section class="hero text-white">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8 col-md-10">
                    <h1 class="mb-4">Discover Your Next Favorite Book</h1>
                    <p class="lead mb-4">Access a vast collection of academic books, study materials, and resources.</p>
                    <div class="d-flex gap-3">
                        <a href="books.php" class="btn btn-light btn-lg rounded-pill">Browse Books</a>
                        <a href="#featured" class="btn btn-outline-light btn-lg rounded-pill">Featured Books</a>
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
    <section id="featured" class="py-5">
        <div class="container">
            <h2 class="text-center mb-4">Featured Books</h2>
            <?php if (empty($featured_books)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    No featured books available at the moment.
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($featured_books as $book): ?>
                        <div class="col-md-3">
                            <div class="card book-card h-100">
                                <img src="<?php echo htmlspecialchars($book['image_url']); ?>" 
                                     class="card-img-top" 
                                     alt="<?php echo htmlspecialchars($book['title']); ?>">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($book['title']); ?></h5>
                                    <p class="card-text text-muted"><?php echo htmlspecialchars($book['author']); ?></p>
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
            <?php if (empty($categories)): ?>
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle me-2"></i>
                    No categories available at the moment.
                </div>
            <?php else: ?>
            <div class="row g-4">
                <?php 
                // Define icons for each category (add more as needed)
                $category_icons = [
                    'Programming' => 'fa-code',
                    'Database' => 'fa-database',
                    'Web Development' => 'fa-globe',
                    'Networking' => 'fa-network-wired',
                    'Security' => 'fa-shield-alt',
                    'Operating Systems' => 'fa-laptop',
                    'Mobile Development' => 'fa-mobile-alt',
                    'Data Science' => 'fa-chart-bar',
                    'Artificial Intelligence' => 'fa-robot',
                    'Cloud Computing' => 'fa-cloud',
                    'Default' => 'fa-book' // Default icon
                ];
                ?>
                <?php foreach ($categories as $name => $data): ?>
                    <div class="col-md-3">
                        <a href="books.php?category=<?php echo urlencode(strtolower($name)); ?>" 
                           class="text-decoration-none">
                            <div class="card category-card h-100 border-0 shadow-sm">
                                <div class="card-body text-center">
                                    <div class="category-icon mb-3">
                                        <i class="fas <?php echo $category_icons[htmlspecialchars($name)] ?? $category_icons['Default']; ?> fa-3x text-primary"></i>
                                    </div>
                                    <h5 class="card-title"><?php echo htmlspecialchars($name); ?></h5>
                                    <p class="card-text text-muted small"><?php echo htmlspecialchars($data['description']); ?></p>
                                    <div class="mt-3">
                                        <span class="badge bg-primary rounded-pill"><?php echo $data['count']; ?> Books</span>
                                    </div>
                                </div>
                                <div class="card-footer bg-transparent border-0 text-center">
                                    <span class="text-primary">Browse Category <i class="fas fa-arrow-right ms-1"></i></span>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
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
            <?php if (empty($latest_books)): ?>
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle me-2"></i>
                    No books available at the moment.
                </div>
            <?php else: ?>
            <div class="row g-4">
                <?php foreach ($latest_books as $book): ?>
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
            <?php endif; ?>
        </div>
    </section>

    <!-- Statistics Section with More Details -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Our Impact</h2>
                <p class="text-muted">Growing together with our community</p>
            </div>
            <?php if ($stats['total_students'] == 0 && $stats['total_books'] == 0 && 
                      $stats['total_orders'] == 0 && $stats['satisfaction_rate'] == 0): ?>
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle me-2"></i>
                    No statistics available at the moment.
                </div>
            <?php else: ?>
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
            <?php endif; ?>
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
                    const target = parseFloat(counter.innerText.replace(/[,%]/g, '')) || 0;
                    const isPercentage = counter.innerText.includes('%');
                    let current = 0;
                    const increment = target / speed;

                    const updateCount = () => {
                        if (current < target) {
                            current = Math.min(current + increment, target);
                            counter.innerText = isPercentage 
                                ? Math.round(current).toLocaleString() + '%'
                                : Math.round(current).toLocaleString();
                            requestAnimationFrame(updateCount);
                        }
                    };

                    updateCount();
                };

                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting && entry.target.classList.contains('counter')) {
                            animateCounter(entry.target);
                            observer.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.5 });

                counters.forEach(counter => {
                    if (counter) {
                        observer.observe(counter);
                    }
                });
            } catch (error) {
                console.error('Error in counter animation:', error);
            }
        });

        // Add to cart functionality
        document.querySelectorAll('.add-to-cart').forEach(button => {
            button.addEventListener('click', async function(e) {
                e.preventDefault();
                try {
                    const bookId = this.dataset.bookId;
                    if (!bookId) {
                        throw new Error('Book ID is missing');
                    }

                    const response = await fetch('/cart/add.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            book_id: bookId
                        })
                    });
                    
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }

                    const data = await response.json();
                    if (data.success) {
                        // Show success message
                        const alert = document.createElement('div');
                        alert.className = 'alert alert-success alert-dismissible fade show';
                        alert.innerHTML = `
                            <div class="container">
                                <i class="fas fa-check-circle me-2"></i>
                                Book added to cart successfully!
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        `;
                        document.body.insertBefore(alert, document.body.firstChild);
                    } else {
                        throw new Error(data.message || 'Failed to add book to cart');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    // Show error message
                    const alert = document.createElement('div');
                    alert.className = 'alert alert-danger alert-dismissible fade show';
                    alert.innerHTML = `
                        <div class="container">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            ${error.message || 'Failed to add book to cart. Please try again.'}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    `;
                    document.body.insertBefore(alert, document.body.firstChild);
                }
            });
        });
    </script>
</body>

</html>