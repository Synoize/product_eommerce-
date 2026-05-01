<?php
/**
 * Product Detail Page
 * Shows product information with gallery, reviews, and add to cart
 */

require_once 'includes/header.php';

// Get product ID
$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($productId <= 0) {
    setFlash('Invalid product ID', 'danger');
    redirect(BASE_URL . 'shop.php');
}

// Fetch product details
try {
    $stmt = $pdo->prepare("SELECT p.*, c.name as category_name 
                          FROM products p 
                          LEFT JOIN categories c ON p.category_id = c.id 
                          WHERE p.id = ? AND p.status = 1");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();
    
    if (!$product) {
        setFlash('Product not found', 'danger');
        redirect(BASE_URL . 'shop.php');
    }
} catch (PDOException $e) {
    setFlash('Error loading product', 'danger');
    redirect(BASE_URL . 'shop.php');
}

// Parse gallery images
$gallery = [];
if (!empty($product['gallery'])) {
    $gallery = json_decode($product['gallery'], true) ?: [];
}

// Add main image to gallery if not already there
$mainImage = $product['image'];
if (!empty($mainImage) && !in_array($mainImage, $gallery)) {
    array_unshift($gallery, $mainImage);
}

// Fetch related products
$relatedProducts = [];
try {
    $stmt = $pdo->prepare("SELECT p.*, c.name as category_name 
                          FROM products p 
                          LEFT JOIN categories c ON p.category_id = c.id 
                          WHERE p.category_id = ? AND p.id != ? AND p.status = 1 
                          ORDER BY RAND() LIMIT 4");
    $stmt->execute([$product['category_id'], $productId]);
    $relatedProducts = $stmt->fetchAll();
} catch (PDOException $e) {
    // Silent fail
}

// Fetch reviews
$reviews = [];
$avgRating = 0;
try {
    $stmt = $pdo->prepare("SELECT r.*, u.name as user_name 
                          FROM reviews r 
                          JOIN users u ON r.user_id = u.id 
                          WHERE r.product_id = ? 
                          ORDER BY r.created_at DESC");
    $stmt->execute([$productId]);
    $reviews = $stmt->fetchAll();
    
    if (count($reviews) > 0) {
        $avgRating = array_sum(array_column($reviews, 'rating')) / count($reviews);
    }
} catch (PDOException $e) {
    // Reviews table might not exist
}

$pageTitle = $product['name'];
?>

<!-- Breadcrumb -->
<section class="bg-primary-light py-3">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>">Home</a></li>
                <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>shop.php">Shop</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?php echo e($product['name']); ?></li>
            </ol>
        </nav>
    </div>
</section>

<!-- Product Detail -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <!-- Product Gallery -->
            <div class="col-lg-6 mb-4">
                <div class="product-gallery">
                    <!-- Main Image -->
                    <div class="main-image">
                        <?php 
                        $mainImageUrl = !empty($gallery) ? getImageUrl($gallery[0], 'products') : getImageUrl($product['image'], 'products');
                        ?>
                        <img src="<?php echo $mainImageUrl; ?>" id="mainProductImage" alt="<?php echo e($product['name']); ?>">
                    </div>
                    
                    <!-- Thumbnail Images -->
                    <?php if (count($gallery) > 1): ?>
                    <div class="thumbnail-images">
                        <?php foreach ($gallery as $index => $image): ?>
                        <img src="<?php echo getImageUrl($image, 'products'); ?>" 
                             alt="<?php echo e($product['name']); ?> - <?php echo $index + 1; ?>"
                             class="<?php echo $index === 0 ? 'active' : ''; ?>"
                             onclick="changeMainImage(this.src)">
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Product Info -->
            <div class="col-lg-6">
                <div class="ps-lg-4">
                    <!-- Category -->
                    <a href="<?php echo BASE_URL; ?>shop.php?category=<?php echo $product['category_id']; ?>" 
                       class="text-muted text-decoration-none">
                        <?php echo e($product['category_name'] ?? 'Uncategorized'); ?>
                    </a>
                    
                    <!-- Title -->
                    <h1 class="fw-bold mt-2 mb-3"><?php echo e($product['name']); ?></h1>
                    
                    <!-- Rating -->
                    <div class="d-flex align-items-center mb-3">
                        <div class="star-rating me-2">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star <?php echo $i <= round($avgRating) ? '' : 'empty'; ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <span class="text-muted">(<?php echo count($reviews); ?> reviews)</span>
                    </div>
                    
                    <!-- Price -->
                    <div class="mb-4">
                        <span class="price fs-2 fw-bold"><?php echo formatCurrency($product['price']); ?></span>
                        <?php if ($product['original_price'] > $product['price']): ?>
                        <span class="original-price fs-4 text-muted"><?php echo formatCurrency($product['original_price']); ?></span>
                        <span class="badge bg-success ms-2">
                            <?php echo round((($product['original_price'] - $product['price']) / $product['original_price']) * 100); ?>% OFF
                        </span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Description -->
                    <p class="text-muted mb-4"><?php echo nl2br(e($product['description'])); ?></p>
                    
                    <!-- Stock Status -->
                    <div class="mb-4">
                        <?php if ($product['stock'] > 10): ?>
                            <span class="badge bg-success"><i class="fas fa-check me-1"></i>In Stock</span>
                        <?php elseif ($product['stock'] > 0): ?>
                            <span class="badge bg-warning"><i class="fas fa-exclamation me-1"></i>Only <?php echo $product['stock']; ?> left</span>
                        <?php else: ?>
                            <span class="badge bg-danger"><i class="fas fa-times me-1"></i>Out of Stock</span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Add to Cart Form -->
                    <?php if ($product['stock'] > 0): ?>
                    <form action="<?php echo BASE_URL; ?>cart.php" method="POST" class="mb-4">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="row g-3 align-items-center">
                            <div class="col-auto">
                                <label class="fw-bold">Quantity:</label>
                            </div>
                            <div class="col-auto">
                                <div class="quantity-control">
                                    <button type="button" onclick="decreaseQty()">-</button>
                                    <input type="number" name="quantity" id="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>">
                                    <button type="button" onclick="increaseQty(<?php echo $product['stock']; ?>)">+</button>
                                </div>
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-cart-plus me-2"></i>Add to Cart
                                </button>
                            </div>
                        </div>
                    </form>
                    <?php endif; ?>
                    
                    <!-- Additional Info -->
                    <div class="border-top pt-4">
                        <div class="row text-center">
                            <div class="col-4">
                                <i class="fas fa-truck fa-2x text-primary mb-2"></i>
                                <p class="small mb-0">Free Shipping</p>
                            </div>
                            <div class="col-4">
                                <i class="fas fa-undo fa-2x text-primary mb-2"></i>
                                <p class="small mb-0">Easy Returns</p>
                            </div>
                            <div class="col-4">
                                <i class="fas fa-shield-alt fa-2x text-primary mb-2"></i>
                                <p class="small mb-0">Secure Payment</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Reviews Section -->
        <div class="row mt-5">
            <div class="col-12">
                <h3 class="fw-bold mb-4">Customer Reviews</h3>
                
                <?php if (count($reviews) > 0): ?>
                <div class="row">
                    <div class="col-lg-4 mb-4">
                        <div class="card">
                            <div class="card-body text-center">
                                <h2 class="fw-bold mb-0"><?php echo number_format($avgRating, 1); ?></h2>
                                <div class="star-rating my-2">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star <?php echo $i <= round($avgRating) ? '' : 'empty'; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <p class="text-muted mb-0">Based on <?php echo count($reviews); ?> reviews</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-8">
                        <?php foreach ($reviews as $review): ?>
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="fw-bold mb-1"><?php echo e($review['user_name']); ?></h6>
                                        <div class="star-rating mb-2">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fas fa-star <?php echo $i <= $review['rating'] ? '' : 'empty'; ?>"></i>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    <small class="text-muted"><?php echo date('M d, Y', strtotime($review['created_at'])); ?></small>
                                </div>
                                <p class="mb-0"><?php echo e($review['comment']); ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-comment-slash fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No reviews yet. Be the first to review!</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Related Products -->
        <?php if (!empty($relatedProducts)): ?>
        <div class="row mt-5">
            <div class="col-12">
                <h3 class="fw-bold mb-4">Related Products</h3>
                <div class="row g-4">
                    <?php foreach ($relatedProducts as $related): ?>
                    <div class="col-6 col-md-3">
                        <div class="card product-card h-100">
                            <?php 
                            $relImageUrl = getImageUrl($related['image'], 'products');
                            ?>
                            <img src="<?php echo $relImageUrl; ?>" class="card-img-top" alt="<?php echo e($related['name']); ?>">
                            <div class="card-body text-center">
                                <h5 class="card-title"><?php echo e($related['name']); ?></h5>
                                <p class="price mb-2"><?php echo formatCurrency($related['price']); ?></p>
                                <a href="<?php echo BASE_URL; ?>product.php?id=<?php echo $related['id']; ?>" class="btn btn-outline-primary btn-sm">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<script>
function changeMainImage(src) {
    document.getElementById('mainProductImage').src = src;
    
    // Update active state on thumbnails
    document.querySelectorAll('.thumbnail-images img').forEach(img => {
        img.classList.remove('active');
        if (img.src === src) {
            img.classList.add('active');
        }
    });
}

function increaseQty(max) {
    const qtyInput = document.getElementById('quantity');
    let val = parseInt(qtyInput.value);
    if (val < max) {
        qtyInput.value = val + 1;
    }
}

function decreaseQty() {
    const qtyInput = document.getElementById('quantity');
    let val = parseInt(qtyInput.value);
    if (val > 1) {
        qtyInput.value = val - 1;
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>
