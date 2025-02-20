# BookStore Website Setup Guide

This guide explains how to set up the BookStore website on AWS EC2 and RDS MySQL.

## Prerequisites

- AWS Account with EC2 and RDS access
- Basic understanding of AWS services
- Domain name (optional)

## AWS Setup

### 1. RDS MySQL Setup

1. Go to AWS RDS Console
2. Click "Create database"
3. Choose settings:
   ```
   Engine: MySQL 8.0.28
   Template: Free tier
   DB instance identifier: bookstore-db
   Master username: admin
   Master password: [Your-Secure-Password]
   ```
4. Under Connectivity:
   ```
   VPC: Default VPC
   Publicly accessible: Yes (for development)
   Security group: Create new
   ```
5. Create database

### 2. EC2 Instance Setup

1. Go to AWS EC2 Console
2. Launch new instance:
   ```
   AMI: Amazon Linux 2023
   Instance type: t2.micro (free tier)
   Key pair: Create new
   Security group: Allow HTTP (80), HTTPS (443), SSH (22)
   ```
3. Advanced details - User data:
   ```bash
   #!/bin/bash

   # Update system
   dnf update -y

   # Install required packages
   dnf install -y httpd php php-mysqlnd php-pdo git

   # Start and enable Apache
   systemctl start httpd
   systemctl enable httpd

   # Install Composer
   php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
   php composer-setup.php --install-dir=/usr/local/bin --filename=composer
   php -r "unlink('composer-setup.php');"

   # Clone repository
   cd /var/www/html
   git clone [YOUR-REPO-URL] .

   # Set permissions
   chown -R apache:apache /var/www/html
   chmod -R 755 /var/www/html

   # Create env file
   cat > /var/www/html/inc/config.php << 'EOL'
   <?php
   define('DB_HOST', 'YOUR-RDS-ENDPOINT');
   define('DB_NAME', 'bookstore');
   define('DB_USER', 'admin');
   define('DB_PASS', 'YOUR-DB-PASSWORD');
   define('DEBUG_MODE', false);
   EOL

   # Restart Apache
   systemctl restart httpd
   ```

### 3. Database Initialization

1. SSH into your EC2 instance:
   ```bash
   ssh -i your-key.pem ec2-user@your-ec2-ip
   ```

2. Navigate to website directory:
   ```bash
   cd /var/www/html
   ```

3. Run database setup:
   ```bash
   php database/init.php
   ```

## File Structure

```
bookstore/
├── admin/          # Admin panel files
├── css/            # Stylesheets
├── database/       # Database scripts
├── images/         # Image assets
├── inc/           # Include files
├── js/            # JavaScript files
└── models/        # PHP model classes
```

## Key Configuration Files

### 1. Database Configuration (inc/dbConfig.php)
```php
<?php
require_once __DIR__ . '/ErrorHandler.php';
define('DEBUG_MODE', false);

class Database {
    private static $instance = null;
    private $conn;

    private function __construct() {
        try {
            $this->conn = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
            ErrorHandler::setDbStatus(true);
        } catch (PDOException $e) {
            ErrorHandler::setDbStatus(false);
            ErrorHandler::handleException($e);
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->conn;
    }
}
```

### 2. Database Schema (database/schema.sql)
```sql
CREATE DATABASE IF NOT EXISTS bookstore;
USE bookstore;

CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('customer', 'admin') DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE books (
    book_id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    image_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Add other tables as needed
```

## Security Considerations

1. Update security groups:
   - RDS: Allow inbound MySQL (3306) only from EC2 IP
   - EC2: Allow inbound HTTP (80), HTTPS (443), SSH (22)

2. SSL Setup:
   ```bash
   sudo yum install -y mod_ssl
   sudo systemctl restart httpd
   ```

3. File Permissions:
   ```bash
   sudo chown -R apache:apache /var/www/html
   sudo chmod -R 755 /var/www/html
   sudo chmod -R 777 /var/www/html/images/uploads
   ```

## Maintenance

1. Log Monitoring:
   ```bash
   sudo tail -f /var/log/httpd/error_log
   ```

2. Database Backup:
   ```bash
   mysqldump -h [RDS-ENDPOINT] -u admin -p bookstore > backup.sql
   ```

3. Update Code:
   ```bash
   cd /var/www/html
   git pull origin main
   ```

## Troubleshooting

1. Check Apache status:
   ```bash
   sudo systemctl status httpd
   ```

2. Test Database Connection:
   ```bash
   php -f test_connection.php
   ```

3. Check Permissions:
   ```bash
   ls -la /var/www/html
   ```

4. View Error Logs:
   ```bash
   sudo tail -f /var/log/httpd/error_log
   ```

## Support

For issues or questions:
1. Check error logs
2. Review AWS documentation
3. Contact system administrator

## License

[Your License Information]