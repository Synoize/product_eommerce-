<?php
/**
 * Order Success Page
 * Shows confirmation after successful order placement
 */

$pageTitle = 'Order Successful';
require_once 'includes/header.php';

// Get order ID
$orderId = isset($_SESSION['last_order_id']) ? $_SESSION['last_order_id'] : 0;

if (!$orderId) {
    redirect(BASE_URL);
}

// Fetch order details
try {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
    $stmt->execute([$orderId, $_SESSION['user_id']]);
    $order = $stmt->fetch();
    
    if (!$order) {
        redirect(BASE_URL);
    }
} catch (PDOException $e) {
    redirect(BASE_URL);
}

// Fetch order items
try {
    $stmt = $pdo->prepare("SELECT oi.*, p.name as product_name 
                          FROM order_items oi 
                          JOIN products p ON oi.product_id = p.id 
                          WHERE oi.order_id = ?");
    $stmt->execute([$orderId]);
    $orderItems = $stmt->fetchAll();
} catch (PDOException $e) {
    $orderItems = [];
}

// Clear the order ID from session
unset($_SESSION['last_order_id']);
?>

<section class="py-12 bg-gray-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-2xl shadow-xl p-6 md:p-10 text-center">
            <!-- Success Icon -->
            <div class="mb-6">
                <div class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center mx-auto">
                    <i class="fas fa-check-circle text-6xl text-green-500"></i>
                </div>
            </div>
            
            <h2 class="text-3xl font-bold text-gray-900 mb-3">Order Placed Successfully!</h2>
            <p class="text-gray-500 mb-8">
                <?php 
                if ($order['payment_method'] === 'cod') {
                    echo 'Thank you for your purchase. Your initial payment has been received. Please pay the remaining amount on delivery.';
                } else {
                    echo 'Thank you for your purchase. Your payment has been confirmed.';
                }
                ?>
            </p>
            
            <!-- Order Details -->
            <div class="bg-gray-50 rounded-xl p-6 mb-8">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-left">
                    <div>
                        <p class="text-gray-500 text-sm mb-1">Order Number</p>
                        <p class="font-bold text-xl text-gray-900">#<?php echo $orderId; ?></p>
                    </div>
                    <div class="sm:text-right">
                        <p class="text-gray-500 text-sm mb-1">Order Total</p>
                        <p class="font-bold text-xl text-primary-500"><?php echo formatCurrency($order['total_amount']); ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Order Summary -->
            <div class="text-left mb-8">
                <h5 class="font-bold text-gray-900 mb-4">Order Summary</h5>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($orderItems as $item): ?>
                            <tr>
                                <td class="py-3">
                                    <?php echo e($item['product_name']); ?>
                                    <?php if (!empty($item['weight'])): ?>
                                        <span class="text-blue-600 text-xs">(<?php echo e($item['weight']); ?>)</span>
                                    <?php endif; ?>
                                    (x<?php echo $item['quantity']; ?>)
                                </td>
                                <td class="py-3 text-right font-medium"><?php echo formatCurrency($item['price'] * $item['quantity']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if ($order['discount_amount'] > 0): ?>
                            <tr>
                                <td class="py-3 text-green-600">Discount</td>
                                <td class="py-3 text-right text-green-600 font-medium">-<?php echo formatCurrency($order['discount_amount']); ?></td>
                            </tr>
                            <?php endif; ?>
                            <tr class="font-bold">
                                <td class="py-3">Total</td>
                                <td class="py-3 text-right text-primary-500"><?php echo formatCurrency($order['total_amount']); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Shipping Info -->
            <div class="text-left mb-8">
                <h5 class="font-bold text-gray-900 mb-4">Shipping Information</h5>
                <p class="font-medium text-gray-900 mb-1"><?php echo e($order['name']); ?></p>
                <p class="text-gray-600 text-sm mb-1"><?php echo e($order['address']); ?></p>
                <p class="text-gray-600 text-sm mb-1"><?php echo e($order['city']); ?>, <?php echo e($order['state']); ?> - <?php echo e($order['pincode']); ?></p>
                <p class="text-gray-600 text-sm">Mobile: <?php echo e($order['mobile']); ?></p>
            </div>
            
            <!-- Payment Info -->
            <div class="text-left mb-8">
                <h5 class="font-bold text-gray-900 mb-4">Payment Information</h5>
                <p class="text-gray-600 mb-1">Payment Method: <span class="font-medium text-gray-900"><?php echo $order['payment_method'] === 'cod' ? 'Cash on Delivery (COD)' : 'Online Payment'; ?></span></p>
                
                <?php if ($order['payment_method'] === 'cod'): ?>
                    <!-- COD Payment Details -->
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mt-3">
                        <p class="text-gray-600 mb-2">Total Amount: <span class="font-bold text-gray-900"><?php echo formatCurrency($order['total_amount']); ?></span></p>
                        <p class="text-gray-600 mb-2">Initial Payment (Paid): <span class="font-bold text-green-600"><?php echo formatCurrency($order['initial_payment_amount']); ?></span></p>
                        <p class="text-gray-600">Remaining Payment (On Delivery): <span class="font-bold text-orange-600"><?php echo formatCurrency($order['remaining_payment_amount']); ?></span></p>
                        <p class="text-gray-600 text-xs mt-2">Initial Payment Status: <span class="inline-block bg-green-100 text-green-700 px-2 py-1 rounded text-xs font-medium"><?php echo ucfirst(e($order['initial_payment_status'])); ?></span></p>
                    </div>
                <?php else: ?>
                    <!-- Online Payment Details -->
                    <p class="text-gray-600 mb-1">Payment Status: 
                        <span class="inline-block bg-green-100 text-green-700 px-2 py-1 rounded text-xs font-medium"><?php echo ucfirst(e($order['payment_status'])); ?></span>
                    </p>
                    <?php if ($order['razorpay_payment_id']): ?>
                    <p class="text-gray-400 text-xs">Payment ID: <?php echo e($order['razorpay_payment_id']); ?></p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            
            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <a href="<?php echo BASE_URL; ?>user/orders.php" class="inline-flex items-center justify-center bg-primary-500 hover:bg-primary-600 text-white font-semibold py-3 px-6 rounded-full transition shadow-lg hover:shadow-xl">
                    <i class="fas fa-box mr-2"></i>View My Orders
                </a>
                <a href="<?php echo BASE_URL; ?>shop.php" class="inline-flex items-center justify-center border-2 border-primary-500 text-primary-500 hover:bg-primary-500 hover:text-white font-semibold py-3 px-6 rounded-full transition">
                    <i class="fas fa-shopping-bag mr-2"></i>Continue Shopping
                </a>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
