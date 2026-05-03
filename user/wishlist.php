<?php
/**
 * User Wishlist Page
 */

require_once __DIR__ . '/../includes/header.php';
requireLogin();

$userId = $_SESSION['user_id'];

// Handle remove from wishlist
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'remove') {
    $productId = (int)$_POST['product_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$userId, $productId]);
        setFlash('Product removed from wishlist', 'success');
    } catch (PDOException $e) {
        setFlash('Error removing from wishlist', 'danger');
    }
}

// Handle move to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'move_to_cart') {
    $productId = (int)$_POST['product_id'];
    $quantity = (int)$_POST['quantity'] ?? 1;
    
    // Add to cart session
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    // Check if product already in cart
    $found = false;
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['product_id'] == $productId) {
            $item['quantity'] += $quantity;
            $found = true;
            break;
        }
    }
    
    if (!$found) {
        $_SESSION['cart'][] = [
            'product_id' => $productId,
            'quantity' => $quantity
        ];
    }
    
    // Remove from wishlist
    try {
        $stmt = $pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$userId, $productId]);
    } catch (PDOException $e) {
        // Silent fail
    }
    
    setFlash('Product moved to cart', 'success');
}

// Fetch wishlist items
$wishlistItems = [];
try {
    $stmt = $pdo->prepare("
        SELECT p.*, c.name as category_name 
        FROM wishlist w 
        JOIN products p ON w.product_id = p.id 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE w.user_id = ? 
        ORDER BY w.created_at DESC
    ");
    $stmt->execute([$userId]);
    $wishlistItems = $stmt->fetchAll();
} catch (PDOException $e) {
    setFlash('Error loading wishlist', 'danger');
}

$pageTitle = 'My Wishlist';
?>

<!-- Breadcrumb -->
<section class="bg-gradient-to-r from-primary-50 to-primary-100 py-4 mt-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <nav class="text-sm text-gray-600">
            <ol class="flex items-center space-x-2">
                <li><a href="<?php echo BASE_URL; ?>" class="hover:text-primary-500">Home</a></li>
                <li><i class="fas fa-chevron-right text-xs"></i></li>
                <li><a href="<?php echo BASE_URL; ?>user/profile.php" class="hover:text-primary-500">My Account</a></li>
                <li><i class="fas fa-chevron-right text-xs"></i></li>
                <li class="text-primary-600 font-medium">Wishlist</li>
            </ol>
        </nav>
    </div>
</section>

<!-- Wishlist Section -->
<section class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl md:text-4xl font-bold text-gray-900">My Wishlist</h1>
            <p class="text-gray-600 mt-2">Save your favorite products for later</p>
        </div>

        <?php if (empty($wishlistItems)): ?>
            <div class="bg-white rounded-xl shadow-md p-12 text-center">
                <div class="mb-4">
                    <i class="fas fa-heart text-6xl text-gray-300"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Your wishlist is empty</h2>
                <p class="text-gray-600 mb-6">Start adding products you love to your wishlist</p>
                <a href="<?php echo BASE_URL; ?>shop.php" class="inline-flex items-center bg-primary-500 hover:bg-primary-600 text-white font-semibold py-3 px-8 rounded-full transition shadow-lg hover:shadow-xl">
                    <i class="fas fa-shopping-bag mr-2"></i>Continue Shopping
                </a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($wishlistItems as $item): ?>
                <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition">
                    <!-- Product Image -->
                    <div class="relative bg-gray-100 h-64 overflow-hidden group">
                        <img src="<?php echo getImageUrl($item['image'], 'products'); ?>" 
                             alt="<?php echo e($item['name']); ?>" 
                             class="w-full h-full object-cover group-hover:scale-110 transition duration-300">
                        
                        <!-- Category Badge -->
                        <span class="absolute top-3 left-3 bg-primary-500 text-white text-xs font-bold px-3 py-1 rounded-full">
                            <?php echo e($item['category_name'] ?? 'Product'); ?>
                        </span>
                        
                        <!-- Remove Button -->
                        <form method="POST" class="absolute top-3 right-3">
                            <input type="hidden" name="action" value="remove">
                            <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                            <button type="submit" class="bg-red-500 hover:bg-red-600 text-white p-2 rounded-full transition shadow-lg hover:shadow-xl" title="Remove from wishlist">
                                <i class="fas fa-trash text-sm"></i>
                            </button>
                        </form>
                    </div>
                    
                    <!-- Product Info -->
                    <div class="p-4">
                        <!-- Product Name -->
                        <h3 class="text-lg font-bold text-gray-900 mb-2 line-clamp-2">
                            <a href="<?php echo BASE_URL; ?>product.php?id=<?php echo $item['id']; ?>" class="hover:text-primary-500 transition">
                                <?php echo e($item['name']); ?>
                            </a>
                        </h3>
                        
                        <!-- Price -->
                        <div class="mb-4">
                            <span class="text-2xl font-bold text-primary-500"><?php echo formatCurrency($item['price']); ?></span>
                            <?php if ($item['original_price'] > $item['price']): ?>
                            <span class="text-gray-400 line-through ml-2"><?php echo formatCurrency($item['original_price']); ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Stock Status -->
                        <div class="mb-4">
                            <?php if ($item['stock'] > 0): ?>
                                <span class="text-sm text-green-600 font-medium"><i class="fas fa-check mr-1"></i>In Stock</span>
                            <?php else: ?>
                                <span class="text-sm text-red-600 font-medium"><i class="fas fa-times mr-1"></i>Out of Stock</span>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Buttons -->
                        <div class="flex gap-2">
                            <a href="<?php echo BASE_URL; ?>product.php?id=<?php echo $item['id']; ?>" 
                               class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-900 font-semibold py-2 px-4 rounded-lg transition text-center">
                                <i class="fas fa-eye mr-1"></i>View
                            </a>
                            <?php if ($item['stock'] > 0): ?>
                            <form method="POST" class="flex-1">
                                <input type="hidden" name="action" value="move_to_cart">
                                <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="w-full bg-primary-500 hover:bg-primary-600 text-white font-semibold py-2 px-4 rounded-lg transition">
                                    <i class="fas fa-cart-plus mr-1"></i>Add Cart
                                </button>
                            </form>
                            <?php else: ?>
                            <button disabled class="flex-1 bg-gray-200 text-gray-500 font-semibold py-2 px-4 rounded-lg cursor-not-allowed">
                                Out of Stock
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Continue Shopping -->
            <div class="mt-12 text-center">
                <a href="<?php echo BASE_URL; ?>shop.php" class="inline-flex items-center border-2 border-primary-500 text-primary-500 hover:bg-primary-500 hover:text-white font-semibold py-3 px-8 rounded-full transition">
                    <i class="fas fa-arrow-left mr-2"></i>Continue Shopping
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
