<?php
require_once '../inc/config.php';
require_once '../database/DatabaseManager.php';

try {
    $db = DatabaseManager::getInstance();
    
    echo "Starting database population...\n\n";

    // Create tables first
    $tables_sql = [
        // Users table
        "CREATE TABLE IF NOT EXISTS users (
            user_id INT PRIMARY KEY AUTO_INCREMENT,
            username VARCHAR(50) NOT NULL UNIQUE,
            email VARCHAR(100) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            full_name VARCHAR(100) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            is_admin BOOLEAN DEFAULT FALSE,
            status ENUM('active', 'inactive', 'suspended') DEFAULT 'active'
        )",

        // Categories table
        "CREATE TABLE IF NOT EXISTS categories (
            category_id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(50) NOT NULL UNIQUE,
            description TEXT,
            image_url VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            status ENUM('active', 'inactive') DEFAULT 'active'
        )",

        // Books table
        "CREATE TABLE IF NOT EXISTS books (
            book_id INT PRIMARY KEY AUTO_INCREMENT,
            title VARCHAR(255) NOT NULL,
            author VARCHAR(100) NOT NULL,
            description TEXT,
            price DECIMAL(10, 2) NOT NULL,
            category_id INT,
            image_url VARCHAR(255),
            isbn VARCHAR(13) UNIQUE,
            stock_quantity INT DEFAULT 0,
            featured BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            status ENUM('available', 'out_of_stock', 'discontinued') DEFAULT 'available',
            FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE SET NULL
        )",

        // Cart items table
        "CREATE TABLE IF NOT EXISTS cart_items (
            cart_item_id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            book_id INT NOT NULL,
            quantity INT NOT NULL DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
            FOREIGN KEY (book_id) REFERENCES books(book_id) ON DELETE CASCADE
        )",

        // Orders table
        "CREATE TABLE IF NOT EXISTS orders (
            order_id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            total_amount DECIMAL(10, 2) NOT NULL,
            status ENUM('pending', 'processing', 'completed', 'cancelled') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE RESTRICT
        )",

        // Remember tokens table
        "CREATE TABLE IF NOT EXISTS remember_tokens (
            token_id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            token VARCHAR(64) NOT NULL,
            expires_at TIMESTAMP NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
            INDEX idx_token (token),
            INDEX idx_expires (expires_at)
        )"
    ];

    // Execute table creation
    foreach ($tables_sql as $sql) {
        try {
            $db->query($sql);
            echo "Table created successfully\n";
        } catch (Exception $e) {
            echo "Error creating table: " . $e->getMessage() . "\n";
        }
    }

    // Insert sample categories
    $categories_data = [
        ['Programming', 'Explore programming languages and software development', 'images/categories/programming.jpg'],
        ['Web Development', 'Learn modern web technologies and frameworks', 'images/categories/web.jpg'],
        ['Database', 'Master database management and design', 'images/categories/database.jpg'],
        ['Computer Science', 'Core computer science concepts and theories', 'images/categories/cs.jpg']
    ];

    foreach ($categories_data as $category) {
        try {
            $db->query(
                "INSERT INTO categories (name, description, image_url) VALUES (:name, :description, :image_url)",
                [
                    ':name' => $category[0],
                    ':description' => $category[1],
                    ':image_url' => $category[2]
                ]
            );
            echo "Category '{$category[0]}' inserted successfully\n";
        } catch (Exception $e) {
            echo "Error inserting category '{$category[0]}': " . $e->getMessage() . "\n";
        }
    }

    // Insert sample books
    $books_data = [
        [
            'PHP & MySQL Web Development',
            'Luke Welling',
            'Learn PHP and MySQL development',
            49.99,
            'Web Development',
            'images/books/php-mysql.jpg',
            '9781119149217',
            50,
            true
        ],
        [
            'Python Crash Course',
            'Eric Matthes',
            'A hands-on project-based introduction to programming',
            39.99,
            'Programming',
            'images/books/python-crash.jpg',
            '9781593279288',
            75,
            true
        ],
        [
            'Database Design for Mere Mortals',
            'Michael J. Hernandez',
            'A comprehensive guide to database design',
            54.99,
            'Database',
            'images/books/database-design.jpg',
            '9780321884497',
            30,
            true
        ]
    ];

    foreach ($books_data as $book) {
        try {
            // Get category ID
            $stmt = $db->query("SELECT category_id FROM categories WHERE name = :name", [':name' => $book[4]]);
            $category = $db->fetch($stmt);
            
            if ($category) {
                $db->query(
                    "INSERT INTO books (title, author, description, price, category_id, image_url, isbn, stock_quantity, featured) 
                     VALUES (:title, :author, :description, :price, :category_id, :image_url, :isbn, :stock_quantity, :featured)",
                    [
                        ':title' => $book[0],
                        ':author' => $book[1],
                        ':description' => $book[2],
                        ':price' => $book[3],
                        ':category_id' => $category['category_id'],
                        ':image_url' => $book[5],
                        ':isbn' => $book[6],
                        ':stock_quantity' => $book[7],
                        ':featured' => $book[8]
                    ]
                );
                echo "Book '{$book[0]}' inserted successfully\n";
            }
        } catch (Exception $e) {
            echo "Error inserting book '{$book[0]}': " . $e->getMessage() . "\n";
        }
    }

    // Create sample admin user
    $admin_password = password_hash('Admin123!', PASSWORD_DEFAULT);
    try {
        $db->query(
            "INSERT INTO users (username, email, password_hash, full_name, is_admin, status) 
             VALUES (:username, :email, :password_hash, :full_name, :is_admin, :status)",
            [
                ':username' => 'admin',
                ':email' => 'admin@example.com',
                ':password_hash' => $admin_password,
                ':full_name' => 'Admin User',
                ':is_admin' => true,
                ':status' => 'active'
            ]
        );
        echo "Admin user created successfully\n";
    } catch (Exception $e) {
        echo "Error creating admin user: " . $e->getMessage() . "\n";
    }

    echo "\nDatabase population completed successfully!\n";
    echo "You can now log in with:\n";
    echo "Username: admin\n";
    echo "Password: Admin123!\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} 