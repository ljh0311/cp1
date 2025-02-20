<footer class="bg-white border-top">
    <div class="container py-5">
        <!-- Main Footer Content -->
        <div class="row g-4">
            <!-- Brand Section -->
            <div class="col-lg-4">
                <div class="footer-brand mb-4">
                    <img src="images/logo.png" alt="Logo" width="40" height="40" class="rounded-circle">
                    <span class="ms-2 h5">BookStore</span>
                </div>
                <p class="text-muted">Your one-stop destination for all your reading needs. Discover a world of knowledge and imagination.</p>
                <div class="social-links mt-4">
                    <?php
                    $social_links = [
                        'facebook-f' => '#',
                        'twitter' => '#',
                        'instagram' => '#',
                        'linkedin-in' => '#'
                    ];

                    foreach ($social_links as $platform => $url) {
                        echo '<a href="' . $url . '" class="me-3 text-muted" target="_blank" rel="noopener">';
                        echo '<i class="fab fa-' . $platform . '"></i></a>';
                    }
                    ?>
                </div>
            </div>
            
            <!-- Quick Links -->
            <div class="col-6 col-lg-2">
                <h6 class="footer-title">Quick Links</h6>
                <ul class="footer-links">
                    <?php
                    $quick_links = [
                        'Books' => 'books.php',
                        'Categories' => 'categories.php',
                        'About Us' => 'about.php',
                        'Contact' => 'contact.php'
                    ];

                    foreach ($quick_links as $name => $url) {
                        echo '<li><a href="' . $url . '">' . $name . '</a></li>';
                    }
                    ?>
                </ul>
            </div>
            
            <!-- Categories -->
            <div class="col-6 col-lg-2">
                <h6 class="footer-title">Categories</h6>
                <ul class="footer-links">
                    <?php
                    $categories = [
                        'Fiction' => 'fiction',
                        'Non-Fiction' => 'non-fiction',
                        'Children' => 'children',
                        'Academic' => 'academic'
                    ];

                    foreach ($categories as $name => $category) {
                        echo '<li><a href="books.php?category=' . $category . '">' . $name . '</a></li>';
                    }
                    ?>
                </ul>
            </div>
            
            <!-- Newsletter -->
            <div class="col-lg-4">
                <h6 class="footer-title">Newsletter</h6>
                <p class="text-muted">Subscribe to our newsletter for the latest updates and exclusive offers.</p>
                <form class="mt-3" id="newsletterForm" action="subscribe.php" method="POST">
                    <div class="input-group">
                        <input type="email" class="form-control" name="email" placeholder="Enter your email" required>
                        <button class="btn btn-primary" type="submit">Subscribe</button>
                    </div>
                </form>
            </div>
        </div>
        
        <hr class="my-4">
        
        <!-- Bottom Footer -->
        <div class="row align-items-center">
            <div class="col-md-6 text-center text-md-start">
                <p class="mb-0">&copy; <?php echo date('Y'); ?> BookStore. All rights reserved.</p>
            </div>
            <div class="col-md-6 text-center text-md-end mt-3 mt-md-0">
                <?php
                $legal_links = [
                    'Privacy Policy' => 'privacy.php',
                    'Terms of Service' => 'terms.php',
                    'Cookie Policy' => 'cookies.php'
                ];

                $last_key = array_key_last($legal_links);
                foreach ($legal_links as $name => $url) {
                    echo '<a href="' . $url . '" class="text-muted' . 
                         ($name !== $last_key ? ' me-3' : '') . '">' . $name . '</a>';
                }
                ?>
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