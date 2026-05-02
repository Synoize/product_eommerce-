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
<section class="bg-white py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid md:grid-cols-2 gap-12 items-center">
            <div>
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-gray-900 mb-6 leading-tight">
                    Discover Amazing <span class="text-primary-500">Products</span> at Great Prices
                </h1>
                <p class="text-lg text-gray-600 mb-8">Shop the latest trends with our curated collection of quality products. Free shipping on orders over ₹999!</p>
                <div class="flex flex-wrap gap-4">
                    <a href="<?php echo BASE_URL; ?>shop.php" class="inline-flex items-center bg-primary-500 hover:bg-primary-600 text-white font-semibold py-4 px-8 rounded-full transition shadow-lg hover:shadow-xl">
                        <i class="fas fa-shopping-bag mr-2"></i>Shop Now
                    </a>
                    <a href="<?php echo BASE_URL; ?>about-us.php" class="inline-flex items-center border-2 border-primary-500 text-primary-500 hover:bg-primary-50 font-semibold py-4 px-8 rounded-full transition">
                        <i class="fas fa-info-circle mr-2"></i>Learn More
                    </a>
                </div>
            </div>
            <!-- <div class="text-center">
                <img src="<?php echo IMAGES_URL; ?>/pkg1.png" 
                     alt="Shopping" class="max-h-[500px] mx-auto ">
            </div> -->

            <div class="relative w-[700px] h-[500px] mx-auto flex items-center justify-center">

                <img src="<?php echo IMAGES_URL; ?>/pkg1.png"
                    class="pkg absolute max-h-[500px] opacity-0 scale-75">

                <img src="<?php echo IMAGES_URL; ?>/pkg2.png"
                    class="pkg absolute max-h-[500px] opacity-0 scale-75">

                <img src="<?php echo IMAGES_URL; ?>/pkg1.png"
                    class="pkg absolute max-h-[500px] opacity-0 scale-75">

            </div>
            <script>
                const images = document.querySelectorAll('.pkg');
                let current = 0;

                function showNextImage() {
                    // hide all
                    images.forEach(img => {
                        img.classList.remove('animate-pop', 'animate-float');
                        img.classList.add('opacity-0', 'scale-75');
                    });

                    // show current
                    const img = images[current];
                    img.classList.remove('opacity-0', 'scale-75');
                    img.classList.add('animate-pop');

                    // after pop → start floating
                    setTimeout(() => {
                        img.classList.remove('animate-pop');
                        img.classList.add('animate-float');
                    }, 500);

                    current = (current + 1) % images.length;
                }

                // start
                showNextImage();

                // change every 10 sec
                setInterval(showNextImage, 10000);
            </script>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Shop by Category</h2>
            <p class="text-gray-500 text-lg">Explore our wide range of product categories</p>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php foreach ($categories as $category): ?>
                <a href="<?php echo BASE_URL; ?>shop.php?category=<?php echo $category['id']; ?>" class="group">
                    <div class="relative rounded-2xl overflow-hidden shadow-lg transition transform group-hover:-translate-y-2 group-hover:shadow-2xl">
                        <?php if ($category['image']): ?>
                            <img src="<?php echo getImageUrl($category['image'], 'categories'); ?>"
                                alt="<?php echo e($category['name']); ?>"
                                class="w-full h-48 object-cover">
                        <?php else: ?>
                            <div class="w-full h-48 bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center">
                                <span class="text-white font-bold text-xl text-center px-4"><?php echo e($category['name']); ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent flex items-end p-4">
                            <h5 class="text-white font-semibold text-lg"><?php echo e($category['name']); ?></h5>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>

            <?php if (empty($categories)): ?>
                <div class="col-span-full text-center py-12">
                    <p class="text-gray-500">No categories available yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Featured Products Section -->
<section class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-end mb-12">
            <div>
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-2">Featured Products</h2>
                <p class="text-gray-500">Handpicked items just for you</p>
            </div>
            <a href="<?php echo BASE_URL; ?>shop.php" class="hidden md:inline-flex items-center text-primary-500 hover:text-primary-600 font-semibold">
                View All <i class="fas fa-arrow-right ml-2"></i>
            </a>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php foreach ($featuredProducts as $product): ?>
                <div class="bg-white rounded-2xl shadow-md hover:shadow-xl transition overflow-hidden group">
                    <div class="relative">
                        <?php $imageUrl = getImageUrl($product['image'], 'products'); ?>
                        <img src="<?php echo $imageUrl; ?>" alt="<?php echo e($product['name']); ?>" class="w-full h-48 object-cover">

                        <?php if ($product['stock'] <= 5 && $product['stock'] > 0): ?>
                            <span class="absolute top-2 left-2 bg-yellow-400 text-yellow-900 text-xs font-bold px-2 py-1 rounded">Low Stock</span>
                        <?php elseif ($product['stock'] == 0): ?>
                            <span class="absolute top-2 left-2 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded">Out of Stock</span>
                        <?php endif; ?>

                        <a href="<?php echo BASE_URL; ?>product.php?id=<?php echo $product['id']; ?>" class="absolute top-2 right-2 bg-white/90 hover:bg-white text-primary-500 w-10 h-10 rounded-full flex items-center justify-center shadow opacity-0 group-hover:opacity-100 transition">
                            <i class="fas fa-eye"></i>
                        </a>
                    </div>
                    <div class="p-4">
                        <small class="text-gray-400 text-xs"><?php echo e($product['category_name']); ?></small>
                        <h5 class="font-semibold text-gray-900 mb-1 truncate"><?php echo e($product['name']); ?></h5>
                        <p class="text-gray-500 text-sm mb-3 line-clamp-2"><?php echo substr(e($product['description']), 0, 60) . '...'; ?></p>
                        <div class="flex justify-between items-center">
                            <span class="text-primary-600 font-bold text-lg"><?php echo formatCurrency($product['price']); ?></span>
                            <?php if ($product['stock'] > 0): ?>
                                <form action="<?php echo BASE_URL; ?>cart.php" method="POST">
                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                    <input type="hidden" name="action" value="add">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="bg-primary-500 hover:bg-primary-600 text-white w-10 h-10 rounded-full flex items-center justify-center transition">
                                        <i class="fas fa-cart-plus"></i>
                                    </button>
                                </form>
                            <?php else: ?>
                                <button class="bg-gray-300 text-white w-10 h-10 rounded-full flex items-center justify-center cursor-not-allowed" disabled>
                                    <i class="fas fa-times"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if (empty($featuredProducts)): ?>
                <div class="col-span-full text-center py-12">
                    <div class="bg-blue-50 border border-blue-200 text-blue-700 px-6 py-4 rounded-xl inline-flex items-center">
                        <i class="fas fa-info-circle mr-2"></i>Products will appear here once added by admin.
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="mt-8 text-center md:hidden">
            <a href="<?php echo BASE_URL; ?>shop.php" class="inline-flex items-center text-primary-500 hover:text-primary-600 font-semibold">
                View All <i class="fas fa-arrow-right ml-2"></i>
            </a>
        </div>
    </div>
</section>

<!-- Trending Products Section -->
<section class="py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Trending Now</h2>
            <p class="text-gray-500">Most popular items this week</p>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <?php foreach ($trendingProducts as $product): ?>
                <div class="bg-white rounded-2xl shadow-md hover:shadow-xl transition overflow-hidden">
                    <?php $imageUrl = getImageUrl($product['image'], 'products'); ?>
                    <img src="<?php echo $imageUrl; ?>" alt="<?php echo e($product['name']); ?>" class="w-full h-48 object-cover">
                    <div class="p-4 text-center">
                        <h5 class="font-semibold text-gray-900 mb-2 truncate"><?php echo e($product['name']); ?></h5>
                        <p class="text-primary-600 font-bold text-lg mb-3"><?php echo formatCurrency($product['price']); ?></p>
                        <a href="<?php echo BASE_URL; ?>product.php?id=<?php echo $product['id']; ?>" class="inline-flex items-center border-2 border-primary-500 text-primary-500 hover:bg-primary-500 hover:text-white font-medium py-2 px-4 rounded-full transition text-sm">
                            View Details
                        </a>
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