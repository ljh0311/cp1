<?php
session_start();
require_once 'inc/dbConfig.php';
require_once 'models/Book.php';

try {
    $bookModel = new Book();
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = 12;
    $offset = ($page - 1) * $limit;
    
    $books = $bookModel->getAllBooks($limit, $offset);
} catch (Exception $e) {
    ErrorHandler::handleException($e);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Bookstore - Browse Books</title>
    <?php include "inc/head.inc.php"; ?>
    <!-- Add Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Add Bootstrap CSS if not included in head.inc.php -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #000000;
            --secondary-color: #FFFFFF;
            --accent-color: #3182CE;
            --text-color: #2D3748;
            --background-color: #F7FAFC;
            --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --transition-speed: 0.2s;
        }

        body {
            background-color: var(--background-color);
            color: var(--text-color);
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }

        .container {
            max-width: 1280px;
            padding: 2rem 1rem;
        }

        .book-card {
            background: var(--secondary-color);
            border: none;
            border-radius: 1rem;
            transition: all var(--transition-speed) ease-in-out;
            overflow: hidden;
            box-shadow: var(--card-shadow);
        }

        .book-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        .book-image {
            height: 300px;
            object-fit: cover;
            object-position: center;
            border-top-left-radius: 1rem;
            border-top-right-radius: 1rem;
        }

        .card-body {
            padding: 1.5rem;
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 0.75rem;
        }

        .card-text {
            color: #4A5568;
            font-size: 0.95rem;
            line-height: 1.5;
            margin-bottom: 1.25rem;
        }

        .btn-primary {
            background-color: var(--accent-color);
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-weight: 500;
            transition: background-color var(--transition-speed);
        }

        .btn-primary:hover {
            background-color: #2B6CB0;
            transform: translateY(-1px);
        }

        .modal-content {
            border-radius: 1rem;
            border: none;
            box-shadow: var(--card-shadow);
        }

        .modal-header {
            border-bottom: 1px solid #E2E8F0;
            padding: 1.5rem;
        }

        .modal-body {
            padding: 1.5rem;
        }

        .modal-footer {
            border-top: 1px solid #E2E8F0;
            padding: 1.5rem;
        }

        .toast {
            background: var(--secondary-color);
            border-radius: 0.5rem;
            box-shadow: var(--card-shadow);
        }

        .toast-header {
            background: transparent;
            border-bottom: 1px solid #E2E8F0;
        }

        .badge {
            padding: 0.35em 0.65em;
            font-size: 0.75em;
            font-weight: 600;
            border-radius: 9999px;
        }

        #viewCart {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 500;
        }

        .remove-item {
            padding: 0.25rem 0.5rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
        }

        h1 {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 2rem;
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            h1 {
                font-size: 1.75rem;
            }

            .book-image {
                height: 250px;
            }
        }
    </style>
</head>
<body>
    <?php include "inc/nav.inc.php"; ?>
    
    <main class="container py-5">
        <div class="row mb-4">
            <div class="col">
                <h1>Browse Our Collection</h1>
            </div>
            <div class="col-auto">
                <button class="btn btn-primary rounded-full" id="viewCart">
                    <i class="fas fa-shopping-cart"></i> Cart
                    <span class="badge bg-light text-dark ms-2" id="cartCount">0</span>
                </button>
            </div>
        </div>

        <!-- Search and Filter Section -->
        <div class="row mb-4">
            <div class="col-md-8">
                <form class="d-flex" action="books.php" method="GET">
                    <input class="form-control me-2" type="search" name="search" placeholder="Search books..." aria-label="Search">
                    <button class="btn btn-outline-primary" type="submit">Search</button>
                </form>
            </div>
            <div class="col-md-4">
                <select class="form-select" id="categoryFilter">
                    <option value="">All Categories</option>
                    <option value="fiction">Fiction</option>
                    <option value="non-fiction">Non-Fiction</option>
                    <option value="educational">Educational</option>
                </select>
            </div>
        </div>

        <!-- Books Grid -->
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php foreach ($books as $book): ?>
            <div class="col">
                <div class="card book-card h-100">
                    <img src="<?php echo htmlspecialchars($book['image_url']); ?>" 
                         class="card-img-top book-image" 
                         alt="<?php echo htmlspecialchars($book['title']); ?>">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($book['title']); ?></h5>
                        <p class="card-text"><?php echo htmlspecialchars($book['description']); ?></p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="h5 mb-0">$<?php echo number_format($book['price'], 2); ?></span>
                            <button class="btn btn-primary add-to-cart" 
                                    data-book-id="<?php echo $book['book_id']; ?>"
                                    <?php echo ($book['stock_quantity'] < 1) ? 'disabled' : ''; ?>>
                                <?php echo ($book['stock_quantity'] < 1) ? 'Out of Stock' : 'Add to Cart'; ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <!-- Add pagination links here -->
            </ul>
        </nav>
    </main>

    <?php include "inc/footer.inc.php"; ?>
    
    <script src="js/cart.js"></script>
</body>
</html>
