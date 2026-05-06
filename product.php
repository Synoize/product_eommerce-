<?php
/**
 * Product Detail Page
 * Shows product information with gallery, reviews, and add to cart
 */

$pageTitle = 'Products';
require_once 'includes/header.php';

// Get product ID
$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($productId <= 0) {
    setFlash('Invalid product ID', 'danger');
    redirect(BASE_URL . 'shop.php');
}

// Handle wishlist action via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['wishlist_action'])) {
    if (!isLoggedIn()) {
        setFlash('Please login to manage wishlist', 'warning');
        redirect(BASE_URL . 'user/login.php');
    }
    
    $action = $_POST['wishlist_action'];
    
    if ($action === 'add') {
        if (addToWishlist($productId)) {
            setFlash('Added to wishlist!', 'success');
        } else {
            setFlash('Error adding to wishlist', 'danger');
        }
    } elseif ($action === 'remove') {
        if (removeFromWishlist($productId)) {
            setFlash('Removed from wishlist', 'success');
        } else {
            setFlash('Error removing from wishlist', 'danger');
        }
    }
    
    redirect(BASE_URL . 'product.php?id=' . $productId);
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

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_action'])) {
    if (!isLoggedIn()) {
        $_SESSION['redirect_after_login'] = BASE_URL . 'product.php?id=' . $productId . '#reviews';
        setFlash('Please login to write a review.', 'warning');
        redirect(BASE_URL . 'user/login.php');
    }

    $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;
    $comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';
    $reviewErrors = [];

    if ($rating < 1 || $rating > 5) {
        $reviewErrors[] = 'Please select a rating from 1 to 5 stars.';
    }

    if ($comment === '') {
        $reviewErrors[] = 'Please write your review.';
    } elseif (strlen($comment) > 1000) {
        $reviewErrors[] = 'Review must be 1000 characters or less.';
    }

    if (empty($reviewErrors)) {
        try {
            $stmt = $pdo->prepare("SELECT id FROM reviews WHERE product_id = ? AND user_id = ? ORDER BY created_at DESC LIMIT 1");
            $stmt->execute([$productId, $_SESSION['user_id']]);
            $existingReview = $stmt->fetch();

            if ($existingReview) {
                $stmt = $pdo->prepare("UPDATE reviews SET rating = ?, comment = ?, created_at = NOW() WHERE id = ?");
                $stmt->execute([$rating, $comment, $existingReview['id']]);
                setFlash('Your review has been updated.', 'success');
            } else {
                $stmt = $pdo->prepare("INSERT INTO reviews (product_id, user_id, rating, comment, created_at) VALUES (?, ?, ?, ?, NOW())");
                $stmt->execute([$productId, $_SESSION['user_id'], $rating, $comment]);
                setFlash('Thank you for your review!', 'success');
            }
        } catch (PDOException $e) {
            setFlash('Unable to save your review right now.', 'danger');
        }
    } else {
        setFlash(implode(' ', $reviewErrors), 'danger');
    }

    redirect(BASE_URL . 'product.php?id=' . $productId . '#reviews');
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

// Fetch product weights
$productWeights = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM product_weights WHERE product_id = ? ORDER BY sort_order ASC");
    $stmt->execute([$productId]);
    $productWeights = $stmt->fetchAll();
} catch (PDOException $e) {
    // Silent fail
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
$userReview = null;
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

    if (isLoggedIn()) {
        foreach ($reviews as $review) {
            if ((int)$review['user_id'] === (int)$_SESSION['user_id']) {
                $userReview = $review;
                break;
            }
        }
    }
} catch (PDOException $e) {
    // Reviews table might not exist
}

// Check if product is in wishlist
$inWishlist = isInWishlist($productId);

$pageTitle = $product['name'];

$productDetailSections = [
    'Description' => trim((string)($product['description'] ?? '')),
    'Ingredients' => trim((string)($product['ingredients'] ?? '')),
    'Shipping & Return' => trim((string)($product['shipping_return'] ?? '')),
    'Legal Mandatories' => trim((string)($product['legal_mandatories'] ?? '')),
];

$productName = trim((string)($product['name'] ?? 'this product'));
$productCategory = trim((string)($product['category_name'] ?? 'Food Product'));
$categoryKey = strtolower($productCategory);
$defaultIngredients = strpos($categoryKey, 'spice') !== false || strpos($categoryKey, 'masala') !== false
    ? $productName . '. Packed fresh to preserve natural aroma and flavor. Please check the product pack for the complete ingredient list.'
    : $productName . ', seasoning, spices, and permitted ingredients as applicable. Please check the product pack for complete ingredients and allergen information.';

$productDetailFallbacks = [
    'Ingredients' => $defaultIngredients,
    'Shipping & Return' => 'Orders are usually shipped within 2-4 business days. Returns are accepted for eligible unopened products as per store policy.',
    'Legal Mandatories' => "Product: {$productName}\nCategory: {$productCategory}\nCountry of Origin: India\nPlease refer to the product packaging for manufacturer details, batch number, expiry date, MRP, net quantity, and other statutory information.",
];
?>

<!-- Breadcrumb -->
<section class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <nav class="text-sm text-gray-600">
            <ol class="flex items-center space-x-2">
                <li><a href="<?php echo BASE_URL; ?>" class="hover:text-accent">Home</a></li>
                <li><i class="fas fa-chevron-right text-xs"></i></li>
                <li><a href="<?php echo BASE_URL; ?>shop.php" class="hover:text-accent">Shop</a></li>
                <li><i class="fas fa-chevron-right text-xs"></i></li>
                <li class="text-accent font-medium truncate max-w-xs"><?php echo e($product['name']); ?></li>
            </ol>
        </nav>
    </div>
</section>

<!-- Product Detail -->
<section>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Product Gallery -->
            <div>
                <div class="space-y-4">
                    <!-- Main Image -->
                    <div class="rounded-2xl border overflow-hidden shadow-sm bg-gray-50">
                        <?php 
                        $mainImageUrl = !empty($gallery) ? getImageUrl($gallery[0], 'products') : getImageUrl($product['image'], 'products');
                        ?>
                        <img src="<?php echo $mainImageUrl; ?>" id="mainProductImage" alt="<?php echo e($product['name']); ?>" class="w-full max-h-96 min-h-92 object-contain">
                    </div>
                    
                    <!-- Thumbnail Images -->
                    <?php if (count($gallery) > 1): ?>
                    <div class="grid grid-cols-4 gap-3">
                        <?php foreach ($gallery as $index => $image): ?>
                        <img src="<?php echo getImageUrl($image, 'products'); ?>" 
                             alt="<?php echo e($product['name']); ?> - <?php echo $index + 1; ?>"
                             class="w-full max-h-24 min-h-18 object-cover rounded-xl border cursor-pointer transition hover:opacity-75 <?php echo $index === 0 ? 'ring-2 ring-accent' : ''; ?>"
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
                       class="text-gray-500 hover:text-accent text-sm font-medium">
                        <?php echo e($product['category_name'] ?? 'Uncategorized'); ?>
                    </a>
                    
                    <!-- Title -->
                    <h1 class="text-2xl md:text-4xl font-bold text-gray-900 mt-2 mb-4 line-clamp-2"><?php echo e($product['name']); ?></h1>
                    
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
                    <div class="mb-6 flex items-center">
                        <span class="text-4xl font-bold text-primary-500"><?php echo formatCurrency($product['price']); ?></span>
                        <?php if ($product['original_price'] > $product['price']): ?>
                        <span class="text-xl text-gray-400 line-through ml-3"><?php echo formatCurrency($product['original_price']); ?></span>
                        <span class="inline-block bg-green-500 text-white text-xs font-bold px-2 py-1 rounded ml-5">
                            <?php echo round((($product['original_price'] - $product['price']) / $product['original_price']) * 100); ?>% OFF
                        </span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Description -->
                    <p class="text-gray-600 mb-6 text-sm leading-relaxed"><?php echo nl2br(e($product['description'])); ?></p>
                    
                    <!-- Stock Status -->
                    <div class="mb-6">
                        <?php if ($product['stock'] > 10): ?>
                            <span></span>
                        <?php elseif ($product['stock'] > 0): ?>
                            <span class="inline-flex items-center bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-sm font-medium"><i data-lucide="smile" class="w-4 h-4 mr-1"></i>Only <?php echo $product['stock']; ?> left</span>
                        <?php else: ?>
                            <span class="inline-flex items-center bg-red-100 text-red-700 px-3 py-1 rounded-full text-sm font-medium"><i data-lucide="frown" class="w-4 h-4 mr-1"></i> Out of Stock</span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Add to Cart Form -->
                    <?php if ($product['stock'] > 0): ?>
                    <form action="<?php echo BASE_URL; ?>cart.php" method="POST" class="mb-6">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        <input type="hidden" name="action" value="add">
                        
                        <!-- Weight Selection -->
                        <?php if (!empty($productWeights)): ?>
                        <div class="mb-4">
                            <label class="block font-semibold text-gray-700 mb-2">Select Weight:</label>
                            <div class="space-y-2">
                                <?php foreach ($productWeights as $weight): ?>
                                    <label class="flex items-center">
                                        <input type="radio" name="weight_id" value="<?php echo $weight['id']; ?>" 
                                               class="mr-3 text-primary-500 focus:ring-primary-500" 
                                               onchange="updatePrice(<?php echo $weight['price']; ?>, <?php echo $weight['stock']; ?>)"
                                               <?php echo $weight === $productWeights[0] ? 'checked' : ''; ?>>
                                        <span class="text-gray-700">
                                            <?php echo htmlspecialchars($weight['weight']); ?> - ₹<?php echo number_format($weight['price'], 2); ?>
                                            <?php if ($weight['stock'] <= 0): ?>
                                                <span class="text-red-500 text-sm">(Out of Stock)</span>
                                            <?php elseif ($weight['stock'] <= 5): ?>
                                                <span class="text-yellow-600 text-sm">(Only <?php echo $weight['stock']; ?> left)</span>
                                            <?php endif; ?>
                                        </span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="flex flex-wrap items-center gap-4">
                            <label class="font-semibold text-gray-700">Quantity:</label>
                            <div class="flex items-center border-2 border-gray-300 rounded-full overflow-hidden">
                                <button type="button" onclick="decreaseQty()" class="w-10 h-10 flex items-center justify-center hover:bg-gray-100 transition text-lg font-medium">-</button>
                                <input type="number" name="quantity" id="quantity" value="1" min="1" max="<?php echo !empty($productWeights) ? $productWeights[0]['stock'] : $product['stock']; ?>" class="w-14 text-center text-lg font-semibold border-x-2 border-gray-300">
                                <button type="button" onclick="increaseQty(<?php echo !empty($productWeights) ? $productWeights[0]['stock'] : $product['stock']; ?>)" class="w-10 h-10 flex items-center justify-center hover:bg-gray-100 transition text-lg font-medium">+</button>
                            </div>
                            <button type="submit" class="bg-primary-500 hover:bg-primary-600 text-white font-semibold py-3 px-8 rounded-full transition shadow-lg hover:shadow-xl inline-flex items-center">
                                <i class="fas fa-cart-plus mr-2"></i>Add to Cart
                            </button>
                        </div>
                    </form>
                    <?php endif; ?>
                    
                    <!-- Wishlist Button -->
                    <div class="mb-6">
                        <form method="POST" class="inline">
                            <input type="hidden" name="wishlist_action" value="<?php echo $inWishlist ? 'remove' : 'add'; ?>">
                            <?php if (isLoggedIn()): ?>
                                <button type="submit" class="<?php echo $inWishlist ? 'bg-red-500 hover:bg-red-600' : 'bg-gray-200 hover:bg-gray-300'; ?> text-<?php echo $inWishlist ? 'white' : 'gray-900'; ?> font-semibold py-3 px-6 rounded-full transition shadow-lg hover:shadow-xl inline-flex items-center">
                                    <i class="fas fa-heart mr-2"></i><?php echo $inWishlist ? 'Remove from Wishlist' : 'Add to Wishlist'; ?>
                                </button>
                            <?php else: ?>
                                <a href="<?php echo BASE_URL; ?>user/login.php" class="bg-gray-200 hover:bg-gray-300 text-gray-900 font-semibold py-3 px-6 rounded-full transition shadow-lg hover:shadow-xl inline-flex items-center">
                                    <i class="fas fa-heart mr-2"></i>Add to Wishlist
                                </a>
                            <?php endif; ?>
                        </form>
                        <a href="<?php echo BASE_URL; ?>user/wishlist.php" class="ml-3 text-primary-500 hover:text-primary-600 font-medium inline-flex items-center">
                            <i class="fas fa-list mr-1"></i>View Wishlist
                        </a>
                    </div>
                    
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
        
        <!-- Product Details Accordion -->
        <div class="mt-16 bg-neutral-dark text-white rounded-2xl p-6 md:p-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-5 pb-8">
                <h3 class="text-xl font-bold">Accepted Payment Methods</h3>
                <div class="flex flex-wrap items-center gap-3 md:justify-end" aria-label="Accepted payment methods">
                    <span class="inline-flex h-8 items-center rounded bg-white px-3 text-sm font-black italic text-blue-700">VISA</span>
                    <span class="inline-flex h-8 items-center rounded bg-white px-3 text-sm font-black text-red-600">Master<span class="text-yellow-500">Card</span></span>
                    <span class="inline-flex h-8 items-center rounded bg-white px-3 text-sm font-black text-blue-700">RuPay</span>
                    <span class="inline-flex h-8 items-center rounded bg-white px-3 text-sm font-black text-green-700">UPI</span>
                    <span class="inline-flex h-8 items-center rounded bg-white px-3 text-sm font-black text-blue-600">Maestro</span>
                </div>
            </div>

            <div class="divide-y divide-white/15">
                <?php foreach ($productDetailSections as $sectionTitle => $sectionContent): ?>
                    <?php $detailContent = $sectionContent !== '' ? $sectionContent : ($productDetailFallbacks[$sectionTitle] ?? 'Details will be updated soon.'); ?>
                    <details class="group" <?php echo $sectionTitle === 'Description' ? 'open' : ''; ?>>
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-4 py-6 text-lg font-bold">
                            <span><?php echo e($sectionTitle); ?></span>
                            <i class="fas fa-chevron-down text-sm transition group-open:rotate-180"></i>
                        </summary>
                        <div class="pb-6 leading-relaxed text-gray-300">
                            <?php echo nl2br(e($detailContent)); ?>
                        </div>
                    </details>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Reviews Section -->
        <div class="mt-16" id="reviews">
            <h3 class="text-2xl font-bold text-gray-900 mb-8">Customer Reviews</h3>

            <div class="mb-8">
                <div class="bg-[#111111] text-white px-6 md:px-10 py-6">
                    <div class="grid grid-cols-1 md:grid-cols-[1fr_1px_300px] items-center gap-6 md:gap-10">
                        <div>
                            <div class="flex items-center gap-1.5 text-orange-400 text-xl mb-1">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="far fa-star"></i>
                                <?php endfor; ?>
                            </div>
                            <p class="text-lg font-bold">
                                <?php echo count($reviews) === 0 ? 'Be the first to write a review' : 'Share your experience with this product'; ?>
                            </p>
                        </div>
                        <div class="hidden md:block h-20 bg-white/15"></div>
                        <div>
                            <?php if (isLoggedIn()): ?>
                            <button type="button" onclick="toggleReviewForm()" class="w-full bg-orange-400 hover:bg-orange-500 text-white text-lg font-bold py-4 px-8 transition">
                                <?php echo $userReview ? 'Update review' : 'Write a review'; ?>
                            </button>
                            <?php else: ?>
                            <a href="<?php echo BASE_URL; ?>user/login.php" class="w-full bg-orange-400 hover:bg-orange-500 text-white text-lg font-bold py-4 px-8 transition inline-flex items-center justify-center">
                                Write a review
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <?php if (isLoggedIn()): ?>
                <div id="reviewFormPanel" class="hidden bg-white border border-gray-100 shadow-sm p-6">
                    <h4 class="text-xl font-bold text-gray-900 mb-4"><?php echo $userReview ? 'Update Your Review' : 'Write a Review'; ?></h4>
                    <form method="POST" action="<?php echo BASE_URL; ?>product.php?id=<?php echo $productId; ?>#reviews" class="space-y-5">
                        <input type="hidden" name="review_action" value="save">

                        <div>
                            <label class="block font-semibold text-gray-700 mb-2">Your Rating</label>
                            <div class="review-star-rating flex flex-row-reverse justify-end gap-2 text-3xl">
                                <?php for ($ratingValue = 5; $ratingValue >= 1; $ratingValue--): ?>
                                <input type="radio" id="rating<?php echo $ratingValue; ?>" name="rating" value="<?php echo $ratingValue; ?>" class="sr-only" <?php echo ((int)($userReview['rating'] ?? 5) === $ratingValue) ? 'checked' : ''; ?>>
                                <label for="rating<?php echo $ratingValue; ?>" class="cursor-pointer text-gray-300 transition hover:text-orange-400" aria-label="<?php echo $ratingValue; ?> star<?php echo $ratingValue > 1 ? 's' : ''; ?>">
                                    <i class="fas fa-star"></i>
                                </label>
                                <?php endfor; ?>
                            </div>
                        </div>

                        <div>
                            <label for="reviewComment" class="block font-semibold text-gray-700 mb-2">Your Review</label>
                            <textarea id="reviewComment" name="comment" rows="4" maxlength="1000" required class="w-full px-4 py-3 border border-gray-300 rounded-xl outline-none focus:border-orange-400 transition" placeholder="Share your experience with this product"><?php echo e($userReview['comment'] ?? ''); ?></textarea>
                        </div>

                        <button type="submit" class="bg-orange-400 hover:bg-orange-500 text-white font-semibold py-3 px-6 rounded-full transition shadow-lg hover:shadow-xl inline-flex items-center">
                            <i class="fas fa-paper-plane mr-2"></i><?php echo $userReview ? 'Update Review' : 'Submit Review'; ?>
                        </button>
                    </form>
                </div>
                <?php endif; ?>
            </div>
            
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
                <?php foreach ($relatedProducts as $product): ?>
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
        </div>
        <?php endif; ?>
    </div>
</section>

<style>
.review-star-rating input:checked ~ label,
.review-star-rating label:hover,
.review-star-rating label:hover ~ label {
    color: #fb923c;
}
</style>

<script>
function changeMainImage(src) {
    document.getElementById('mainProductImage').src = src;
    
    // Update active state on thumbnails
    document.querySelectorAll('.grid-cols-4 img').forEach(img => {
        img.classList.remove('ring-2', 'ring-accent');
        if (img.src === src) {
            img.classList.add('ring-2', 'ring-accent');
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

function updatePrice(price, stock) {
    // Update quantity max based on selected weight stock
    const qtyInput = document.getElementById('quantity');
    qtyInput.max = stock;
    
    // Reset quantity to 1 if current value exceeds new stock
    if (parseInt(qtyInput.value) > stock) {
        qtyInput.value = 1;
    }
    
    // Update the increase button's onclick to use new stock
    const increaseBtn = qtyInput.nextElementSibling;
    increaseBtn.onclick = function() { increaseQty(stock); };
}

function toggleReviewForm() {
    const panel = document.getElementById('reviewFormPanel');
    if (!panel) return;

    panel.classList.toggle('hidden');
    if (!panel.classList.contains('hidden')) {
        panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>
