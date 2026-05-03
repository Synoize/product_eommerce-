<?php
/**
 * Footer Template
 * Included on all frontend pages
 */
?>

<!-- Footer -->
<footer class="bg-gray-900 text-white pt-12 pb-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-8 mb-8">
            <!-- About Column -->
            <div class="lg:col-span-2">
                <h5 class="text-lg font-bold mb-4 text-primary-400">About WebStore</h5>
                <p class="text-gray-400 text-sm mb-4">Your one-stop destination for quality products at amazing prices. We bring you the best shopping experience with secure payments and fast delivery.</p>
                <div class="flex space-x-4">
                    <a href="#" class="text-gray-400 hover:text-primary-400 transition"><i class="fab fa-facebook-f text-lg"></i></a>
                    <a href="#" class="text-gray-400 hover:text-primary-400 transition"><i class="fab fa-twitter text-lg"></i></a>
                    <a href="#" class="text-gray-400 hover:text-primary-400 transition"><i class="fab fa-instagram text-lg"></i></a>
                    <a href="#" class="text-gray-400 hover:text-primary-400 transition"><i class="fab fa-linkedin-in text-lg"></i></a>
                </div>
            </div>
            
            <!-- Quick Links -->
            <div>
                <h5 class="text-lg font-bold mb-4 text-primary-400">Quick Links</h5>
                <ul class="space-y-2">
                    <li><a href="<?php echo BASE_URL; ?>" class="text-gray-400 hover:text-white text-sm transition">Home</a></li>
                    <li><a href="<?php echo BASE_URL; ?>shop.php" class="text-gray-400 hover:text-white text-sm transition">Shop</a></li>
                    <li><a href="<?php echo BASE_URL; ?>about-us.php" class="text-gray-400 hover:text-white text-sm transition">About Us</a></li>
                    <li><a href="<?php echo BASE_URL; ?>contact-us.php" class="text-gray-400 hover:text-white text-sm transition">Contact Us</a></li>
                </ul>
            </div>
            
            <!-- Categories -->
            <div>
                <h5 class="text-lg font-bold mb-4 text-primary-400">Categories</h5>
                <ul class="space-y-2">
                    <?php 
                    try {
                        $stmt = $pdo->query("SELECT name FROM categories ORDER BY name ASC LIMIT 5");
                        $footerCategories = $stmt->fetchAll();
                        foreach ($footerCategories as $cat):
                    ?>
                    <li>
                        <a href="<?php echo BASE_URL; ?>shop.php?search=<?php echo urlencode($cat['name']); ?>" class="text-gray-400 hover:text-white text-sm transition">
                            <?php echo e($cat['name']); ?>
                        </a>
                    </li>
                    <?php 
                        endforeach;
                    } catch (PDOException $e) {
                        // Silent fail
                    }
                    ?>
                </ul>
            </div>
            
            <!-- Contact Info -->
            <div>
                <h5 class="text-lg font-bold mb-4 text-primary-400">Contact Us</h5>
                <ul class="space-y-2 text-gray-400 text-sm">
                    <li class="flex items-start"><i class="fas fa-map-marker-alt mt-1 mr-2 text-primary-400"></i>123 Street, City, Country</li>
                    <li class="flex items-center"><i class="fas fa-phone mr-2 text-primary-400"></i>+1 234 567 8900</li>
                    <li class="flex items-center"><i class="fas fa-envelope mr-2 text-primary-400"></i>support@webstore.com</li>
                </ul>
            </div>
        </div>
        
        <!-- Footer Bottom -->
        <div class="border-t border-gray-800 pt-6 text-center">
            <p class="text-gray-400 text-sm">&copy; <?php echo date('Y'); ?> WebStore. All rights reserved.</p>
        </div>
    </div>
</footer>

<!-- Scroll to Top Button -->
<button id="scrollToTop" class="fixed bottom-6 right-6 bg-primary-500 hover:bg-primary-600 text-white w-12 h-12 rounded-full shadow-lg flex items-center justify-center opacity-0 invisible transition-all duration-300 z-50">
    <i class="fas fa-chevron-up"></i>
</button>

<!-- Scripts -->
<script>
// Scroll to Top Button
const scrollToTopBtn = document.getElementById('scrollToTop');

window.addEventListener('scroll', function() {
    if (window.pageYOffset > 300) {
        scrollToTopBtn.classList.remove('opacity-0', 'invisible');
        scrollToTopBtn.classList.add('opacity-100', 'visible');
    } else {
        scrollToTopBtn.classList.add('opacity-0', 'invisible');
        scrollToTopBtn.classList.remove('opacity-100', 'visible');
    }
});

scrollToTopBtn.addEventListener('click', function() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
});
</script>

<script src="<?php echo ASSETS_URL; ?>/public/js/script.js"></script>
</body>
</html>
