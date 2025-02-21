<?php
require_once '../inc/config.php';
require_once 'DatabaseManager.php';

try {
    $db = DatabaseManager::getInstance();
    
    // Start transaction
    $db->query("START TRANSACTION");

    // Insert categories if they don't exist
    $categories = [
        ['Programming', 'Learn various programming languages and software development concepts'],
        ['Web Development', 'Master web technologies and frameworks'],
        ['Database', 'Explore database management systems and design principles'],
        ['Cloud Computing', 'Learn about cloud services and architecture'],
        ['Cybersecurity', 'Understand security concepts and best practices'],
        ['Artificial Intelligence', 'Explore AI, machine learning, and data science']
    ];

    foreach ($categories as $category) {
        $db->query(
            "INSERT IGNORE INTO categories (name, description) VALUES (?, ?)",
            $category
        );
    }

    // Sample books data
    $books = [
        [
            'Python Crash Course', 'Eric Matthes', 
            'A hands-on, project-based introduction to programming', 
            39.99, 1, 'images/books/python-crash-course.jpg', '9781593279288', 50, 1
        ],
        [
            'Clean Code', 'Robert C. Martin',
            'A handbook of agile software craftsmanship',
            45.99, 1, 'images/books/clean-code.jpg', '9780132350884', 35, 1
        ],
        [
            'JavaScript: The Good Parts', 'Douglas Crockford',
            'Unearthing the excellence in JavaScript',
            29.99, 2, 'images/books/javascript-good-parts.jpg', '9780596517748', 40, 0
        ],
        [
            'Learning React', 'Alex Banks & Eve Porcello',
            'Modern patterns for developing React apps',
            49.99, 2, 'images/books/learning-react.jpg', '9781492051725', 30, 1
        ],
        [
            'Database Design for Mere Mortals', 'Michael J. Hernandez',
            'A hands-on guide to relational database design',
            54.99, 3, 'images/books/database-design.jpg', '9780321884497', 25, 0
        ],
        [
            'AWS Certified Solutions Architect', 'Ben Piper',
            'Associate SAA-C02 Exam Guide',
            59.99, 4, 'images/books/aws-certified.jpg', '9781789539233', 45, 1
        ],
        [
            'CompTIA Security+', 'Mike Chapple',
            'Get certified in cybersecurity',
            49.99, 5, 'images/books/security-plus.jpg', '9781260464009', 30, 0
        ],
        [
            'Deep Learning', 'Ian Goodfellow',
            'Comprehensive guide to deep learning',
            69.99, 6, 'images/books/deep-learning.jpg', '9780262035613', 20, 1
        ]
    ];

    // Insert books
    foreach ($books as $book) {
        $db->query(
            "INSERT IGNORE INTO books (title, author, description, price, category_id, 
             image_url, isbn, stock_quantity, featured) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
            $book
        );
    }

    // Create sample users if they don't exist
    $users = [
        ['john_doe', 'john@example.com', password_hash('password123', PASSWORD_DEFAULT), 'John Doe', 0],
        ['jane_smith', 'jane@example.com', password_hash('password123', PASSWORD_DEFAULT), 'Jane Smith', 0],
        ['bob_wilson', 'bob@example.com', password_hash('password123', PASSWORD_DEFAULT), 'Bob Wilson', 0]
    ];

    foreach ($users as $user) {
        $db->query(
            "INSERT IGNORE INTO users (username, email, password_hash, full_name, is_admin) 
             VALUES (?, ?, ?, ?, ?)",
            $user
        );
    }

    // Create sample orders
    $statuses = ['pending', 'processing', 'completed', 'cancelled'];
    $users_result = $db->query("SELECT user_id FROM users WHERE is_admin = 0");
    $users = $db->fetchAll($users_result);
    $books_result = $db->query("SELECT book_id, price FROM books");
    $books = $db->fetchAll($books_result);

    // Generate orders for the past 30 days
    for ($i = 0; $i < 20; $i++) {
        $user = $users[array_rand($users)];
        $status = $statuses[array_rand($statuses)];
        $order_date = date('Y-m-d H:i:s', strtotime("-" . rand(0, 30) . " days"));
        
        // Create order
        $db->query(
            "INSERT INTO orders (user_id, total_amount, status, created_at) 
             VALUES (?, 0, ?, ?)",
            [$user['user_id'], $status, $order_date]
        );
        
        $order_id = $db->lastInsertId();
        $total_amount = 0;
        
        // Add 1-5 random books to the order
        $num_books = rand(1, 5);
        $selected_books = array_rand($books, $num_books);
        if (!is_array($selected_books)) {
            $selected_books = [$selected_books];
        }
        
        foreach ($selected_books as $book_index) {
            $book = $books[$book_index];
            $quantity = rand(1, 3);
            $price_at_time = $book['price'];
            $total_amount += $price_at_time * $quantity;
            
            $db->query(
                "INSERT INTO order_items (order_id, book_id, quantity, price_at_time) 
                 VALUES (?, ?, ?, ?)",
                [$order_id, $book['book_id'], $quantity, $price_at_time]
            );
        }
        
        // Update order total
        $db->query(
            "UPDATE orders SET total_amount = ? WHERE order_id = ?",
            [$total_amount, $order_id]
        );
    }

    // Commit transaction
    $db->query("COMMIT");
    
    echo "Database successfully populated with sample data!\n";
    echo "Added:\n";
    echo "- " . count($categories) . " categories\n";
    echo "- " . count($books) . " books\n";
    echo "- " . count($users) . " users\n";
    echo "- 20 orders with random items\n";

} catch (Exception $e) {
    // Rollback transaction on error
    $db->query("ROLLBACK");
    echo "Error: " . $e->getMessage() . "\n";
} 