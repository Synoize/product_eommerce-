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
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="hero-title">Discover Amazing Products at Great Prices</h1>
                <p class="hero-subtitle">Shop the latest trends with our curated collection of quality products. Free shipping on orders over ₹999!</p>
                <div class="d-flex gap-3">
                    <a href="<?php echo BASE_URL; ?>shop.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-shopping-bag me-2"></i>Shop Now
                    </a>
                    <a href="<?php echo BASE_URL; ?>about-us.php" class="btn btn-outline-primary btn-lg">
                        <i class="fas fa-info-circle me-2"></i>Learn More
                    </a>
                </div>
            </div>
            <div class="col-lg-6 text-center">
                <img src="https://img.freepik.com/free-vector/ecommerce-web-page-concept-illustration_114360-820.jpg" 
                     alt="Shopping" class="img-fluid rounded-4 shadow-lg" style="max-height: 400px;">
            </div>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Shop by Category</h2>
            <p class="text-muted">Explore our wide range of product categories</p>
        </div>
        
        <div class="row g-4">
            <?php foreach ($categories as $category): ?>
            <div class="col-6 col-md-4 col-lg-3">
                <a href="<?php echo BASE_URL; ?>shop.php?category=<?php echo $category['id']; ?>" class="text-decoration-none">
                    <div class="category-card">
                        <?php if ($category['image']): ?>
                            <img src="<?php echo getImageUrl($category['image'], 'categories'); ?>" 
                                 alt="<?php echo e($category['name']); ?>">
                        <?php else: ?>
                            <img src="https://via.placeholder.com/300x200/f84183/ffffff?text=<?php echo urlencode($category['name']); ?>" 
                                 alt="<?php echo e($category['name']); ?>">
                        <?php endif; ?>
                        <div class="overlay">
                            <h5><?php echo e($category['name']); ?></h5>
                        </div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
            
            <?php if (empty($categories)): ?>
            <div class="col-12 text-center">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>Categories will appear here once added by admin.
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Featured Products Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h2 class="fw-bold mb-0">Featured Products</h2>
                <p class="text-muted mb-0">Handpicked items just for you</p>
            </div>
            <a href="<?php echo BASE_URL; ?>shop.php" class="btn btn-outline-primary">
                View All <i class="fas fa-arrow-right ms-2"></i>
            </a>
        </div>
        
        <div class="row g-4">
            <?php foreach ($featuredProducts as $product): ?>
            <div class="col-6 col-md-4 col-lg-3">
                <div class="card product-card h-100">
                    <div class="position-relative">
                        <?php 
                        $imageUrl = getImageUrl($product['image'], 'products');
                        ?>
                        <img src="<?php echo $imageUrl; ?>" class="card-img-top" alt="<?php echo e($product['name']); ?>">
                        <?php if ($product['stock'] <= 5 && $product['stock'] > 0): ?>
                            <span class="badge bg-warning position-absolute top-0 start-0 m-2">Low Stock</span>
                        <?php elseif ($product['stock'] == 0): ?>
                            <span class="badge bg-danger position-absolute top-0 start-0 m-2">Out of Stock</span>
                        <?php endif; ?>
                        
                        <div class="product-actions position-absolute top-0 end-0 m-2 d-flex flex-column gap-2">
                            <a href="<?php echo BASE_URL; ?>product.php?id=<?php echo $product['id']; ?>" class="btn btn-light btn-sm rounded-circle" title="View Details">
                                <i class="fas fa-eye text-primary"></i>
                            </a>
                        </div>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <small class="text-muted"><?php echo e($product['category_name']); ?></small>
                        <h5 class="card-title"><?php echo e($product['name']); ?></h5>
                        <p class="card-text flex-grow-1"><?php echo substr(e($product['description']), 0, 60) . '...'; ?></p>
                        <div class="d-flex justify-content-between align-items-center mt-auto">
                            <span class="price"><?php echo formatCurrency($product['price']); ?></span>
                            <?php if ($product['stock'] > 0): ?>
                            <form action="<?php echo BASE_URL; ?>cart.php" method="POST" class="d-inline">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="fas fa-cart-plus"></i>
                                </button>
                            </form>
                            <?php else: ?>
                            <button class="btn btn-secondary btn-sm" disabled>
                                <i class="fas fa-times"></i>
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            
            <?php if (empty($featuredProducts)): ?>
            <div class="col-12 text-center">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>Products will appear here once added by admin.
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Trending Products Section -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Trending Now</h2>
            <p class="text-muted">Most popular items this week</p>
        </div>
        
        <div class="row g-4">
            <?php foreach ($trendingProducts as $product): ?>
            <div class="col-6 col-md-3">
                <div class="card product-card h-100">
                    <?php 
                    $imageUrl = getImageUrl($product['image'], 'products');
                    ?>
                    <img src="<?php echo $imageUrl; ?>" class="card-img-top" alt="<?php echo e($product['name']); ?>">
                    <div class="card-body text-center">
                        <h5 class="card-title"><?php echo e($product['name']); ?></h5>
                        <p class="price mb-2"><?php echo formatCurrency($product['price']); ?></p>
                        <a href="<?php echo BASE_URL; ?>product.php?id=<?php echo $product['id']; ?>" class="btn btn-outline-primary btn-sm">
                            View Details
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-5 bg-primary-light">
    <div class="container">
        <div class="row g-4 text-center">
            <div class="col-6 col-md-3">
                <div class="p-4">
                    <i class="fas fa-shipping-fast fa-3x text-primary mb-3"></i>
                    <h5>Free Shipping</h5>
                    <p class="text-muted mb-0">On orders over ₹999</p>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="p-4">
                    <i class="fas fa-shield-alt fa-3x text-primary mb-3"></i>
                    <h5>Secure Payment</h5>
                    <p class="text-muted mb-0">100% secure checkout</p>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="p-4">
                    <i class="fas fa-undo fa-3x text-primary mb-3"></i>
                    <h5>Easy Returns</h5>
                    <p class="text-muted mb-0">30 day return policy</p>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="p-4">
                    <i class="fas fa-headset fa-3x text-primary mb-3"></i>
                    <h5>24/7 Support</h5>
                    <p class="text-muted mb-0">Dedicated support</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Newsletter Section -->
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 text-center">
                <h2 class="fw-bold mb-3">Subscribe to Our Newsletter</h2>
                <p class="text-muted mb-4">Get the latest updates on new products and exclusive offers</p>
                <form class="d-flex gap-2" action="<?php echo BASE_URL; ?>subscribe.php" method="POST">
                    <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane me-2"></i>Subscribe
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
