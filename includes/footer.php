<?php
/**
 * Footer Template
 * Included on all frontend pages
 */
?>

<!-- Footer -->
<footer class="footer">
    <div class="container">
        <div class="row">
            <!-- About Column -->
            <div class="col-lg-4 col-md-6 mb-4">
                <h5>About WebStore</h5>
                <p>Your one-stop destination for quality products at amazing prices. We bring you the best shopping experience with secure payments and fast delivery.</p>
                <div class="social-links">
                    <a href="#" class="me-3"><i class="fab fa-facebook-f fa-lg"></i></a>
                    <a href="#" class="me-3"><i class="fab fa-twitter fa-lg"></i></a>
                    <a href="#" class="me-3"><i class="fab fa-instagram fa-lg"></i></a>
                    <a href="#" class="me-3"><i class="fab fa-linkedin-in fa-lg"></i></a>
                </div>
            </div>
            
            <!-- Quick Links -->
            <div class="col-lg-2 col-md-6 mb-4">
                <h5>Quick Links</h5>
                <a href="<?php echo BASE_URL; ?>">Home</a>
                <a href="<?php echo BASE_URL; ?>shop.php">Shop</a>
                <a href="<?php echo BASE_URL; ?>about-us.php">About Us</a>
                <a href="<?php echo BASE_URL; ?>contact-us.php">Contact Us</a>
            </div>
            
            <!-- Categories -->
            <div class="col-lg-2 col-md-6 mb-4">
                <h5>Categories</h5>
                <?php 
                try {
                    $stmt = $pdo->query("SELECT name FROM categories ORDER BY name ASC LIMIT 5");
                    $footerCategories = $stmt->fetchAll();
                    foreach ($footerCategories as $cat):
                ?>
                <a href="<?php echo BASE_URL; ?>shop.php?search=<?php echo urlencode($cat['name']); ?>">
                    <?php echo e($cat['name']); ?>
                </a>
                <?php 
                    endforeach;
                } catch (PDOException $e) {
                    // Silent fail
                }
                ?>
            </div>
            
            <!-- Customer Service -->
            <div class="col-lg-2 col-md-6 mb-4">
                <h5>Customer Service</h5>
                <a href="#">FAQs</a>
                <a href="#">Shipping Info</a>
                <a href="#">Returns Policy</a>
                <a href="#">Privacy Policy</a>
                <a href="#">Terms of Service</a>
            </div>
            
            <!-- Contact Info -->
            <div class="col-lg-2 col-md-6 mb-4">
                <h5>Contact Us</h5>
                <p><i class="fas fa-map-marker-alt me-2"></i>123 Street, City, Country</p>
                <p><i class="fas fa-phone me-2"></i>+1 234 567 8900</p>
                <p><i class="fas fa-envelope me-2"></i>support@webstore.com</p>
            </div>
        </div>
        
        <!-- Footer Bottom -->
        <div class="footer-bottom text-center">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> WebStore. All rights reserved.</p>
        </div>
    </div>
</footer>

<!-- Scroll to Top Button -->
<div class="scroll-to-top" id="scrollToTop">
    <i class="fas fa-chevron-up"></i>
</div>

<!-- Scripts -->
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

<!-- Bootstrap 5 Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- MDB UI Kit JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.0/mdb.min.js"></script>

<!-- Custom JavaScript -->
<script>
$(document).ready(function() {
    // Flash message auto-dismiss
    setTimeout(function() {
        $('.flash-message').alert('close');
    }, 5000);
    
    // Scroll to Top Button
    const scrollToTopBtn = document.getElementById('scrollToTop');
    
    window.addEventListener('scroll', function() {
        if (window.pageYOffset > 300) {
            scrollToTopBtn.classList.add('show');
        } else {
            scrollToTopBtn.classList.remove('show');
        }
    });
    
    scrollToTopBtn.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
    
    // Product image zoom on hover
    $('.product-card .card-img-top').on('mouseenter', function() {
        $(this).css('transform', 'scale(1.05)');
    }).on('mouseleave', function() {
        $(this).css('transform', 'scale(1)');
    });
});
</script>

</body>
</html>
