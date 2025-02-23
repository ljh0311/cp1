# Hybrid Cloud Architecture (IaaS, PaaS)

## EC2
PHP-based e-commerce application server hosting the bookstore website.
- Runs Apache web server with PHP
- Handles user authentication, shopping cart, and order processing
- Scalable compute environment for dynamic content

## S3
Durable object storage for:
- Book cover images
- User profile pictures
- Static assets (CSS, JS, images)
- System backups

## RDS
MySQL database for:
- User accounts and profiles
- Book catalog and inventory
- Order management
- Shopping cart data
- Secure and managed database solution with automated backups

## CloudFront
Content Delivery Network (CDN) for:
- Fast delivery of static assets
- Cached book images
- Improved global access speeds
- SSL/TLS security

## Additional Components
- Sessions management for user state
- Secure file uploads handling
- Logging and monitoring
- Database connection pooling
- Error handling and debugging capabilities 