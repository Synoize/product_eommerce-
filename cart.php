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
    $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    
    switch ($action) {
        case 'add':
            $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
            
            // Validate product exists and has stock
            try {
                $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND status = 1");
                $stmt->execute([$productId]);
                $product = $stmt->fetch();
                
                if ($product) {
                    if ($product['stock'] >= $quantity) {
                        if (isset($_SESSION['cart'][$productId])) {
                            $_SESSION['cart'][$productId]['quantity'] += $quantity;
                        } else {
                            $_SESSION['cart'][$productId] = [
                                'id' => $product['id'],
                                'name' => $product['name'],
                                'price' => $product['price'],
                                'image' => $product['image'],
                                'quantity' => $quantity,
                                'stock' => $product['stock']
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
            
            if (isset($_SESSION['cart'][$productId])) {
                if ($quantity > 0) {
                    $_SESSION['cart'][$productId]['quantity'] = $quantity;
                    setFlash('Cart updated successfully!', 'success');
                } else {
                    unset($_SESSION['cart'][$productId]);
                    setFlash('Product removed from cart.', 'success');
                }
            }
            break;
            
        case 'remove':
            if (isset($_SESSION['cart'][$productId])) {
                unset($_SESSION['cart'][$productId]);
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
<section class="bg-primary-light py-4">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>">Home</a></li>
                <li class="breadcrumb-item active">Shopping Cart</li>
            </ol>
        </nav>
        <h1 class="fw-bold mt-2">Shopping Cart</h1>
    </div>
</section>

<!-- Cart Content -->
<section class="py-5">
    <div class="container">
        <?php if (empty($_SESSION['cart'])): ?>
        <!-- Empty Cart -->
        <div class="text-center py-5">
            <i class="fas fa-shopping-cart fa-4x text-muted mb-4"></i>
            <h3>Your cart is empty</h3>
            <p class="text-muted">Looks like you haven't added any products yet.</p>
            <a href="<?php echo BASE_URL; ?>shop.php" class="btn btn-primary btn-lg">
                <i class="fas fa-shopping-bag me-2"></i>Continue Shopping
            </a>
        </div>
        <?php else: ?>
        <div class="row">
            <!-- Cart Items -->
            <div class="col-lg-8">
                <!-- Clear Cart Button -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <p class="text-muted mb-0"><?php echo count($_SESSION['cart']); ?> item(s) in cart</p>
                    <form action="<?php echo BASE_URL; ?>cart.php" method="POST" class="d-inline">
                        <input type="hidden" name="action" value="clear">
                        <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Clear your cart?')">
                            <i class="fas fa-trash me-2"></i>Clear Cart
                        </button>
                    </form>
                </div>
                
                <!-- Cart Items List -->
                <?php foreach ($_SESSION['cart'] as $productId => $item): ?>
                <div class="cart-item">
                    <div class="row align-items-center">
                        <div class="col-3 col-md-2">
                            <?php 
                            $imageUrl = getImageUrl($item['image'], 'products');
                            ?>
                            <img src="<?php echo $imageUrl; ?>" alt="<?php echo e($item['name']); ?>">
                        </div>
                        <div class="col-9 col-md-4">
                            <h5 class="fw-bold mb-1"><?php echo e($item['name']); ?></h5>
                            <p class="text-muted mb-0"><?php echo formatCurrency($item['price']); ?></p>
                            <p class="text-muted small mb-0">Stock: <?php echo $item['stock']; ?> available</p>
                        </div>
                        <div class="col-6 col-md-3 mt-3 mt-md-0">
                            <form action="<?php echo BASE_URL; ?>cart.php" method="POST">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="product_id" value="<?php echo $productId; ?>">
                                <div class="quantity-control">
                                    <button type="button" onclick="this.parentElement.querySelector('input').stepDown(); this.form.submit();">-</button>
                                    <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $item['stock']; ?>" onchange="this.form.submit()">
                                    <button type="button" onclick="this.parentElement.querySelector('input').stepUp(); this.form.submit();">+</button>
                                </div>
                            </form>
                        </div>
                        <div class="col-6 col-md-2 mt-3 mt-md-0 text-end">
                            <p class="fw-bold mb-0"><?php echo formatCurrency($item['price'] * $item['quantity']); ?></p>
                        </div>
                        <div class="col-12 col-md-1 mt-3 mt-md-0 text-end">
                            <form action="<?php echo BASE_URL; ?>cart.php" method="POST" class="d-inline">
                                <input type="hidden" name="action" value="remove">
                                <input type="hidden" name="product_id" value="<?php echo $productId; ?>">
                                <button type="submit" class="btn btn-link text-danger p-0" onclick="return confirm('Remove this item?')">
                                    <i class="fas fa-times fa-lg"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <!-- Continue Shopping -->
                <a href="<?php echo BASE_URL; ?>shop.php" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left me-2"></i>Continue Shopping
                </a>
            </div>
            
            <!-- Cart Summary -->
            <div class="col-lg-4 mt-4 mt-lg-0">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title fw-bold mb-4">Order Summary</h5>
                        
                        <!-- Coupon Section -->
                        <?php if (!isset($_SESSION['coupon'])): ?>
                        <div class="coupon-section mb-4">
                            <p class="fw-bold mb-2"><i class="fas fa-tag me-2"></i>Have a coupon?</p>
                            <form action="<?php echo BASE_URL; ?>cart.php" method="POST">
                                <input type="hidden" name="action" value="apply_coupon">
                                <div class="input-group">
                                    <input type="text" name="coupon_code" class="form-control" placeholder="Enter coupon code" required>
                                    <button type="submit" class="btn btn-primary">Apply</button>
                                </div>
                            </form>
                        </div>
                        <?php else: ?>
                        <div class="mb-4">
                            <div class="coupon-applied mb-2">
                                <i class="fas fa-check-circle"></i>
                                <span>Coupon: <strong><?php echo e($_SESSION['coupon']['code']); ?></strong></span>
                            </div>
                            <form action="<?php echo BASE_URL; ?>cart.php" method="POST" class="d-inline">
                                <input type="hidden" name="action" value="remove_coupon">
                                <button type="submit" class="btn btn-link text-danger p-0">Remove coupon</button>
                            </form>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Price Breakdown -->
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Subtotal</span>
                            <span><?php echo formatCurrency($subtotal); ?></span>
                        </div>
                        
                        <?php if ($discount > 0): ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Discount</span>
                            <span class="text-success">-<?php echo formatCurrency($discount); ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Shipping</span>
                            <span class="text-success">Free</span>
                        </div>
                        
                        <hr>
                        
                        <div class="d-flex justify-content-between mb-4">
                            <span class="fw-bold">Total</span>
                            <span class="fw-bold fs-5 text-primary"><?php echo formatCurrency($total); ?></span>
                        </div>
                        
                        <!-- Checkout Button -->
                        <a href="<?php echo BASE_URL; ?>checkout.php" class="btn btn-primary w-100 btn-lg">
                            <i class="fas fa-credit-card me-2"></i>Proceed to Checkout
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
