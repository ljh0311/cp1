# Online Bookstore E-Commerce Platform

A cloud-based e-commerce platform for selling books, built with PHP and deployed on AWS infrastructure. The platform provides a seamless shopping experience with features like user authentication, shopping cart management, secure payments, and order processing.

## 🌐 Live Access

- **Production URL**: https://bookstore.example.com
- **Admin Panel**: https://bookstore.example.com/admin

## 🏗️ Architecture

This project implements a cloud architecture utilizing AWS services:

### Core Components

- **Web Server**: AWS EC2 running Apache/PHP
- **Database**: AWS RDS MySQL
- **File Storage**: Local file system for static assets and uploads

### Key Features

- User authentication and profile management
- Book catalog with search and filtering
- Shopping cart and checkout system
- Order processing and tracking
- Admin panel for inventory management
- Secure payment processing
- Image upload and management
- Session management
- Responsive design

## 🚀 Getting Started

### Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache web server
- Composer for dependency management

### Local Development Setup

1. Clone the repository:
   ```bash
   git clone [your-repository-url]
   cd [project-directory]
   ```

2. Configure your database:
   - Create a new MySQL database
   - Import the schema from `database/schema.sql`
   - Copy `config.example.php` to `config.php` and update the credentials

3. Start the development server:
   ```bash
   ./server.bat
   ```
   or
   ```bash
   php -S localhost:8000 router.php
   ```

4. Access the application at `http://localhost:8000`

### Environment Configuration

Create the following configuration files:
- `config.php` - Database and application settings
- `php.ini` - PHP configuration
- `.env` - Environment variables (for development)

## 📁 Project Structure

```
├── admin/           # Admin panel files
├── database/        # Database schemas and migrations
├── inc/            # PHP includes and utilities
├── public/         # Publicly accessible files
├── css/            # Stylesheets
├── js/             # JavaScript files
├── images/         # Static images
├── uploads/        # User uploaded content
└── sessions/       # Session storage
```

## 🔒 Security

- SSL/TLS encryption
- Secure session management
- Password hashing and salting
- Input validation and sanitization
- Protected file uploads
- Regular security updates

## 🔧 Maintenance

- Daily automated backups
- Error logging and debugging
- Regular performance optimization
- Monthly maintenance windows

## 📈 Scaling

The application is designed to handle:
- 1,000-5,000 monthly active users (short term)
- 10,000-20,000 monthly active users (long term)
- 50-500 daily transactions
- 2-5GB monthly storage growth

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## 📝 License

[Your License] - See LICENSE file for details

## 📞 Support

For support and queries, please contact:
- Email: [your-support-email]
- Issue Tracker: [your-issue-tracker-url] 