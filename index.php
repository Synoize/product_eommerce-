<?php

/**
 * Home Page
 * eCommerce Website Main Landing Page
 */

$pageTitle = 'Home';
require_once 'includes/header.php';

// Fetch featured products
$featuredProducts = [];
try {
    $stmt = $pdo->query("SELECT p.*, c.name as category_name 
                         FROM products p 
                         LEFT JOIN categories c ON p.category_id = c.id 
                         WHERE p.status = 1 
                         ORDER BY p.created_at DESC 
                         LIMIT 8");
    $featuredProducts = $stmt->fetchAll();
} catch (PDOException $e) {
    // Products table might not exist yet
}

// Fetch categories with images
$categories = [];
try {
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    // Categories table might not exist yet
}

// Fetch trending products (most viewed/ordered)
$trendingProducts = [];
try {
    $stmt = $pdo->query("SELECT p.*, c.name as category_name 
                         FROM products p 
                         LEFT JOIN categories c ON p.category_id = c.id 
                         WHERE p.status = 1 
                         ORDER BY RAND() 
                         LIMIT 4");
    $trendingProducts = $stmt->fetchAll();
} catch (PDOException $e) {
    // Silent fail
}
?>

<!-- Hero Section -->
<section class="bg-accent/5 py-20 md:py-20 overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="grid md:grid-cols-2 gap-10 lg:gap-16 items-center">

            <!-- IMAGE (SHOW FIRST ON MOBILE) -->
            <div class="order-1 md:order-2 relative w-full h-[200px] sm:h-[400px] md:h-[450px] lg:h-[500px] flex items-center justify-center">
                <img src="<?php echo IMAGES_URL; ?>/pkg1.png"
                    class="pkg absolute max-h-full w-auto opacity-0 scale-75">

                <img src="<?php echo IMAGES_URL; ?>/pkg2.png"
                    class="pkg absolute max-h-full w-auto opacity-0 scale-75">

                <img src="<?php echo IMAGES_URL; ?>/pkg1.png"
                    class="pkg absolute max-h-full w-auto opacity-0 scale-75">
            </div>

            <!-- TEXT (SHOW BELOW ON MOBILE) -->
            <div class="order-2 md:order-1 text-center md:text-left">

                <h1 class="text-4xl sm:text-6xl leading-[1.2] mb-6 font-luckiest animate-slide-left">
                    Discover
                    <span class="text-accent" style="-webkit-text-stroke: 1px black;">
                        Delicious Snacks
                    </span>
                    and Indian Authentic
                    <span class="text-accent" style="-webkit-text-stroke: 1px black;">
                        Spices
                    </span>
                </h1>

                <p class="text-base text-gray-600 mb-10 max-w-lg mx-auto md:mx-0 animate-slide-left">
                    Discover the rich flavors of our carefully curated selection of wholesome snacks and aromatic spices, crafted to bring freshness, taste, and authenticity to every bite and every meal you prepare.
                </p>

                <div class="flex flex-wrap justify-center md:justify-start gap-4">

                    <!-- Explore Flavors -->
                    <a href="<?php echo BASE_URL; ?>about-us.php"
                        class="bg-accent-500 text-gray-900 font-semibold 
            py-3 px-8 rounded-full 
            shadow-[3px_3px_0_#000] hover:shadow-[4px_3px_0_#000] 
            transition duration-150 animate-slide-bottom">
                        Explore Flavors
                    </a>

                    <!-- Shop Now -->
                    <a href="<?php echo BASE_URL; ?>shop.php"
                        class="bg-white text-gray-800 font-semibold 
            py-3 px-8 rounded-full border border-gray-200
            shadow-[3px_3px_0_#000] hover:shadow-[4px_3px_0_#000] 
            transition duration-150 animate-slide-bottom">
                        <i class="fas fa-shopping-bag mr-2"></i>
                        Shop Now
                    </a>

                </div>

            </div>

            <script>
                document.addEventListener("DOMContentLoaded", () => {
                    const images = document.querySelectorAll('.pkg');
                    let current = 0;

                    function showNextImage() {
                        images.forEach(img => {
                            img.classList.remove('animate-pop', 'animate-float');
                            img.classList.add('opacity-0', 'scale-75');
                        });

                        const img = images[current];
                        img.classList.remove('opacity-0', 'scale-75');
                        img.classList.add('animate-pop');

                        setTimeout(() => {
                            img.classList.remove('animate-pop');
                            img.classList.add('animate-float');
                        }, 500);

                        current = (current + 1) % images.length;
                    }

                    showNextImage();
                    setInterval(showNextImage, 5000); // better UX than 10s
                });
            </script>

        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Heading -->
        <div class="text-center mb-12 scroll-animate-top">
            <h2 class="text-3xl md:text-5xl font-luckiest text-gray-900 mb-2">
                Shop by
                <span class="text-accent" style="-webkit-text-stroke: 1px black;">
                    Category
                </span>
            </h2>
            <p class="text-gray-500 text-base">
                Explore our wide range of product categories
            </p>
        </div>

        <!-- Categories Container -->
        <div class="flex gap-8 md:gap-20 overflow-x-auto 
                justify-center md:justify-center 
                 [&::-webkit-scrollbar]:hidden 
            scrollbar-hide">

            <?php foreach ($categories as $category): ?>
                <a href="<?php echo BASE_URL; ?>shop.php?category=<?php echo $category['id']; ?>"
                    class="group flex-shrink-0 text-center ">

                    <!-- Image -->
                    <div class="relative transition duration-300 
                      group-hover:scale-105 py-4">

                        <?php if ($category['image']): ?>
                            <img src="<?php echo getImageUrl($category['image'], 'categories'); ?>"
                                alt="<?php echo e($category['name']); ?>"
                                class="w-full h-24 md:h-48 object-contain 
                          mx-auto">
                        <?php else: ?>
                            <div class="w-full h-24 md:h-48 bg-gradient-to-br from-primary-400 to-primary-600 
                          flex items-center justify-center rounded-xl">
                                <span class="text-white font-bold text-center px-2">
                                    <?php echo e($category['name']); ?>
                                </span>
                            </div>
                        <?php endif; ?>

                    </div>

                    <!-- Title -->
                    <h5 class="mt-2 text-black text-center text-xs md:text-lg">
                        <?php echo e($category['name']); ?>
                    </h5>

                </a>
            <?php endforeach; ?>

            <img src="<?php echo IMAGES_URL; ?>/new.png" class="scroll-animate-top hidden md:block mt-8 w-24 h-24 md:w-56 md:h-56 animate-float">

        </div>

    </div>
</section>

<!-- Featured Products Section -->
<section>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-end mb-12">
            <div class="scroll-animate-left">
                <h2 class="text-3xl md:text-5xl font-luckiest text-gray-900 mb-2">Featured <span class="text-accent" style="-webkit-text-stroke: 1px black;">Products</span></h2>
                <p class="text-gray-500 text-base">Handpicked items just for you</p>
            </div>
            <a href="<?php echo BASE_URL; ?>shop.php"
                class="scroll-animate-right hidden md:inline-flex items-center gap-2 
          text-primary-600 hover:text-primary-700 
           transition duration-300 group border px-4 py-2 rounded-full">

                View All

                <i class="fas fa-arrow-right 
            transition-transform duration-300 
            group-hover:translate-x-1"></i>
            </a>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 md:gap-8">
            <?php foreach ($featuredProducts as $product): ?>
                <div class="bg-white rounded-2xl border hover:shadow-lg transition-all duration-300 overflow-hidden group">

                    <!-- IMAGE -->
                    <div class="relative overflow-hidden p-3 md:p-4 bg-gray-50">
                        <?php $imageUrl = getImageUrl($product['image'], 'products'); ?>

                        <a href="<?php echo BASE_URL; ?>product.php?id=<?php echo $product['id']; ?>">
                            <img
                                src="<?php echo $imageUrl; ?>"
                                alt="<?php echo e($product['name']); ?>"
                                class="h-28 sm:h-40 md:h-44 object-contain 
                       group-hover:scale-105 transition duration-300">
                        </a>

                        <!-- BADGES -->
                        <?php if ($product['stock'] <= 10 && $product['stock'] > 0): ?>
                            <span class="absolute top-3 left-3 bg-spice/10 backdrop-blur-sm text-spice text-xs font-semibold px-2 py-1 rounded-full shadow">
                                Only <?php echo $product['stock']; ?> left
                            </span>
                        <?php endif; ?>

                        <?php renderWishlistIconButton($product['id'], 'absolute top-3 right-3 z-10'); ?>
                    </div>

                    <!-- CONTENT -->
                    <div class="p-3 md:p-4 flex flex-col justify-between h-[160px]">

                        <!-- CATEGORY -->
                        <!-- <small class="text-gray-400 text-xs uppercase tracking-wide">
                            <?php echo e($product['category_name']); ?>
                        </small> -->

                        <!-- NAME -->
                        <h3 class="font-semibold text-gray-900 text-xs sm:text-lg leading-tight line-clamp-2 md:line-clamp-1">
                            <?php echo e($product['name']); ?>
                        </h3>

                        <!-- DESCRIPTION -->
                        <!-- <p class="text-gray-500 text-xs sm:text-sm line-clamp-2">
                            <?php echo substr(e($product['description']), 0, 60) . '...'; ?>
                        </p> -->


                        <!-- PRICE -->
                        <div class="w-full flex justify-between items-center">

                            <!-- LEFT: PRICE -->
                            <div class="flex items-baseline flex-wrap md:gap-2">

                                <span class="text-primary-600 font-bold text-base sm:text-lg md:text-xl">
                                    <?php echo formatCurrency($product['price']); ?>
                                </span>

                                <?php if ($product['original_price'] > $product['price']): ?>
                                    <span class="text-gray-400 line-through text-xs sm:text-sm">
                                        <?php echo formatCurrency($product['original_price']); ?>
                                    </span>
                                <?php endif; ?>

                            </div>

                            <!-- RIGHT: DISCOUNT -->
                            <?php if ($product['original_price'] > $product['price']): ?>
                                <span class="bg-green-100 text-green-700 text-[8px] md:text-xs text-center md:font-semibold px-2 py-1 rounded-full">
                                    <?php
                                    $discount = round((($product['original_price'] - $product['price']) / $product['original_price']) * 100);
                                    echo $discount . '% OFF';
                                    ?>
                                </span>
                            <?php endif; ?>

                        </div>

                        <!-- BUTTON -->
                        <?php if ($product['stock'] > 0): ?>
                            <form action="<?php echo BASE_URL; ?>cart.php" method="POST">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="quantity" value="1">

                                <button type="submit"
                                    class="w-full p-2.5 flex items-center justify-center 
                       bg-accent hover:bg-accent-800 text-white text-xs md:text-base font-semibold 
                       rounded-full gap-2 ">
                                    <i class="fas fa-cart-plus text-xs"></i>
                                    Add to Cart
                                </button>
                            </form>
                        <?php elseif ($product['stock'] <= 0): ?>
                            <button disabled
                                class="w-full p-2.5 flex items-center justify-center 
                   bg-red-500 text-white text-xs md:text-base font-semibold rounded-full cursor-not-allowed">
                                Out of Stock
                            </button>
                        <?php endif; ?>


                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="mt-8 text-center md:hidden">
            <a href="<?php echo BASE_URL; ?>shop.php" class="inline-flex items-center text-primary-500 hover:text-primary-600 border px-4 py-2 rounded-full transition">
                View All <i class="fas fa-arrow-right ml-2"></i>
            </a>
        </div>
    </div>
</section>

<!-- Trending Products Section -->
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="scroll-animate-top text-center mb-12">
            <h2 class="text-3xl md:text-5xl font-luckiest text-gray-900 mb-2">Trending <span class="text-accent" style="-webkit-text-stroke: 1px black;">Now</span></h2>
            <p class="text-gray-500 text-base">Most popular items this week</p>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <?php foreach ($trendingProducts as $product): ?>
                <div class="bg-white rounded-2xl border border-gray-100 hover:shadow-xl transition-all duration-300 overflow-hidden group">

                    <!-- IMAGE -->
                    <div class="relative bg-gray-50 flex items-center justify-center p-3 sm:p-4">
                        <?php $imageUrl = getImageUrl($product['image'], 'products'); ?>

                        <a href="<?php echo BASE_URL; ?>product.php?id=<?php echo $product['id']; ?>">
                            <img
                                src="<?php echo $imageUrl; ?>"
                                alt="<?php echo e($product['name']); ?>"
                                class="h-28 sm:h-40 md:h-44 object-contain 
                       group-hover:scale-105 transition duration-300">
                        </a>

                        <!-- LOW STOCK BADGE -->
                        <?php if ($product['stock'] <= 10 && $product['stock'] > 0): ?>
                            <span class="absolute top-2 left-2 bg-spice/10 backdrop-blur-sm text-spice text-xs font-semibold px-2 py-1 rounded-full">
                                Only <?php echo $product['stock']; ?> left
                            </span>
                        <?php endif; ?>

                        <?php renderWishlistIconButton($product['id'], 'absolute top-2 right-2 z-10'); ?>
                    </div>

                    <!-- CONTENT -->
                    <div class="p-3 sm:p-4 flex flex-col gap-2">

                        <!-- NAME -->
                        <h3 class="font-semibold text-gray-900 text-xs sm:text-sm md:text-base leading-tight line-clamp-2">
                            <?php echo e($product['name']); ?>
                        </h3>

                        <!-- PRICE ROW -->
                        <div class="flex justify-between items-center">

                            <!-- PRICE -->
                            <div class="flex items-center gap-1 flex-wrap">
                                <span class="text-primary-600 font-bold text-sm sm:text-base md:text-lg">
                                    <?php echo formatCurrency($product['price']); ?>
                                </span>

                                <?php if ($product['original_price'] > $product['price']): ?>
                                    <span class="text-gray-400 line-through text-[10px] sm:text-xs">
                                        <?php echo formatCurrency($product['original_price']); ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <!-- DISCOUNT -->
                            <?php if ($product['original_price'] > $product['price']): ?>
                                <span class="bg-green-100 text-green-700 text-[9px] sm:text-xs font-semibold px-2 py-0.5 rounded-full">
                                    <?php
                                    $discount = round((($product['original_price'] - $product['price']) / $product['original_price']) * 100);
                                    echo $discount . '% OFF';
                                    ?>
                                </span>
                            <?php endif; ?>

                        </div>

                        <!-- BUTTON -->
                        <?php if ($product['stock'] > 0): ?>
                            <form action="<?php echo BASE_URL; ?>cart.php" method="POST" class="mt-2">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="quantity" value="1">

                                <button type="submit"
                                    class="w-full py-2.5 flex items-center justify-center gap-2 
                           bg-primary-500 hover:bg-primary-600 text-white 
                           text-xs sm:text-base font-semibold 
                           rounded-full">
                                    <i class="fas fa-cart-plus text-xs"></i>
                                    Add to Cart
                                </button>
                            </form>
                        <?php else: ?>
                            <button disabled
                                class="w-full py-2.5 flex items-center justify-center 
                       bg-gray-300 text-white text-xs sm:text-base font-semibold 
                       rounded-full cursor-not-allowed mt-2">
                                Out of Stock
                            </button>
                        <?php endif; ?>

                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-20 bg-gradient-to-r from-pink-100/50 to-purple-100/50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
            <div class="p-6">
                <div class="w-16 h-16 bg-primary-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-shipping-fast text-2xl text-primary-500"></i>
                </div>
                <h5 class="font-bold text-gray-900 mb-2">Free Shipping</h5>
                <p class="text-gray-500 text-sm">On orders over ₹999</p>
            </div>
            <div class="p-6">
                <div class="w-16 h-16 bg-primary-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-shield-alt text-2xl text-primary-500"></i>
                </div>
                <h5 class="font-bold text-gray-900 mb-2">Secure Payment</h5>
                <p class="text-gray-500 text-sm">100% secure checkout</p>
            </div>
            <div class="p-6">
                <div class="w-16 h-16 bg-primary-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-undo text-2xl text-primary-500"></i>
                </div>
                <h5 class="font-bold text-gray-900 mb-2">Easy Returns</h5>
                <p class="text-gray-500 text-sm">30 day return policy</p>
            </div>
            <div class="p-6">
                <div class="w-16 h-16 bg-primary-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-headset text-2xl text-primary-500"></i>
                </div>
                <h5 class="font-bold text-gray-900 mb-2">24/7 Support</h5>
                <p class="text-gray-500 text-sm">Dedicated support</p>
            </div>
        </div>
    </div>
</section>

<!-- Newsletter Section -->
<section class="py-20 bg-gray-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl mx-auto text-center">
            <h2 class="text-3xl font-bold text-white mb-4">Subscribe to Our Newsletter</h2>
            <p class="text-gray-400 mb-8">Get the latest updates on new products and exclusive offers</p>
            <form class="flex flex-col sm:flex-row gap-4" action="<?php echo BASE_URL; ?>subscribe.php" method="POST">
                <input type="email" name="email" placeholder="Enter your email" required
                    class="flex-1 px-6 py-4 rounded-full bg-white/10 border border-white/20 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                <button type="submit" class="bg-primary-500 hover:bg-primary-600 text-white font-semibold py-4 px-8 rounded-full transition flex items-center justify-center">
                    <i class="fas fa-paper-plane mr-2"></i>Subscribe
                </button>
            </form>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
