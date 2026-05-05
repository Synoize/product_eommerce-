<?php

/**
 * Cart Page
 * Shopping Cart Management
 */

require_once __DIR__ . '/includes/db_connect.php';

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle cart actions BEFORE including header (to avoid headers already sent error)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $cartKey = isset($_POST['product_id']) ? (string)$_POST['product_id'] : '';
    $productId = (int)$cartKey;

    switch ($action) {
        case 'add':
            $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
            $weightId = isset($_POST['weight_id']) ? (int)$_POST['weight_id'] : null;

            // Validate product exists and has stock
            try {
                $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND status = 1");
                $stmt->execute([$productId]);
                $product = $stmt->fetch();

                if ($product) {
                    $availableStock = $product['stock'];
                    $price = $product['price'];
                    $weight = null;

                    // If weight is selected, get weight details
                    if ($weightId) {
                        $stmt = $pdo->prepare("SELECT * FROM product_weights WHERE id = ? AND product_id = ?");
                        $stmt->execute([$weightId, $productId]);
                        $weight = $stmt->fetch();

                        if ($weight) {
                            $availableStock = $weight['stock'];
                            $price = $weight['price'];
                        } else {
                            setFlash('Invalid weight selection.', 'danger');
                            redirect(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : BASE_URL . 'shop.php');
                            exit;
                        }
                    }

                    if ($availableStock >= $quantity) {
                        $cartKey = $weightId ? $productId . '_w' . $weightId : $productId;

                        if (isset($_SESSION['cart'][$cartKey])) {
                            $_SESSION['cart'][$cartKey]['quantity'] += $quantity;
                        } else {
                            $_SESSION['cart'][$cartKey] = [
                                'id' => $product['id'],
                                'name' => $product['name'],
                                'price' => $price,
                                'image' => $product['image'],
                                'quantity' => $quantity,
                                'stock' => $availableStock,
                                'weight_id' => $weightId,
                                'weight' => $weight ? $weight['weight'] : null
                            ];
                        }
                        setFlash('Product added to cart successfully!', 'success');
                    } else {
                        setFlash('Not enough stock available.', 'warning');
                    }
                } else {
                    setFlash('Product not found.', 'danger');
                }
            } catch (PDOException $e) {
                setFlash('Error adding product to cart.', 'danger');
            }

            // Redirect back to referring page or shop
            $redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : BASE_URL . 'shop.php';
            redirect($redirect);
            exit;

        case 'update':
            $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

            if ($cartKey !== '' && isset($_SESSION['cart'][$cartKey])) {
                if ($quantity > 0) {
                    $maxQty = (int)($_SESSION['cart'][$cartKey]['stock'] ?? 0);
                    if ($maxQty > 0) {
                        $quantity = min($quantity, $maxQty);
                    }
                    $_SESSION['cart'][$cartKey]['quantity'] = $quantity;
                    setFlash('Cart updated successfully!', 'success');
                } else {
                    unset($_SESSION['cart'][$cartKey]);
                    setFlash('Product removed from cart.', 'success');
                }
            }
            break;

        case 'remove':
            if ($cartKey !== '' && isset($_SESSION['cart'][$cartKey])) {
                unset($_SESSION['cart'][$cartKey]);
                setFlash('Product removed from cart.', 'success');
            }
            break;

        case 'clear':
            $_SESSION['cart'] = [];
            setFlash('Cart cleared successfully!', 'success');
            break;

        case 'apply_coupon':
            $couponCode = isset($_POST['coupon_code']) ? trim($_POST['coupon_code']) : '';

            if (empty($couponCode)) {
                setFlash('Please enter a coupon code.', 'warning');
            } else {
                // Validate coupon
                try {
                    $stmt = $pdo->prepare("SELECT * FROM coupons WHERE code = ? AND status = 1");
                    $stmt->execute([$couponCode]);
                    $coupon = $stmt->fetch();

                    if ($coupon) {
                        // Check expiry
                        if (strtotime($coupon['expiry_date']) < time()) {
                            setFlash('Coupon has expired.', 'danger');
                        }
                        // Check usage limit
                        elseif ($coupon['usage_limit'] > 0 && $coupon['used_count'] >= $coupon['usage_limit']) {
                            setFlash('Coupon usage limit reached.', 'danger');
                        } else {
                            // Calculate subtotal
                            $subtotal = 0;
                            foreach ($_SESSION['cart'] as $item) {
                                $subtotal += $item['price'] * $item['quantity'];
                            }

                            // Check minimum order
                            if ($coupon['min_order'] > 0 && $subtotal < $coupon['min_order']) {
                                setFlash('Minimum order amount of ' . formatCurrency($coupon['min_order']) . ' required.', 'warning');
                            } else {
                                $_SESSION['coupon'] = $coupon;
                                setFlash('Coupon applied successfully!', 'success');
                            }
                        }
                    } else {
                        setFlash('Invalid coupon code.', 'danger');
                    }
                } catch (PDOException $e) {
                    setFlash('Error applying coupon.', 'danger');
                }
            }
            break;

        case 'remove_coupon':
            unset($_SESSION['coupon']);
            setFlash('Coupon removed.', 'success');
            break;
    }

    // Redirect to avoid form resubmission
    redirect(BASE_URL . 'cart.php');
    exit;
}

$pageTitle = 'Shopping Cart';
require_once 'includes/header.php';

// Calculate cart totals
$subtotal = 0;
$discount = 0;
$total = 0;

foreach ($_SESSION['cart'] as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

// Apply coupon discount
if (isset($_SESSION['coupon'])) {
    $coupon = $_SESSION['coupon'];

    if ($coupon['type'] === 'percent') {
        $discount = ($subtotal * $coupon['value']) / 100;
        if ($coupon['max_discount'] > 0) {
            $discount = min($discount, $coupon['max_discount']);
        }
    } else {
        $discount = $coupon['value'];
    }

    $discount = min($discount, $subtotal);
}

$total = $subtotal - $discount;
?>

<!-- Page Header -->
<section class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <nav class="text-sm text-gray-600 mb-2">
            <ol class="flex items-center space-x-2">
                <li><a href="<?php echo BASE_URL; ?>" class="hover:text-primary-500">Home</a></li>
                <li><i class="fas fa-chevron-right text-xs"></i></li>
                <li class="text-accent font-medium">Shopping Cart</li>
            </ol>
        </nav>
        <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Shopping Cart</h1>
    </div>
</section>

<!-- Cart Content -->
<section>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <?php if (empty($_SESSION['cart'])): ?>
            <!-- Empty Cart -->
            <div class="text-center py-16">
                <i class="fas fa-shopping-cart text-7xl text-gray-300 mb-6"></i>
                <h3 class="text-2xl font-bold text-gray-900 mb-2">Your cart is empty</h3>
                <p class="text-gray-500 mb-6">Looks like you haven't added any products yet.</p>
                <a href="<?php echo BASE_URL; ?>shop.php" class="inline-flex items-center bg-accent hover:bg-accent-800 text-white font-semibold py-4 px-8 rounded-full transition shadow-lg hover:shadow-xl">
                    <i class="fas fa-shopping-bag mr-2"></i>Continue Shopping
                </a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Cart Items -->
                <div class="lg:col-span-2">
                    <!-- Clear Cart Button -->
                    <div class="flex justify-between items-center mb-6">
                        <p class="text-gray-500"><?php echo count($_SESSION['cart']); ?> item(s) in cart</p>
                        <form action="<?php echo BASE_URL; ?>cart.php" method="POST">
                            <input type="hidden" name="action" value="clear">
                            <button type="submit" class="text-red-500 hover:text-red-600 text-sm font-medium" onclick="return confirm('Clear your cart?')">
                                <i class="fas fa-trash mr-1"></i>Clear Cart
                            </button>
                        </form>
                    </div>

                    <!-- Cart Items List -->
                    <div class="space-y-4 mb-8">
                        <?php foreach ($_SESSION['cart'] as $productId => $item): ?>
                            <div class="bg-white border rounded-xl shadow-sm p-4 flex items-center gap-4">
                                <div class="w-20 h-20 flex-shrink-0">
                                    <?php $imageUrl = getImageUrl($item['image'], 'products'); ?>
                                    <a href="<?php echo BASE_URL; ?>product.php?id=<?php echo (int)$item['id']; ?>">
                                        <img src="<?php echo $imageUrl; ?>" alt="<?php echo e($item['name']); ?>" class="w-full h-full object-cover rounded-lg">
                                    </a>
                                </div>
                                <div class="flex-grow min-w-0">
                                    <h5 class="font-semibold text-gray-900 truncate">
                                        <a href="<?php echo BASE_URL; ?>product.php?id=<?php echo (int)$item['id']; ?>" class="hover:text-primary-500">
                                            <?php echo e($item['name']); ?>
                                        </a>
                                    </h5>
                                    <?php if (!empty($item['weight'])): ?>
                                        <p class="text-blue-600 text-sm font-medium"><?php echo e($item['weight']); ?></p>
                                    <?php endif; ?>
                                    <p class="text-gray-500 text-sm"><?php echo formatCurrency($item['price']); ?></p>
                                    <p class="text-xs font-medium text-gray-600">
                                        <?php if ($item['stock'] > 20): ?>
                                            <span class="text-green-600">In Stock</span> – Ready to ship
                                        <?php elseif ($item['stock'] > 0): ?>
                                            <span class="text-orange-500">Only <?php echo $item['stock']; ?> left in stock!</span>
                                        <?php else: ?>
                                            <span class="text-red-500">Out of Stock</span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <form action="<?php echo BASE_URL; ?>cart.php" method="POST" class="flex items-center">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="product_id" value="<?php echo $productId; ?>">
                                        <button type="button" onclick="this.form.quantity.stepDown(); this.form.submit();"
                                            class="w-8 h-8 rounded-lg bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-gray-600 transition">
                                            <i class="fas fa-minus text-xs"></i>
                                        </button>
                                        <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $item['stock']; ?>" onchange="this.form.submit()"
                                            class="ml-3 w-12 text-center text-sm font-medium outline-none">
                                        <button type="button" onclick="this.form.quantity.stepUp(); this.form.submit();"
                                            class="w-8 h-8 rounded-lg bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-gray-600 transition">
                                        <i class="fas fa-plus text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                                <div class="text-right min-w-[80px]">
                                    <p class="font-bold text-gray-900"><?php echo formatCurrency($item['price'] * $item['quantity']); ?></p>
                                </div>
                                <div>
                                    <form action="<?php echo BASE_URL; ?>cart.php" method="POST">
                                        <input type="hidden" name="action" value="remove">
                                        <input type="hidden" name="product_id" value="<?php echo $productId; ?>">
                                        <button type="submit" class="w-8 h-8 border border-red-500 rounded-full text-red-400 hover:text-red-500 transition" onclick="return confirm('Remove this item?')">
                                            <i class="fas fa-times text-lg"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Continue Shopping -->
                    <a href="<?php echo BASE_URL; ?>shop.php" class="group inline-flex items-center border-2 border-accent text-accent hover:bg-accent-800 hover:text-white font-medium py-3 px-6 rounded-full transition">
                        <i class="fas fa-arrow-left mr-2 transition duration-300 group-hover:-translate-x-1 "></i>Continue Shopping
                    </a>
                </div>

                <!-- Cart Summary -->
                <div>
                    <div class="bg-white border rounded-2xl shadow-sm p-6 sticky top-24">
                        <h5 class="text-xl font-bold text-gray-900 mb-6">Order Summary</h5>

                        <!-- Coupon Section -->
                        <?php if (!isset($_SESSION['coupon'])): ?>
                            <div class="mb-6">
                                <p class="font-semibold text-gray-700 mb-3"><i class="fas fa-tag mr-2 text-primary-500"></i>Have a coupon?</p>
                                <form action="<?php echo BASE_URL; ?>cart.php" method="POST">
                                    <input type="hidden" name="action" value="apply_coupon">
                                    <div class="flex">
                                        <input type="text" name="coupon_code" placeholder="Enter coupon code" required
                                            class="flex-1 px-4 py-2 border rounded-l-lg text-sm outline-none focus:border-accent">
                                        <button type="submit" class="bg-accent hover:bg-accent-700/90 text-white font-medium px-4 py-2 rounded-r-lg transition">Apply</button>
                                    </div>
                                </form>
                            </div>
                        <?php else: ?>
                            <div class="mb-6">
                                <div class="flex items-center gap-2 text-green-600 mb-2">
                                    <i class="fas fa-check-circle"></i>
                                    <span class="font-medium">Coupon: <strong><?php echo e($_SESSION['coupon']['code']); ?></strong></span>
                                </div>
                                <form action="<?php echo BASE_URL; ?>cart.php" method="POST">
                                    <input type="hidden" name="action" value="remove_coupon">
                                    <button type="submit" class="text-red-500 hover:text-red-600 text-sm">Remove coupon</button>
                                </form>
                            </div>
                        <?php endif; ?>

                        <!-- Price Breakdown -->
                        <div class="space-y-3 mb-6">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Subtotal</span>
                                <span class="font-medium"><?php echo formatCurrency($subtotal); ?></span>
                            </div>

                            <?php if ($discount > 0): ?>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Discount</span>
                                    <span class="text-green-600 font-medium">-<?php echo formatCurrency($discount); ?></span>
                                </div>
                            <?php endif; ?>

                            <div class="flex justify-between">
                                <span class="text-gray-500">Shipping</span>
                                <span class="text-green-600 font-medium">Free</span>
                            </div>
                        </div>

                        <div class="border-t border-gray-200 pt-4 mb-6">
                            <div class="flex justify-between">
                                <span class="font-bold text-gray-900">Total</span>
                                <span class="font-bold text-xl text-primary-500"><?php echo formatCurrency($total); ?></span>
                            </div>
                        </div>

                        <!-- Checkout Button -->
                        <a href="<?php echo BASE_URL; ?>checkout.php" class="block w-full bg-accent hover:bg-accent-800 text-white font-semibold py-4 px-6 rounded-lg text-center transition hover:shadow-lg">
                            <i class="fas fa-credit-card mr-2"></i>Proceed to Checkout
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>