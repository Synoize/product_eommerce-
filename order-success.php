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

<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow">
                    <div class="card-body p-5 text-center">
                        <!-- Success Icon -->
                        <div class="mb-4">
                            <i class="fas fa-check-circle fa-5x text-success"></i>
                        </div>
                        
                        <h2 class="fw-bold mb-3">Order Placed Successfully!</h2>
                        <p class="text-muted mb-4">Thank you for your purchase. Your order has been confirmed.</p>
                        
                        <!-- Order Details -->
                        <div class="bg-light p-4 rounded mb-4">
                            <div class="row">
                                <div class="col-sm-6 text-start mb-3 mb-sm-0">
                                    <p class="text-muted mb-1">Order Number</p>
                                    <p class="fw-bold fs-5 mb-0">#<?php echo $orderId; ?></p>
                                </div>
                                <div class="col-sm-6 text-start text-sm-end">
                                    <p class="text-muted mb-1">Order Total</p>
                                    <p class="fw-bold fs-5 text-primary mb-0"><?php echo formatCurrency($order['total_amount']); ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Order Summary -->
                        <div class="text-start mb-4">
                            <h5 class="fw-bold mb-3">Order Summary</h5>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <tbody>
                                        <?php foreach ($orderItems as $item): ?>
                                        <tr>
                                            <td><?php echo e($item['product_name']); ?> (x<?php echo $item['quantity']; ?>)</td>
                                            <td class="text-end"><?php echo formatCurrency($item['price'] * $item['quantity']); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php if ($order['discount_amount'] > 0): ?>
                                        <tr>
                                            <td class="text-success">Discount</td>
                                            <td class="text-end text-success">-<?php echo formatCurrency($order['discount_amount']); ?></td>
                                        </tr>
                                        <?php endif; ?>
                                        <tr class="fw-bold">
                                            <td>Total</td>
                                            <td class="text-end"><?php echo formatCurrency($order['total_amount']); ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Shipping Info -->
                        <div class="text-start mb-4">
                            <h5 class="fw-bold mb-3">Shipping Information</h5>
                            <p class="mb-1"><strong><?php echo e($order['name']); ?></strong></p>
                            <p class="text-muted mb-1"><?php echo e($order['address']); ?></p>
                            <p class="text-muted mb-1"><?php echo e($order['city']); ?>, <?php echo e($order['state']); ?> - <?php echo e($order['pincode']); ?></p>
                            <p class="text-muted mb-0">Mobile: <?php echo e($order['mobile']); ?></p>
                        </div>
                        
                        <!-- Payment Info -->
                        <div class="text-start mb-4">
                            <h5 class="fw-bold mb-3">Payment Information</h5>
                            <p class="mb-1">Payment Method: <strong><?php echo ucfirst(e($order['payment_method'])); ?></strong></p>
                            <p class="mb-1">Payment Status: 
                                <span class="badge bg-success"><?php echo ucfirst(e($order['payment_status'])); ?></span>
                            </p>
                            <?php if ($order['razorpay_payment_id']): ?>
                            <p class="mb-0 text-muted small">Payment ID: <?php echo e($order['razorpay_payment_id']); ?></p>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-center gap-3">
                            <a href="<?php echo BASE_URL; ?>user/orders.php" class="btn btn-primary">
                                <i class="fas fa-box me-2"></i>View My Orders
                            </a>
                            <a href="<?php echo BASE_URL; ?>shop.php" class="btn btn-outline-primary">
                                <i class="fas fa-shopping-bag me-2"></i>Continue Shopping
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
