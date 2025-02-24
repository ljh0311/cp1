<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define root path if not already defined
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', __DIR__);
}

require_once 'inc/config.php';
require_once 'inc/session_config.php';
require_once 'database/DatabaseManager.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    $db = DatabaseManager::getInstance();
    
    // Get total number of books
    $books_count = $db->fetchOne("SELECT COUNT(*) FROM books");
    
    // Get total number of categories
    $categories_count = $db->fetchOne("SELECT COUNT(*) FROM categories");
    
    // Get total number of customers (users)
    $customers_count = $db->fetchOne("SELECT COUNT(*) FROM users WHERE role = 'customer'");
    
    // Calculate satisfaction rate based on reviews (if you have a reviews table)
    $satisfaction_query = "SELECT 
        ROUND(
            (COUNT(CASE WHEN rating >= 4 THEN 1 END) * 100.0) / NULLIF(COUNT(*), 0)
        ) as satisfaction_rate
        FROM reviews";
    $satisfaction_rate = $db->fetchOne($satisfaction_query) ?? 0;
    
} catch (Exception $e) {
    error_log('Error in about.php: ' . $e->getMessage());
    // Set default values in case of database error
    $books_count = 0;
    $categories_count = 0;
    $customers_count = 0;
    $satisfaction_rate = 0;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>About Us - <?php echo SITE_NAME; ?></title>
    <!-- CSS Files -->
    <link rel="stylesheet" href="https://unpkg.com/bootstrap@5.3.2/dist/css/bootstrap.min.css" />
    <link rel="stylesheet" href="https://unpkg.com/bs-brain@2.0.3/components/abouts/about-5/assets/css/about-5.css" />
    <link rel="stylesheet" href="https://unpkg.com/bs-brain@2.0.3/components/abouts/about-1/assets/css/about-1.css" />
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/animate.css/animate.min.css" rel="stylesheet">
    <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">
    <?php
    require_once 'inc/head.inc.php';
    ?>
    <style>
        .hero-about {
            background: linear-gradient(rgba(13, 110, 253, 0.8), rgba(13, 110, 253, 0.9)), url('images/hero-bg.jpg');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 5rem 0;
            margin-bottom: 3rem;
        }
        .about-card {
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .about-card:hover {
            transform: translateY(-5px);
        }
        .about-card img {
            height: 250px;
            object-fit: cover;
        }
        .stats-section {
            background-color: #f8f9fa;
            padding: 4rem 0;
            margin: 2rem 0;
        }
        .stat-item {
            text-align: center;
            padding: 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s ease;
        }
        .stat-item:hover {
            transform: translateY(-5px);
        }
        .stat-item .number {
            font-size: 2.5rem;
            font-weight: 700;
            color: #0d6efd;
            margin-bottom: 0.5rem;
        }
        .stat-item p {
            color: #6c757d;
            font-size: 1.1rem;
            font-weight: 500;
        }
        .timeline {
            position: relative;
            padding: 2rem 0;
        }
        .timeline-item {
            padding: 2rem;
            border-left: 3px solid #0d6efd;
            margin-bottom: 2rem;
            position: relative;
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -10px;
            top: 2rem;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #0d6efd;
        }
        .testimonial-card {
            background: white;
            padding: 2rem;
            border-radius: 1rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        .testimonial-card img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 1rem;
        }
    </style>
</head>

<body>

    <?php
    require_once 'inc/nav.inc.php';
    ?>
    <section class="hero-about">
        <div class="container text-center">
            <h1 class="display-4 mb-4">About Academic Book Haven</h1>
            <p class="lead mb-0">Your Premier Destination for IT Educational Resources</p>
        </div>
    </section>

    <main class="container">
        <!-- Vision & Mission -->
        <section class="py-5">
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="about-card h-100">
                        <div class="card-body p-4">
                            <div class="text-primary mb-4">
                                <i class="fas fa-eye fa-3x"></i>
                            </div>
                            <h2 class="h4 mb-3">Our Vision</h2>
                            <p class="mb-0">To be the leading online platform for IT educational resources, making quality technical literature accessible to students and professionals worldwide.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="about-card h-100">
                        <div class="card-body p-4">
                            <div class="text-primary mb-4">
                                <i class="fas fa-bullseye fa-3x"></i>
                            </div>
                            <h2 class="h4 mb-3">Our Mission</h2>
                            <p class="mb-0">To streamline access to high-quality IT educational materials, fostering learning and professional growth in the technology sector through carefully curated resources.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Stats Section -->
        <section class="stats-section">
            <div class="container">
                <div class="row g-4">
                    <div class="col-md-3">
                        <div class="stat-item">
                            <div class="number"><?php echo number_format($books_count); ?>+</div>
                            <p class="mb-0">IT Books</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-item">
                            <div class="number"><?php echo number_format($categories_count); ?>+</div>
                            <p class="mb-0">Tech Categories</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-item">
                            <div class="number"><?php echo number_format($customers_count); ?>+</div>
                            <p class="mb-0">Happy Customers</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-item">
                            <div class="number"><?php echo $satisfaction_rate; ?>%</div>
                            <p class="mb-0">Satisfaction Rate</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Why Choose Us -->
        <section class="py-5">
            <h2 class="text-center mb-5">Why Choose Academic Book Haven</h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-cubes fa-3x text-primary mb-3"></i>
                            <h3 class="h5 mb-3">Comprehensive Collection</h3>
                            <p class="mb-0">Access to a vast library of IT books covering programming, networking, cybersecurity, and more.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-shipping-fast fa-3x text-primary mb-3"></i>
                            <h3 class="h5 mb-3">Quick Delivery</h3>
                            <p class="mb-0">Fast and reliable shipping to ensure you get your educational materials when you need them.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-headset fa-3x text-primary mb-3"></i>
                            <h3 class="h5 mb-3">Expert Support</h3>
                            <p class="mb-0">Dedicated customer service to help you find the right resources for your learning journey.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Timeline -->
        <section class="py-5">
            <h2 class="text-center mb-5">Our Journey</h2>
            <div class="timeline">
                <div class="timeline-item">
                    <h3 class="h5">2023</h3>
                    <p class="mb-0">Expanded our collection to include cloud computing and AI/ML resources</p>
                </div>
                <div class="timeline-item">
                    <h3 class="h5">2022</h3>
                    <p class="mb-0">Launched mobile app for easier access to our catalog</p>
                </div>
                <div class="timeline-item">
                    <h3 class="h5">2021</h3>
                    <p class="mb-0">Introduced digital book options and learning paths</p>
                </div>
                <div class="timeline-item">
                    <h3 class="h5">2020</h3>
                    <p class="mb-0">Started Academic Book Haven with a focus on core IT subjects</p>
                </div>
            </div>
        </section>

        <!-- Testimonials -->
        <section class="py-5">
            <h2 class="text-center mb-5">What Our Customers Say</h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="testimonial-card text-center">
                        <img src="images/student1.jpg" alt="John Doe" class="mx-auto">
                        <h4 class="h5 mb-3">John Doe</h4>
                        <p class="text-muted mb-3">Software Developer</p>
                        <p class="mb-0">"Found exactly what I needed to advance my programming skills. The collection is impressive!"</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="testimonial-card text-center">
                        <img src="images/student2.jpg" alt="Jane Smith" class="mx-auto">
                        <h4 class="h5 mb-3">Jane Smith</h4>
                        <p class="text-muted mb-3">IT Student</p>
                        <p class="mb-0">"Great resource for my studies. The prices are student-friendly and delivery is always on time."</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="testimonial-card text-center">
                        <img src="images/student5.jpg" alt="Mike Johnson" class="mx-auto">
                        <h4 class="h5 mb-3">Mike Johnson</h4>
                        <p class="text-muted mb-3">System Administrator</p>
                        <p class="mb-0">"The technical books here have helped me stay updated with the latest in IT infrastructure."</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php
    require_once 'inc/footer.inc.php';
    ?>
    <script src="assets/vendor/purecounter/purecounter_vanilla.js"></script>
    <script src="assets/vendor/aos/aos.js"></script>
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>
    <script src="assets/vendor/php-email-form/validate.js"></script>
    <script src="js/purecounter.js"></script>
</body>

</html>