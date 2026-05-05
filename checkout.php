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

// Payment settings
define('COD_INITIAL_PAYMENT_PERCENTAGE', 30); // 30% upfront payment for COD
$paymentMethod = isset($_POST['payment_method']) ? $_POST['payment_method'] : 'online';
$initialPaymentAmount = 0;
$remainingPaymentAmount = 0;

if ($paymentMethod === 'cod') {
    $initialPaymentAmount = round($total * COD_INITIAL_PAYMENT_PERCENTAGE / 100, 2);
    $remainingPaymentAmount = $total - $initialPaymentAmount;
}

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
    $paymentMethod = isset($_POST['payment_method']) ? trim($_POST['payment_method']) : 'online';

    // Validation
    if (empty($name)) $errors[] = 'Name is required';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required';
    if (empty($mobile) || !preg_match('/^[0-9]{10}$/', $mobile)) $errors[] = 'Valid 10-digit mobile number is required';
    if (empty($address)) $errors[] = 'Address is required';
    if (empty($city)) $errors[] = 'City is required';
    if (empty($state)) $errors[] = 'State is required';
    if (empty($pincode) || !preg_match('/^[0-9]{6}$/', $pincode)) $errors[] = 'Valid 6-digit pincode is required';
    if (!in_array($paymentMethod, ['online', 'cod'])) $errors[] = 'Invalid payment method';

    // Store checkout data in session for after payment
    if (empty($errors)) {
        // Calculate payment amounts based on method
        if ($paymentMethod === 'cod') {
            $initialPaymentAmount = round($total * COD_INITIAL_PAYMENT_PERCENTAGE / 100, 2);
            $remainingPaymentAmount = $total - $initialPaymentAmount;
        } else {
            $initialPaymentAmount = $total;
            $remainingPaymentAmount = 0;
        }

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
            'payment_method' => $paymentMethod,
            'initial_payment_amount' => $initialPaymentAmount,
            'remaining_payment_amount' => $remainingPaymentAmount,
            'coupon_code' => $couponCode
        ];

        if ($paymentMethod === 'cod') {
            // For COD, process the initial payment
            if ($initialPaymentAmount > 0) {
                // Create Razorpay Order for initial payment
                try {
                    $orderReceipt = 'cod_initial_' . time() . '_' . $userId;
                    $razorpayOrder = createRazorpayOrder($initialPaymentAmount, $orderReceipt);

                    if ($razorpayOrder && isset($razorpayOrder['id'])) {
                        $_SESSION['razorpay_order_id'] = $razorpayOrder['id'];
                        $_SESSION['is_cod_initial_payment'] = true;

                        // Show payment button for initial payment
                        $showPayment = true;
                    } else {
                        $errors[] = 'Failed to create payment order. Please try again.';
                    }
                } catch (Exception $e) {
                    $errors[] = 'Payment initialization failed: ' . $e->getMessage();
                    error_log('Razorpay Error (COD Initial): ' . $e->getMessage());
                }
            } else {
                // No initial payment required, create order directly
                $_SESSION['is_cod_order'] = true;
                redirect(BASE_URL . 'payments/create_cod_order.php');
            }
        } else {
            unset($_SESSION['is_cod_initial_payment']);

            // For Online Payment, create Razorpay Order for full amount
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
}
?>

<!-- Page Header -->
<section class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <nav class="text-sm text-gray-600 mb-2">
            <ol class="flex items-center space-x-2">
                <li><a href="<?php echo BASE_URL; ?>" class="hover:text-primary-500">Home</a></li>
                <li><i class="fas fa-chevron-right text-xs"></i></li>
                <li><a href="<?php echo BASE_URL; ?>cart.php" class="hover:text-primary-500">Cart</a></li>
                <li><i class="fas fa-chevron-right text-xs"></i></li>
                <li class="text-accent font-medium">Checkout</li>
            </ol>
        </nav>
        <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Checkout</h1>
    </div>
</section>

<!-- Checkout Content -->
<section>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <?php if (!empty($errors)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
                <ul class="mb-0 list-disc list-inside">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Checkout Form -->
            <div class="lg:col-span-2">
                <div class="bg-white border rounded-2xl shadow-sm p-6 md:p-8">
                    <h4 class="text-xl font-bold text-gray-900 mb-6">Billing & Shipping Information</h4>

                    <?php if (isset($showPayment) && $showPayment): ?>
                        <!-- Show Order Summary Before Payment -->
                        <div class="bg-gray-50 rounded-xl p-6 mb-8">
                            <h5 class="font-semibold text-gray-900 mb-4">Order Details</h5>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Billing Information -->
                                <div>
                                    <h6 class="font-medium text-gray-900 mb-3">Billing Address</h6>
                                    <div class="text-sm text-gray-600 space-y-1">
                                        <p class="font-medium text-gray-900"><?php echo e($_SESSION['checkout_data']['name']); ?></p>
                                        <p><?php echo e($_SESSION['checkout_data']['address']); ?></p>
                                        <p><?php echo e($_SESSION['checkout_data']['city']); ?>, <?php echo e($_SESSION['checkout_data']['state']); ?> - <?php echo e($_SESSION['checkout_data']['pincode']); ?></p>
                                        <p><i class="fas fa-phone mr-2 text-primary-500"></i><?php echo e($_SESSION['checkout_data']['mobile']); ?></p>
                                        <p><i class="fas fa-envelope mr-2 text-primary-500"></i><?php echo e($_SESSION['checkout_data']['email']); ?></p>
                                    </div>
                                </div>

                                <!-- Payment Information -->
                                <div>
                                    <h6 class="font-medium text-gray-900 mb-3">Payment Details</h6>
                                    <div class="text-sm text-gray-600 space-y-2">
                                        <div class="flex justify-between items-center">
                                            <span>Payment Method:</span>
                                            <span class="font-medium text-gray-900">
                                                <?php echo $_SESSION['checkout_data']['payment_method'] === 'cod' ? 'Cash on Delivery (COD)' : 'Online Payment'; ?>
                                            </span>
                                        </div>

                                        <?php if ($_SESSION['checkout_data']['payment_method'] === 'cod'): ?>
                                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mt-3">
                                                <h6 class="font-medium text-gray-900 mb-2 text-xs">PAYMENT BREAKDOWN</h6>
                                                <div class="space-y-1">
                                                    <div class="flex justify-between items-center">
                                                        <span class="text-xs">Order Total:</span>
                                                        <span class="font-medium text-gray-900"><?php echo formatCurrency($_SESSION['checkout_data']['total']); ?></span>
                                                    </div>
                                                    <div class="flex justify-between items-center">
                                                        <span class="text-xs">Initial Payment (30%):</span>
                                                        <span class="font-medium text-accent"><?php echo formatCurrency($_SESSION['checkout_data']['initial_payment_amount']); ?></span>
                                                    </div>
                                                    <div class="flex justify-between items-center">
                                                        <span class="text-xs">Remaining at Delivery (70%):</span>
                                                        <span class="font-medium text-orange-600"><?php echo formatCurrency($_SESSION['checkout_data']['remaining_payment_amount']); ?></span>
                                                    </div>
                                                </div>
                                                <div class="mt-2 pt-2 border-t border-yellow-300">
                                                    <p class="text-xs text-gray-600">
                                                        <i class="fas fa-info-circle mr-1"></i>
                                                        Pay remaining amount when delivery arrives at your doorstep
                                                    </p>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <div class="bg-green-50 border border-green-200 rounded-lg p-3 mt-3">
                                                <div class="flex justify-between items-center">
                                                    <span class="text-xs font-medium text-gray-900">FULL PAYMENT AMOUNT:</span>
                                                    <span class="font-bold text-primary-500 text-lg"><?php echo formatCurrency($_SESSION['checkout_data']['total']); ?></span>
                                                </div>
                                                <div class="mt-2">
                                                    <p class="text-xs text-gray-600">
                                                        <i class="fas fa-check-circle mr-1 text-green-500"></i>
                                                        Complete payment now for instant order confirmation
                                                    </p>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <?php if (!empty($_SESSION['checkout_data']['coupon_code'])): ?>
                                            <div class="flex justify-between items-center pt-2 border-t border-gray-200">
                                                <span>Coupon Applied:</span>
                                                <span class="font-medium text-green-600"><?php echo strtoupper($_SESSION['checkout_data']['coupon_code']); ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Razorpay Payment Button -->
                        <div class="text-center py-8">
                            <?php if ($_SESSION['checkout_data']['payment_method'] === 'cod' && $_SESSION['checkout_data']['initial_payment_amount'] > 0): ?>
                                <h5 class="text-lg font-semibold text-gray-900 mb-3">Pay Initial Amount (30%)</h5>
                                <p class="text-gray-600 mb-2">You'll pay the remaining amount on delivery</p>
                                <p class="text-gray-500 mb-2">Full Order Amount: <strong class="text-primary-500 text-lg"><?php echo formatCurrency($total); ?></strong></p>
                                <p class="text-gray-500 mb-6">Initial Payment: <strong class="text-primary-500 text-2xl"><?php echo formatCurrency($_SESSION['checkout_data']['initial_payment_amount']); ?></strong></p>
                            <?php else: ?>
                                <h5 class="text-lg font-semibold text-gray-900 mb-3">Complete Your Payment</h5>
                                <p class="text-gray-500 mb-6">Order Total: <strong class="text-primary-500 text-2xl"><?php echo formatCurrency($total); ?></strong></p>
                            <?php endif; ?>

                            <button id="rzp-button" class="bg-primary-500 hover:bg-primary-600 text-white font-semibold py-4 px-8 rounded-full transition shadow-lg hover:shadow-xl">
                                <i class="fas fa-credit-card mr-2"></i><?php echo ($_SESSION['checkout_data']['payment_method'] === 'cod' && $_SESSION['checkout_data']['initial_payment_amount'] > 0) ? 'Pay (30%)' : 'Pay Now'; ?>
                            </button>

                            <div id="payment-loading" class="hidden mt-6">
                                <div class="w-12 h-12 border-4 border-primary-500 border-t-transparent rounded-full animate-spin mx-auto"></div>
                                <p class="mt-3 text-gray-500">Processing payment...</p>
                            </div>
                        </div>
                    <?php else: ?>

                        <!-- Checkout Form -->
                        <form action="<?php echo BASE_URL; ?>checkout.php" method="POST" id="checkoutForm" novalidate>
                            <!-- Payment Method Selection -->
                            <div class="mb-8 pb-6 border-b border-gray-200">
                                <h5 class="font-semibold text-gray-900 mb-4">Select Payment Method</h5>
                                <div class="space-y-3">
                                    <!-- Online Payment Option -->
                                    <label class="flex items-start p-4 border-2 rounded-lg cursor-pointer transition payment-method-option <?php echo ($paymentMethod !== 'cod') ? 'border-accent bg-accent-50' : 'border-gray-200 hover:border-gray-300'; ?>" data-method="online">
                                        <input type="radio" name="payment_method" value="online" 
                                            <?php echo ($paymentMethod !== 'cod') ? 'checked' : ''; ?>
                                            class="mt-1 w-4 h-4 text-accent cursor-pointer payment-method-input">
                                        <div class="ml-3 flex-1">
                                            <p class="font-semibold text-gray-900">Pay Online</p>
                                            <p class="text-sm text-gray-600">Pay the full amount using credit/debit card or UPI</p>
                                            <p class="text-sm font-medium text-primary-500 mt-1 payment-online-amount">Full Amount: <?php echo formatCurrency($total); ?></p>
                                        </div>
                                    </label>

                                    <!-- COD Option -->
                                    <label class="flex items-start p-4 border-2 rounded-lg cursor-pointer transition payment-method-option <?php echo ($paymentMethod === 'cod') ? 'border-accent bg-accent-50' : 'border-gray-200 hover:border-gray-300'; ?>" data-method="cod">
                                        <input type="radio" name="payment_method" value="cod" 
                                            <?php echo ($paymentMethod === 'cod') ? 'checked' : ''; ?>
                                            class="mt-1 w-4 h-4 text-accent cursor-pointer payment-method-input">
                                        <div class="ml-3 flex-1">
                                            <p class="font-semibold text-gray-900">Cash on Delivery (COD)</p>
                                            <p class="text-sm text-gray-600">Pay part now and the rest at delivery</p>
                                            <div class="text-sm font-medium mt-2 space-y-1 payment-cod-amount">
                                                <p class="text-accent">Initial Payment (30%): <?php echo formatCurrency(round($total * 30 / 100, 2)); ?></p>
                                                <p class="text-gray-600">Remaining at Delivery (70%): <?php echo formatCurrency(round($total * 70 / 100, 2)); ?></p>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- Address Selection & Billing Details -->
                            <div class="mb-8">
                                <h5 class="font-semibold text-gray-900 mb-4">Delivery Address & Billing Information</h5>

                                <!-- Saved Addresses Selection -->
                                <?php if (!empty($savedAddresses)): ?>
                                    <div class="mb-6">
                                        <h6 class="text-sm font-medium text-gray-700 mb-3">Select Delivery Address</h6>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <?php foreach ($savedAddresses as $address): ?>
                                                <div class="relative">
                                                    <div class="border-2 rounded-xl p-4 h-full <?php echo ($selectedAddress && $selectedAddress['id'] == $address['id']) ? 'border-accent bg-accent-50' : 'border-gray-200'; ?>">
                                                        <?php if ($address['is_default']): ?>
                                                            <span class="inline-block bg-accent text-white text-xs font-bold px-2 py-1 rounded mb-2">Default</span>
                                                        <?php endif; ?>
                                                        <h6 class="font-semibold text-gray-900 mb-2"><?php echo e($address['name']); ?></h6>
                                                        <p class="text-gray-600 text-sm mb-1"><i class="fas fa-phone mr-2 text-primary-500"></i><?php echo e($address['mobile']); ?></p>
                                                        <p class="text-gray-500 text-sm"><?php echo e($address['address']); ?>, <?php echo e($address['city']); ?>, <?php echo e($address['state']); ?> - <?php echo e($address['pincode']); ?></p>

                                                        <a href="?address_id=<?php echo $address['id']; ?>"
                                                            class="mt-3 inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium transition <?php echo ($selectedAddress && $selectedAddress['id'] == $address['id']) ? 'bg-accent text-white' : 'border-2 border-accent text-accent hover:bg-accent-50'; ?>">
                                                            <?php echo ($selectedAddress && $selectedAddress['id'] == $address['id']) ? 'Selected' : 'Select'; ?>
                                                        </a>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                        <a href="<?php echo BASE_URL; ?>user/addresses.php" class="inline-flex items-center border-2 border-accent text-accent hover:bg-accent-50 font-medium py-2 px-4 rounded-lg transition text-sm mt-4">
                                            <i class="fas fa-plus mr-2"></i>Add New Address
                                        </a>
                                    </div>
                                <?php endif; ?>

                                <!-- Billing Details Form -->
                                <div>
                                    <h6 class="text-sm font-medium text-gray-700 mb-3">Billing Details</h6>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
                                            <input type="text" name="name" required
                                                value="<?php echo e($_POST['name'] ?? $selectedAddress['name'] ?? $user['name'] ?? ''); ?>"
                                                class="w-full px-4 py-3 border rounded-lg outline-none focus:border-accent">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                                            <input type="email" name="email" required
                                                value="<?php echo e($_POST['email'] ?? $user['email'] ?? ''); ?>"
                                                class="w-full px-4 py-3 border rounded-lg outline-none focus:border-accent">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Mobile Number *</label>
                                            <input type="tel" name="mobile"
                                                placeholder="Enter 10-digit mobile number"
                                                pattern="[0-9]{10}" maxlength="10" required
                                                value="<?php echo e($_POST['mobile'] ?? $selectedAddress['mobile'] ?? $user['mobile'] ?? ''); ?>"
                                                class="w-full px-4 py-3 border rounded-lg outline-none focus:border-accent">
                                            <p class="text-xs text-gray-500 mt-1">Enter 10-digit mobile number without country code</p>
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Address *</label>
                                            <textarea name="address" rows="3" required
                                                class="w-full px-4 py-3 border rounded-lg outline-none focus:border-accent"><?php echo e($_POST['address'] ?? $selectedAddress['address'] ?? $user['address'] ?? ''); ?></textarea>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">City *</label>
                                            <input type="text" name="city" required
                                                value="<?php echo e($_POST['city'] ?? $selectedAddress['city'] ?? ''); ?>"
                                                class="w-full px-4 py-3 border rounded-lg outline-none focus:border-accent">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">State *</label>
                                            <input type="text" name="state" required
                                                value="<?php echo e($_POST['state'] ?? $selectedAddress['state'] ?? ''); ?>"
                                                class="w-full px-4 py-3 border rounded-lg outline-none focus:border-accent">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Pincode *</label>
                                            <input type="text" name="pincode"
                                                pattern="[0-9]{6}" maxlength="6" required
                                                value="<?php echo e($_POST['pincode'] ?? $selectedAddress['pincode'] ?? ''); ?>"
                                                class="w-full px-4 py-3 border rounded-lg outline-none focus:border-accent">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-6 border-gray-200">

                            <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                                <a href="<?php echo BASE_URL; ?>cart.php" class="group inline-flex items-center text-gray-600 hover:text-gray-900 font-medium">
                                    <i class="fas fa-arrow-left mr-2 transition duration-300 group-hover:-translate-x-1"></i>Back to Cart
                                </a>
                                <button type="submit" class="bg-primary-500 hover:bg-primary-600 text-white font-semibold py-4 px-8 rounded-lg transition hover:shadow-sm">
                                    <i class="fas fa-credit-card mr-2"></i>Continue to Payment
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Order Summary -->
            <div>
                <div class="bg-white border rounded-2xl shadow-sm p-6 sticky top-24">
                    <h5 class="text-xl font-bold text-gray-900 mb-6">Order Summary</h5>

                    <!-- Cart Items -->
                    <div class="space-y-3 mb-6">
                        <?php foreach ($_SESSION['cart'] as $item): ?>
                            <div class="flex justify-between items-center gap-3">

                                <!-- LEFT SIDE -->
                                <div class="flex min-w-0 flex-1 gap-2 text-sm">
                                    <span class="text-gray-600 truncate">
                                        <?php echo e($item['name']); ?> 
                                    </span>
                                    <span class="text-gray-400">
                                        (x<?php echo $item['quantity']; ?>)
                                    </span>
                                </div>

                                <!-- RIGHT SIDE (PRICE) -->
                                <span class="font-medium whitespace-nowrap">
                                    <?php echo formatCurrency($item['price'] * $item['quantity']); ?>
                                </span>

                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="border-t border-gray-200 pt-4 space-y-3 mb-4">
                        <!-- Price Breakdown -->
                        <div class="flex justify-between">
                            <span class="text-gray-500 text-sm">Subtotal</span>
                            <span class="font-medium"><?php echo formatCurrency($subtotal); ?></span>
                        </div>

                        <?php if ($discount > 0): ?>
                            <div class="flex justify-between">
                                <span class="text-gray-500 text-sm">Discount</span>
                                <span class="text-green-600 font-medium">-<?php echo formatCurrency($discount); ?></span>
                            </div>
                        <?php endif; ?>

                        <div class="flex justify-between">
                            <span class="text-gray-500 text-sm">Shipping</span>
                            <span class="text-green-600 font-medium">Free</span>
                        </div>
                    </div>

                    <div class="border-t border-gray-200 pt-4">
                        <div class="flex justify-between items-center font-semibold">
                            <span class="text-gray-900 text-xl">Total Amount</span>
                            <span class="text-xl text-primary-500"><?php echo formatCurrency($total); ?></span>
                        </div>
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
            "amount": "<?php echo ($_SESSION['checkout_data']['payment_method'] === 'cod' ? $_SESSION['checkout_data']['initial_payment_amount'] : $total) * 100; ?>",
            "currency": "<?php echo RAZORPAY_CURRENCY; ?>",
            "name": "Earthence",
            "description": "<?php echo ($_SESSION['checkout_data']['payment_method'] === 'cod' ? 'COD Initial Payment' : 'Order Payment'); ?>",
            "order_id": "<?php echo $_SESSION['razorpay_order_id']; ?>",
            "handler": function(response) {
                // Show loading
                document.getElementById('rzp-button').classList.add('hidden');
                document.getElementById('payment-loading').classList.remove('hidden');

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

    // Payment method selection handling
    document.querySelectorAll('.payment-method-input')?.forEach(function(input) {
        input.addEventListener('change', function() {
            const selectedMethod = this.value;
            
            // Update all payment method options styling
            document.querySelectorAll('.payment-method-option').forEach(function(label) {
                if (label.getAttribute('data-method') === selectedMethod) {
                    label.classList.remove('border-gray-200', 'hover:border-gray-300');
                    label.classList.add('border-accent', 'bg-accent-50');
                } else {
                    label.classList.remove('border-accent', 'bg-accent-50');
                    label.classList.add('border-gray-200', 'hover:border-gray-300');
                }
            });
        });
    });
</script>

<?php require_once 'includes/footer.php'; ?>