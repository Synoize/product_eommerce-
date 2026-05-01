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
<section class="bg-gradient-to-r from-pink-50 to-purple-50 py-4">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <nav class="text-sm text-gray-600">
            <ol class="flex items-center space-x-2">
                <li><a href="<?php echo BASE_URL; ?>" class="hover:text-primary-500">Home</a></li>
                <li><i class="fas fa-chevron-right text-xs"></i></li>
                <li><a href="<?php echo BASE_URL; ?>shop.php" class="hover:text-primary-500">Shop</a></li>
                <li><i class="fas fa-chevron-right text-xs"></i></li>
                <li class="text-primary-500 font-medium truncate max-w-xs"><?php echo e($product['name']); ?></li>
            </ol>
        </nav>
    </div>
</section>

<!-- Product Detail -->
<section class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            <!-- Product Gallery -->
            <div>
                <div class="space-y-4">
                    <!-- Main Image -->
                    <div class="rounded-2xl overflow-hidden shadow-lg bg-gray-50">
                        <?php 
                        $mainImageUrl = !empty($gallery) ? getImageUrl($gallery[0], 'products') : getImageUrl($product['image'], 'products');
                        ?>
                        <img src="<?php echo $mainImageUrl; ?>" id="mainProductImage" alt="<?php echo e($product['name']); ?>" class="w-full h-96 object-cover">
                    </div>
                    
                    <!-- Thumbnail Images -->
                    <?php if (count($gallery) > 1): ?>
                    <div class="grid grid-cols-4 gap-3">
                        <?php foreach ($gallery as $index => $image): ?>
                        <img src="<?php echo getImageUrl($image, 'products'); ?>" 
                             alt="<?php echo e($product['name']); ?> - <?php echo $index + 1; ?>"
                             class="w-full h-24 object-cover rounded-xl cursor-pointer transition hover:opacity-75 <?php echo $index === 0 ? 'ring-2 ring-primary-500' : ''; ?>"
                             onclick="changeMainImage(this.src)">
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Product Info -->
            <div>
                <div class="lg:pl-8">
                    <!-- Category -->
                    <a href="<?php echo BASE_URL; ?>shop.php?category=<?php echo $product['category_id']; ?>" 
                       class="text-gray-500 hover:text-primary-500 text-sm font-medium">
                        <?php echo e($product['category_name'] ?? 'Uncategorized'); ?>
                    </a>
                    
                    <!-- Title -->
                    <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mt-2 mb-4"><?php echo e($product['name']); ?></h1>
                    
                    <!-- Rating -->
                    <div class="flex items-center mb-4">
                        <div class="flex items-center mr-3">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star <?php echo $i <= round($avgRating) ? 'text-yellow-400' : 'text-gray-300'; ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <span class="text-gray-500">(<?php echo count($reviews); ?> reviews)</span>
                    </div>
                    
                    <!-- Price -->
                    <div class="mb-6">
                        <span class="text-4xl font-bold text-primary-500"><?php echo formatCurrency($product['price']); ?></span>
                        <?php if ($product['original_price'] > $product['price']): ?>
                        <span class="text-xl text-gray-400 line-through ml-3"><?php echo formatCurrency($product['original_price']); ?></span>
                        <span class="inline-block bg-green-500 text-white text-xs font-bold px-2 py-1 rounded ml-2">
                            <?php echo round((($product['original_price'] - $product['price']) / $product['original_price']) * 100); ?>% OFF
                        </span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Description -->
                    <p class="text-gray-600 mb-6 leading-relaxed"><?php echo nl2br(e($product['description'])); ?></p>
                    
                    <!-- Stock Status -->
                    <div class="mb-6">
                        <?php if ($product['stock'] > 10): ?>
                            <span class="inline-flex items-center bg-green-100 text-green-700 px-3 py-1 rounded-full text-sm font-medium"><i class="fas fa-check mr-1"></i>In Stock</span>
                        <?php elseif ($product['stock'] > 0): ?>
                            <span class="inline-flex items-center bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-sm font-medium"><i class="fas fa-exclamation mr-1"></i>Only <?php echo $product['stock']; ?> left</span>
                        <?php else: ?>
                            <span class="inline-flex items-center bg-red-100 text-red-700 px-3 py-1 rounded-full text-sm font-medium"><i class="fas fa-times mr-1"></i>Out of Stock</span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Add to Cart Form -->
                    <?php if ($product['stock'] > 0): ?>
                    <form action="<?php echo BASE_URL; ?>cart.php" method="POST" class="mb-6">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="flex flex-wrap items-center gap-4">
                            <label class="font-semibold text-gray-700">Quantity:</label>
                            <div class="flex items-center border-2 border-gray-300 rounded-full overflow-hidden">
                                <button type="button" onclick="decreaseQty()" class="w-10 h-10 flex items-center justify-center hover:bg-gray-100 transition text-lg font-medium">-</button>
                                <input type="number" name="quantity" id="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>" class="w-14 text-center text-lg font-semibold border-x-2 border-gray-300">
                                <button type="button" onclick="increaseQty(<?php echo $product['stock']; ?>)" class="w-10 h-10 flex items-center justify-center hover:bg-gray-100 transition text-lg font-medium">+</button>
                            </div>
                            <button type="submit" class="bg-primary-500 hover:bg-primary-600 text-white font-semibold py-3 px-8 rounded-full transition shadow-lg hover:shadow-xl inline-flex items-center">
                                <i class="fas fa-cart-plus mr-2"></i>Add to Cart
                            </button>
                        </div>
                    </form>
                    <?php endif; ?>
                    
                    <!-- Additional Info -->
                    <div class="border-t border-gray-200 pt-6">
                        <div class="grid grid-cols-3 gap-4 text-center">
                            <div>
                                <i class="fas fa-truck text-3xl text-primary-500 mb-2"></i>
                                <p class="text-sm text-gray-600">Free Shipping</p>
                            </div>
                            <div>
                                <i class="fas fa-undo text-3xl text-primary-500 mb-2"></i>
                                <p class="text-sm text-gray-600">Easy Returns</p>
                            </div>
                            <div>
                                <i class="fas fa-shield-alt text-3xl text-primary-500 mb-2"></i>
                                <p class="text-sm text-gray-600">Secure Payment</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Reviews Section -->
        <div class="mt-16">
            <h3 class="text-2xl font-bold text-gray-900 mb-8">Customer Reviews</h3>
            
            <?php if (count($reviews) > 0): ?>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div>
                    <div class="bg-white rounded-2xl shadow-md p-6 text-center">
                        <h2 class="text-5xl font-bold text-gray-900 mb-2"><?php echo number_format($avgRating, 1); ?></h2>
                        <div class="flex justify-center items-center my-3">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star <?php echo $i <= round($avgRating) ? 'text-yellow-400' : 'text-gray-300'; ?>"></i>
                            <?php endfor; ?>
                        </div>
                        <p class="text-gray-500">Based on <?php echo count($reviews); ?> reviews</p>
                    </div>
                </div>
                <div class="lg:col-span-2 space-y-4">
                    <?php foreach ($reviews as $review): ?>
                    <div class="bg-white rounded-2xl shadow-sm p-6">
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <h6 class="font-bold text-gray-900"><?php echo e($review['user_name']); ?></h6>
                                <div class="flex items-center mt-1">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star <?php echo $i <= $review['rating'] ? 'text-yellow-400' : 'text-gray-300'; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <small class="text-gray-400"><?php echo date('M d, Y', strtotime($review['created_at'])); ?></small>
                        </div>
                        <p class="text-gray-600"><?php echo e($review['comment']); ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php else: ?>
            <div class="text-center py-12">
                <i class="fas fa-comment-slash text-5xl text-gray-300 mb-4"></i>
                <p class="text-gray-500">No reviews yet. Be the first to review!</p>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Related Products -->
        <?php if (!empty($relatedProducts)): ?>
        <div class="mt-16">
            <h3 class="text-2xl font-bold text-gray-900 mb-8">Related Products</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <?php foreach ($relatedProducts as $related): ?>
                <div class="bg-white rounded-2xl shadow-md overflow-hidden group">
                    <?php $relImageUrl = getImageUrl($related['image'], 'products'); ?>
                    <img src="<?php echo $relImageUrl; ?>" alt="<?php echo e($related['name']); ?>" class="w-full h-48 object-cover">
                    <div class="p-4 text-center">
                        <h5 class="font-semibold text-gray-900 mb-2 truncate"><?php echo e($related['name']); ?></h5>
                        <p class="text-primary-600 font-bold text-lg mb-3"><?php echo formatCurrency($related['price']); ?></p>
                        <a href="<?php echo BASE_URL; ?>product.php?id=<?php echo $related['id']; ?>" class="inline-flex items-center border-2 border-primary-500 text-primary-500 hover:bg-primary-500 hover:text-white font-medium py-2 px-4 rounded-full transition text-sm">
                            View Details
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<script>
function changeMainImage(src) {
    document.getElementById('mainProductImage').src = src;
    
    // Update active state on thumbnails
    document.querySelectorAll('.grid-cols-4 img').forEach(img => {
        img.classList.remove('ring-2', 'ring-primary-500');
        if (img.src === src) {
            img.classList.add('ring-2', 'ring-primary-500');
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
