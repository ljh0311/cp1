<footer class="bg-dark text-light py-5 mt-5">
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-4">
                <h5 class="mb-3">About <?php echo SITE_NAME; ?></h5>
                <p class="text-muted">Your one-stop shop for academic and professional books. We provide quality educational resources to help you succeed in your learning journey.</p>
            </div>
            <div class="col-md-2 mb-4">
                <h5 class="mb-3">Quick Links</h5>
                <ul class="list-unstyled">
                    <li><a href="/" class="text-muted text-decoration-none">Home</a></li>
                    <li><a href="/books.php" class="text-muted text-decoration-none">Books</a></li>
                    <li><a href="/categories.php" class="text-muted text-decoration-none">Categories</a></li>
                    <li><a href="/about.php" class="text-muted text-decoration-none">About Us</a></li>
                </ul>
            </div>
            <div class="col-md-3 mb-4">
                <h5 class="mb-3">Customer Service</h5>
                <ul class="list-unstyled">
                    <li><a href="/contact.php" class="text-muted text-decoration-none">Contact Us</a></li>
                    <li><a href="/faq.php" class="text-muted text-decoration-none">FAQ</a></li>
                    <li><a href="/shipping.php" class="text-muted text-decoration-none">Shipping Information</a></li>
                    <li><a href="/returns.php" class="text-muted text-decoration-none">Returns Policy</a></li>
                </ul>
            </div>
            <div class="col-md-3 mb-4">
                <h5 class="mb-3">Connect With Us</h5>
                <div class="d-flex gap-3 mb-3">
                    <a href="#" class="text-muted text-decoration-none"><i class="fab fa-facebook fa-lg"></i></a>
                    <a href="#" class="text-muted text-decoration-none"><i class="fab fa-twitter fa-lg"></i></a>
                    <a href="#" class="text-muted text-decoration-none"><i class="fab fa-instagram fa-lg"></i></a>
                    <a href="#" class="text-muted text-decoration-none"><i class="fab fa-linkedin fa-lg"></i></a>
                </div>
                <p class="text-muted mb-1">Subscribe to our newsletter</p>
                <form class="newsletter-form">
                    <div class="input-group">
                        <input type="email" class="form-control" placeholder="Your email">
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