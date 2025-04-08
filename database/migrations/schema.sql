-- Drop tables if they exist (for clean setup)
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS cart_items;
DROP TABLE IF EXISTS books;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;

-- Create users table
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_admin BOOLEAN DEFAULT FALSE,
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active'
);

-- Create categories table
CREATE TABLE categories (
    category_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    image_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    status ENUM('active', 'inactive') DEFAULT 'active'
);

-- Create books table
CREATE TABLE books (
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
);

-- Create cart_items table
CREATE TABLE cart_items (
    cart_item_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(book_id) ON DELETE CASCADE
);

-- Create orders table
CREATE TABLE orders (
    order_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'processing', 'completed', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE RESTRICT
);

-- Create order_items table
CREATE TABLE order_items (
    order_item_id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    book_id INT NOT NULL,
    quantity INT NOT NULL,
    price_at_time DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(book_id) ON DELETE RESTRICT
);

-- Insert some sample categories
INSERT INTO categories (name, description, image_url) VALUES
('Programming', 'Explore programming languages and software development', 'images/categories/programming.jpg'),
('Web Development', 'Learn modern web technologies and frameworks', 'images/categories/web.jpg'),
('Database', 'Master database management and design', 'images/categories/database.jpg'),
('Computer Science', 'Core computer science concepts and theories', 'images/categories/cs.jpg');

-- Insert some sample books
INSERT INTO books (title, author, description, price, category_id, image_url, isbn, stock_quantity, featured) VALUES
('PHP & MySQL Web Development', 'Luke Welling', 'Learn PHP and MySQL development', 49.99, 
 (SELECT category_id FROM categories WHERE name = 'Web Development'), 
 'images/books/php-mysql.jpg', '9781119149217', 50, TRUE),
 
('Python Crash Course', 'Eric Matthes', 'A hands-on project-based introduction to programming', 39.99,
 (SELECT category_id FROM categories WHERE name = 'Programming'),
 'images/books/python-crash.jpg', '9781593279288', 75, TRUE),
 
('Database Design for Mere Mortals', 'Michael J. Hernandez', 'A comprehensive guide to database design', 54.99,
 (SELECT category_id FROM categories WHERE name = 'Database'),
 'images/books/database-design.jpg', '9780321884497', 30, TRUE);

-- Create indexes for better performance
CREATE INDEX idx_books_category ON books(category_id);
CREATE INDEX idx_books_featured ON books(featured);
CREATE INDEX idx_orders_user ON orders(user_id);
CREATE INDEX idx_cart_user ON cart_items(user_id);

-- Create a view for book statistics
CREATE VIEW book_statistics AS
SELECT 
    b.category_id,
    c.name as category_name,
    COUNT(b.book_id) as total_books,
    AVG(b.price) as average_price,
    SUM(b.stock_quantity) as total_stock
FROM books b
JOIN categories c ON b.category_id = c.category_id
GROUP BY b.category_id, c.name;

-- Create a trigger to update stock quantity after order
DELIMITER //
CREATE TRIGGER after_order_insert
AFTER INSERT ON order_items
FOR EACH ROW
BEGIN
    UPDATE books 
    SET stock_quantity = stock_quantity - NEW.quantity
    WHERE book_id = NEW.book_id;
END//
DELIMITER ;

-- Create a stored procedure to get featured books
DELIMITER //
CREATE PROCEDURE get_featured_books()
BEGIN
    SELECT 
        b.*, 
        c.name as category_name
    FROM books b
    LEFT JOIN categories c ON b.category_id = c.category_id
    WHERE b.featured = TRUE
    ORDER BY b.created_at DESC;
END//
DELIMITER ; 