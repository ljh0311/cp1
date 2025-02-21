<footer class="bg-dark text-light py-5 mt-5">
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-4">
                <h5 class="mb-3">About <?php echo SITE_NAME; ?></h5>
                <p class="text-muted">Your premier destination for IT educational resources. We provide quality technical books to help you excel in your technology learning journey.</p>
                <div class="mt-3">
                    <p class="mb-1"><i class="fas fa-phone me-2"></i> +65 6123 4567</p>
                    <p class="mb-1"><i class="fas fa-envelope me-2"></i> support@academicbookhaven.com</p>
                    <p class="mb-0"><i class="fas fa-map-marker-alt me-2"></i> 123 Book Street, Singapore 123456</p>
                </div>
            </div>
            <div class="col-md-2 mb-4">
                <h5 class="mb-3">Quick Links</h5>
                <ul class="list-unstyled">
                    <li><a href="/index.php" class="text-muted text-decoration-none">Home</a></li>
                    <li><a href="/books.php" class="text-muted text-decoration-none">Books</a></li>
                    <li><a href="/about.php" class="text-muted text-decoration-none">About Us</a></li>
                    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                        <li><a href="/admin/admin_dashboard.php" class="text-muted text-decoration-none">Admin Dashboard</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="col-md-3 mb-4">
                <h5 class="mb-3">Customer Service</h5>
                <ul class="list-unstyled">
                    <li><a href="/profile.php" class="text-muted text-decoration-none">My Account</a></li>
                    <li><a href="/orders.php" class="text-muted text-decoration-none">Order History</a></li>
                    <li><a href="/cart.php" class="text-muted text-decoration-none">Shopping Cart</a></li>
                    <li><a href="/contact.php" class="text-muted text-decoration-none">Contact Us</a></li>
                </ul>
            </div>
            <div class="col-md-3 mb-4">
                <h5 class="mb-3">Connect With Us</h5>
                <div class="d-flex gap-3 mb-3">
                    <a href="https://facebook.com" class="text-muted text-decoration-none" target="_blank">
                        <i class="fab fa-facebook fa-lg"></i>
                    </a>
                    <a href="https://twitter.com" class="text-muted text-decoration-none" target="_blank">
                        <i class="fab fa-twitter fa-lg"></i>
                    </a>
                    <a href="https://instagram.com" class="text-muted text-decoration-none" target="_blank">
                        <i class="fab fa-instagram fa-lg"></i>
                    </a>
                    <a href="https://linkedin.com" class="text-muted text-decoration-none" target="_blank">
                        <i class="fab fa-linkedin fa-lg"></i>
                    </a>
                </div>
                <p class="text-muted mb-1">Subscribe to our newsletter</p>
                <form class="newsletter-form" id="newsletterForm" action="/process_newsletter.php" method="POST">
                    <div class="input-group">
                        <input type="email" class="form-control" placeholder="Your email" required>
                        <button class="btn btn-primary" type="submit">Subscribe</button>
                    </div>
                </form>
            </div>
        </div>
        <hr class="my-4 bg-secondary">
        <div class="row align-items-center">
            <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                <p class="text-muted mb-0">&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
            </div>
            <div class="col-md-6 text-center text-md-end">
                <img src="/images/payment-methods.png" alt="Payment methods" class="img-fluid" style="max-height: 30px;">
            </div>
        </div>
    </div>
</footer>

<script>
document.getElementById('newsletterForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    try {
        const form = e.target;
        const response = await fetch(form.action, {
            method: 'POST',
            body: new FormData(form)
        });
        const data = await response.json();
        
        if (data.success) {
            alert('Thank you for subscribing!');
            form.reset();
        } else {
            throw new Error(data.message || 'Subscription failed');
        }
    } catch (error) {
        alert(error.message);
    }
});
</script>