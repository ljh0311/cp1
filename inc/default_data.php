<?php
// Default data to be used when database connection fails

class DefaultData {
    public static function getFeaturedBooks() {
        return [
            [
                'book_id' => 1,
                'title' => 'Introduction to Programming',
                'author' => 'John Smith',
                'price' => 29.99,
                'image_url' => 'images/placeholders/book1.jpg',
                'description' => 'A comprehensive guide to programming fundamentals.',
                'featured' => 1,
                'category' => 'Programming'
            ],
            [
                'book_id' => 2,
                'title' => 'Web Development Essentials',
                'author' => 'Jane Doe',
                'price' => 34.99,
                'image_url' => 'images/placeholders/book2.jpg',
                'description' => 'Master the core concepts of modern web development.',
                'featured' => 1,
                'category' => 'Web Development'
            ],
            [
                'book_id' => 3,
                'title' => 'Database Design Patterns',
                'author' => 'Mike Johnson',
                'price' => 39.99,
                'image_url' => 'images/placeholders/book3.jpg',
                'description' => 'Learn effective database design strategies.',
                'featured' => 1,
                'category' => 'Database'
            ],
            [
                'book_id' => 4,
                'title' => 'Cloud Computing Fundamentals',
                'author' => 'Sarah Wilson',
                'price' => 44.99,
                'image_url' => 'images/placeholders/book4.jpg',
                'description' => 'Understanding cloud infrastructure and services.',
                'featured' => 1,
                'category' => 'Cloud Computing'
            ]
        ];
    }

    public static function getCategories() {
        return [
            'Programming' => [
                'image' => 'images/placeholders/category-programming.jpg',
                'description' => 'Explore programming languages and software development',
                'count' => 25
            ],
            'Web Development' => [
                'image' => 'images/placeholders/category-web.jpg',
                'description' => 'Learn modern web technologies and frameworks',
                'count' => 30
            ],
            'Database' => [
                'image' => 'images/placeholders/category-db.jpg',
                'description' => 'Master database management and design',
                'count' => 15
            ],
            'Cloud Computing' => [
                'image' => 'images/placeholders/category-cloud.jpg',
                'description' => 'Discover cloud platforms and services',
                'count' => 20
            ]
        ];
    }

    public static function getStats() {
        return [
            'total_students' => 500,
            'total_books' => 90,
            'total_orders' => 1200,
            'satisfaction_rate' => 95,
            'total_categories' => 4,
            'avg_rating' => 4.5
        ];
    }

    public static function getLatestBooks() {
        return array_slice(self::getFeaturedBooks(), 0, 3);
    }

    public static function getPopularBooks() {
        $books = self::getFeaturedBooks();
        shuffle($books);
        return array_slice($books, 0, 4);
    }
}
?> 