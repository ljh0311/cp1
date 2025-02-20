#!/bin/bash

# Update the system
yum update -y

# Install Apache web server
yum install -y httpd

# Start Apache and enable it on boot
systemctl start httpd
systemctl enable httpd

# Install PHP 8.x and required extensions
yum install -y amazon-linux-extras
amazon-linux-extras enable php8.2
yum clean metadata
yum install -y php php-cli php-mysqlnd php-pdo php-xml php-curl php-mbstring php-json php-common php-fpm

# Install Git
yum install -y git

# Create application directory
mkdir -p /var/www/html/tutoring-system

# Set proper permissions
chown -R apache:apache /var/www/html/
chmod -R 755 /var/www/html/

# Clone your repository (replace with your actual repository URL)
# git clone YOUR_REPOSITORY_URL /var/www/html/tutoring-system/

# Copy application files (if not using Git)
cat > /var/www/html/tutoring-system/dbConn.php << 'EOL'
<?php
function getDbConnection() {
    // AWS RDS MySQL connection settings
    $servername = "database1.czsa24cac7y5.us-east-1.rds.amazonaws.com";
    $username = "admin";
    $password = "KappyAdmin";
    $dbname = "tutoring_system";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    return $conn;
}
?>
EOL

# Create test connection file
cat > /var/www/html/tutoring-system/test_connection.php << 'EOL'
<?php
require_once 'dbConn.php';

try {
    $conn = getDbConnection();
    echo "Successfully connected to the database!";
    $conn->close();
} catch (Exception $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
EOL

# Set proper permissions for the PHP files
chown apache:apache /var/www/html/tutoring-system/*.php
chmod 644 /var/www/html/tutoring-system/*.php

# Configure PHP settings
sed -i 's/memory_limit = .*/memory_limit = 256M/' /etc/php.ini
sed -i 's/max_execution_time = .*/max_execution_time = 300/' /etc/php.ini
sed -i 's/upload_max_filesize = .*/upload_max_filesize = 64M/' /etc/php.ini
sed -i 's/post_max_size = .*/post_max_size = 64M/' /etc/php.ini

# Restart Apache to apply changes
systemctl restart httpd

# Install AWS CLI
curl "https://awscli.amazonaws.com/awscli-exe-linux-x86_64.zip" -o "awscliv2.zip"
yum install -y unzip
unzip awscliv2.zip
./aws/install

# Create a health check file
echo "<?php phpinfo(); ?>" > /var/www/html/tutoring-system/health.php

# Output completion message to the system log
echo "EC2 instance setup completed" >> /var/log/user-data.log 