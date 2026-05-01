<?php
/**
 * Checkout Page
 * Order placement with Razorpay integration
 */

$pageTitle = 'Checkout';
require_once 'includes/header.php';
require_once 'includes/razorpay.php';

// Require login
requireLogin();

// Require cart items
if (empty($_SESSION['cart'])) {
    setFlash('Your cart is empty. Please add products first.', 'warning');
    redirect(BASE_URL . 'shop.php');
}

// Get user details
$userId = $_SESSION['user_id'];
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
} catch (PDOException $e) {
    $user = [];
}

// Get saved addresses
$savedAddresses = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC, created_at DESC");
    $stmt->execute([$userId]);
    $savedAddresses = $stmt->fetchAll();
} catch (PDOException $e) {
    $savedAddresses = [];
}

// Handle address selection
$selectedAddress = null;
if (isset($_GET['address_id'])) {
    $addressId = (int)$_GET['address_id'];
    foreach ($savedAddresses as $addr) {
        if ($addr['id'] == $addressId) {
            $selectedAddress = $addr;
            break;
        }
    }
} elseif (!empty($savedAddresses)) {
    $selectedAddress = $savedAddresses[0]; // Default to first (default) address
}

// Calculate cart totals
$subtotal = 0;
$discount = 0;
$total = 0;

foreach ($_SESSION['cart'] as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

// Apply coupon discount
$couponCode = '';
if (isset($_SESSION['coupon'])) {
    $coupon = $_SESSION['coupon'];
    $couponCode = $coupon['code'];
    
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

// Handle checkout form submission (before payment)
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate form data
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $mobile = isset($_POST['mobile']) ? trim($_POST['mobile']) : '';
    $address = isset($_POST['address']) ? trim($_POST['address']) : '';
    $city = isset($_POST['city']) ? trim($_POST['city']) : '';
    $state = isset($_POST['state']) ? trim($_POST['state']) : '';
    $pincode = isset($_POST['pincode']) ? trim($_POST['pincode']) : '';
    
    // Validation
    if (empty($name)) $errors[] = 'Name is required';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required';
    if (empty($mobile) || !preg_match('/^[0-9]{10}$/', $mobile)) $errors[] = 'Valid 10-digit mobile number is required';
    if (empty($address)) $errors[] = 'Address is required';
    if (empty($city)) $errors[] = 'City is required';
    if (empty($state)) $errors[] = 'State is required';
    if (empty($pincode) || !preg_match('/^[0-9]{6}$/', $pincode)) $errors[] = 'Valid 6-digit pincode is required';
    
    // Store checkout data in session for after payment
    if (empty($errors)) {
        $_SESSION['checkout_data'] = [
            'name' => $name,
            'email' => $email,
            'mobile' => $mobile,
            'address' => $address,
            'city' => $city,
            'state' => $state,
            'pincode' => $pincode,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total' => $total,
            'coupon_code' => $couponCode
        ];
        
        // Create Razorpay Order
        try {
            $orderReceipt = 'order_' . time() . '_' . $userId;
            $razorpayOrder = createRazorpayOrder($total, $orderReceipt);
            
            if ($razorpayOrder && isset($razorpayOrder['id'])) {
                $_SESSION['razorpay_order_id'] = $razorpayOrder['id'];
                
                // Show payment button and options
                $showPayment = true;
            } else {
                $errors[] = 'Failed to create payment order. Please try again.';
            }
        } catch (Exception $e) {
            $errors[] = 'Payment initialization failed: ' . $e->getMessage();
            error_log('Razorpay Error: ' . $e->getMessage());
        }
    }
}
?>

<!-- Page Header -->
<section class="bg-primary-light py-4">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>">Home</a></li>
                <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>cart.php">Cart</a></li>
                <li class="breadcrumb-item active">Checkout</li>
            </ol>
        </nav>
        <h1 class="fw-bold mt-2">Checkout</h1>
    </div>
</section>

<!-- Checkout Content -->
<section class="py-5">
    <div class="container">
        <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                <li><?php echo e($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
        
        <div class="row">
            <!-- Checkout Form -->
            <div class="col-lg-8">
                <div class="checkout-section">
                    <h4 class="fw-bold mb-4">Billing & Shipping Information</h4>
                    
                    <?php if (isset($showPayment) && $showPayment): ?>
                    <!-- Razorpay Payment Button -->
                    <div class="text-center py-4">
                        <h5 class="mb-3">Complete Your Payment</h5>
                        <p class="text-muted mb-4">Order Total: <strong class="text-primary fs-4"><?php echo formatCurrency($total); ?></strong></p>
                        
                        <button id="rzp-button" class="btn btn-primary btn-lg">
                            <i class="fas fa-credit-card me-2"></i>Pay Now with Razorpay
                        </button>
                        
                        <div id="payment-loading" class="d-none mt-3">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2 text-muted">Processing payment...</p>
                        </div>
                    </div>
                    <?php else: ?>
                    
                    <!-- Saved Addresses Selection -->
                    <?php if (!empty($savedAddresses)): ?>
                    <div class="mb-4">
                        <h5 class="fw-bold mb-3">Select Delivery Address</h5>
                        <div class="row">
                            <?php foreach ($savedAddresses as $address): ?>
                            <div class="col-md-6 mb-3">
                                <div class="card h-100 <?php echo ($selectedAddress && $selectedAddress['id'] == $address['id']) ? 'border-primary' : ''; ?>">
                                    <div class="card-body">
                                        <?php if ($address['is_default']): ?>
                                        <span class="badge bg-primary mb-2">Default</span>
                                        <?php endif; ?>
                                        <h6 class="card-title fw-bold"><?php echo e($address['name']); ?></h6>
                                        <p class="card-text mb-1"><i class="fas fa-phone me-2"></i><?php echo e($address['mobile']); ?></p>
                                        <p class="card-text text-muted small"><?php echo e($address['address']); ?>, <?php echo e($address['city']); ?>, <?php echo e($address['state']); ?> - <?php echo e($address['pincode']); ?></p>
                                        
                                        <a href="?address_id=<?php echo $address['id']; ?>" 
                                           class="btn btn-sm <?php echo ($selectedAddress && $selectedAddress['id'] == $address['id']) ? 'btn-primary' : 'btn-outline-primary'; ?>">
                                            <?php echo ($selectedAddress && $selectedAddress['id'] == $address['id']) ? 'Selected' : 'Select'; ?>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <a href="<?php echo BASE_URL; ?>user/addresses.php" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-plus me-2"></i>Add New Address
                        </a>
                        <hr class="my-4">
                    </div>
                    <?php endif; ?>
                    
                    <!-- Checkout Form -->
                    <form action="<?php echo BASE_URL; ?>checkout.php" method="POST" id="checkoutForm" novalidate>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Full Name *</label>
                                <input type="text" name="name" class="form-control" 
                                       value="<?php echo e($_POST['name'] ?? $selectedAddress['name'] ?? $user['name'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email *</label>
                                <input type="email" name="email" class="form-control" 
                                       value="<?php echo e($_POST['email'] ?? $user['email'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Mobile Number *</label>
                                <input type="tel" name="mobile" class="form-control" 
                                       placeholder="Enter 10-digit mobile number"
                                       value="<?php echo e($_POST['mobile'] ?? $selectedAddress['mobile'] ?? $user['mobile'] ?? ''); ?>" 
                                       pattern="[0-9]{10}" maxlength="10" required>
                                <div class="form-text">Enter 10-digit mobile number without country code</div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Address *</label>
                                <textarea name="address" class="form-control" rows="3" required><?php echo e($_POST['address'] ?? $selectedAddress['address'] ?? $user['address'] ?? ''); ?></textarea>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">City *</label>
                                <input type="text" name="city" class="form-control" 
                                       value="<?php echo e($_POST['city'] ?? $selectedAddress['city'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">State *</label>
                                <input type="text" name="state" class="form-control" 
                                       value="<?php echo e($_POST['state'] ?? $selectedAddress['state'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Pincode *</label>
                                <input type="text" name="pincode" class="form-control" 
                                       value="<?php echo e($_POST['pincode'] ?? $selectedAddress['pincode'] ?? ''); ?>" 
                                       pattern="[0-9]{6}" maxlength="6" required>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="<?php echo BASE_URL; ?>cart.php" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Back to Cart
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-credit-card me-2"></i>Continue to Payment
                            </button>
                        </div>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Order Summary -->
            <div class="col-lg-4 mt-4 mt-lg-0">
                <div class="order-summary">
                    <h5 class="fw-bold mb-4">Order Summary</h5>
                    
                    <!-- Cart Items -->
                    <div class="mb-3">
                        <?php foreach ($_SESSION['cart'] as $item): ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-truncate" style="max-width: 150px;">
                                <?php echo e($item['name']); ?> (x<?php echo $item['quantity']; ?>)
                            </span>
                            <span><?php echo formatCurrency($item['price'] * $item['quantity']); ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <hr>
                    
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
                    
                    <div class="d-flex justify-content-between">
                        <span class="fw-bold">Total</span>
                        <span class="fw-bold fs-5"><?php echo formatCurrency($total); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if (isset($showPayment) && $showPayment): ?>
<!-- Razorpay Integration -->
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
var options = {
    "key": "<?php echo RAZORPAY_KEY_ID; ?>",
    "amount": "<?php echo $total * 100; ?>",
    "currency": "<?php echo RAZORPAY_CURRENCY; ?>",
    "name": "WebStore",
    "description": "Order Payment",
    "order_id": "<?php echo $_SESSION['razorpay_order_id']; ?>",
    "handler": function (response) {
        // Show loading
        document.getElementById('rzp-button').classList.add('d-none');
        document.getElementById('payment-loading').classList.remove('d-none');
        
        // Send payment data to server
        var form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?php echo BASE_URL; ?>payments/verify_razorpay_payment.php';
        
        var razorpayPaymentId = document.createElement('input');
        razorpayPaymentId.type = 'hidden';
        razorpayPaymentId.name = 'razorpay_payment_id';
        razorpayPaymentId.value = response.razorpay_payment_id;
        form.appendChild(razorpayPaymentId);
        
        var razorpayOrderId = document.createElement('input');
        razorpayOrderId.type = 'hidden';
        razorpayOrderId.name = 'razorpay_order_id';
        razorpayOrderId.value = response.razorpay_order_id;
        form.appendChild(razorpayOrderId);
        
        var razorpaySignature = document.createElement('input');
        razorpaySignature.type = 'hidden';
        razorpaySignature.name = 'razorpay_signature';
        razorpaySignature.value = response.razorpay_signature;
        form.appendChild(razorpaySignature);
        
        document.body.appendChild(form);
        form.submit();
    },
    "prefill": {
        "name": "<?php echo e($_SESSION['checkout_data']['name'] ?? ''); ?>",
        "email": "<?php echo e($_SESSION['checkout_data']['email'] ?? ''); ?>",
        "contact": "<?php echo e($_SESSION['checkout_data']['mobile'] ?? ''); ?>"
    },
    "theme": {
        "color": "#f84183"
    }
};

var rzp = new Razorpay(options);

document.getElementById('rzp-button').onclick = function(e) {
    rzp.open();
    e.preventDefault();
}
</script>
<?php endif; ?>

<script>
// Form validation
document.getElementById('checkoutForm')?.addEventListener('submit', function(e) {
    var mobile = document.querySelector('input[name="mobile"]');
    var pincode = document.querySelector('input[name="pincode"]');
    
    // Mobile validation - exactly 10 digits
    if (!/^[0-9]{10}$/.test(mobile.value)) {
        e.preventDefault();
        alert('Please enter a valid 10-digit mobile number');
        mobile.focus();
        return false;
    }
    
    // Pincode validation - exactly 6 digits
    if (!/^[0-9]{6}$/.test(pincode.value)) {
        e.preventDefault();
        alert('Please enter a valid 6-digit pincode');
        pincode.focus();
        return false;
    }
    
    return true;
});

// Real-time mobile validation
document.querySelector('input[name="mobile"]')?.addEventListener('input', function(e) {
    this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);
});

// Real-time pincode validation
document.querySelector('input[name="pincode"]')?.addEventListener('input', function(e) {
    this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6);
});
</script>

<?php require_once 'includes/footer.php'; ?>
