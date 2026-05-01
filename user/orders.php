<?php
/**
 * User Orders Page
 * Order history for logged in users
 */

$pageTitle = 'My Orders';
require_once __DIR__ . '/../includes/header.php';

// Require login
requireLogin();

$userId = $_SESSION['user_id'];

// Fetch user's orders
try {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$userId]);
    $orders = $stmt->fetchAll();
} catch (PDOException $e) {
    $orders = [];
    error_log('Orders Fetch Error: ' . $e->getMessage());
}

// Fetch order items for each order
$orderItems = [];
foreach ($orders as $order) {
    try {
        $stmt = $pdo->prepare("SELECT oi.*, p.name as product_name, p.image as product_image 
                              FROM order_items oi 
                              JOIN products p ON oi.product_id = p.id 
                              WHERE oi.order_id = ?");
        $stmt->execute([$order['id']]);
        $orderItems[$order['id']] = $stmt->fetchAll();
    } catch (PDOException $e) {
        $orderItems[$order['id']] = [];
    }
}
?>

<div class="container py-5">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 mb-4">
            <div class="card">
                <div class="card-body text-center">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['user_name']); ?>&background=f84183&color=fff&size=128" 
                         class="rounded-circle mb-3" width="100" alt="Profile">
                    <h5 class="fw-bold mb-1"><?php echo e($_SESSION['user_name']); ?></h5>
                    <p class="text-muted small mb-0"><?php echo e($_SESSION['user_email']); ?></p>
                </div>
                <div class="list-group list-group-flush">
                    <a href="<?php echo BASE_URL; ?>user/profile.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-user me-2"></i>My Profile
                    </a>
                    <a href="<?php echo BASE_URL; ?>user/addresses.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-map-marker-alt me-2"></i>Addresses
                    </a>
                    <a href="<?php echo BASE_URL; ?>user/orders.php" class="list-group-item list-group-item-action active">
                        <i class="fas fa-box me-2"></i>My Orders
                    </a>
                    <a href="<?php echo BASE_URL; ?>user/logout.php" class="list-group-item list-group-item-action text-danger">
                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Orders List -->
        <div class="col-md-9">
            <div class="card">
                <div class="card-body p-4">
                    <h4 class="fw-bold mb-4">My Orders</h4>
                    
                    <?php if (empty($orders)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-box-open fa-4x text-muted mb-3"></i>
                        <h5>No orders yet</h5>
                        <p class="text-muted">Start shopping to see your orders here!</p>
                        <a href="<?php echo BASE_URL; ?>shop.php" class="btn btn-primary">
                            <i class="fas fa-shopping-bag me-2"></i>Start Shopping
                        </a>
                    </div>
                    <?php else: ?>
                    <div class="accordion" id="ordersAccordion">
                        <?php foreach ($orders as $index => $order): ?>
                        <div class="accordion-item mb-3 border rounded">
                            <h2 class="accordion-header">
                                <button class="accordion-button <?php echo $index > 0 ? 'collapsed' : ''; ?>" type="button" 
                                        data-mdb-toggle="collapse" data-mdb-target="#order<?php echo $order['id']; ?>">
                                    <div class="d-flex justify-content-between align-items-center w-100 me-3">
                                        <div>
                                            <span class="fw-bold">Order #<?php echo $order['id']; ?></span>
                                            <span class="text-muted ms-3"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></span>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge badge-<?php echo $order['status']; ?> me-2">
                                                <?php echo ucfirst($order['status']); ?>
                                            </span>
                                            <span class="fw-bold"><?php echo formatCurrency($order['total_amount']); ?></span>
                                        </div>
                                    </div>
                                </button>
                            </h2>
                            <div id="order<?php echo $order['id']; ?>" class="accordion-collapse collapse <?php echo $index === 0 ? 'show' : ''; ?>" data-mdb-parent="#ordersAccordion">
                                <div class="accordion-body">
                                    <!-- Order Details -->
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <h6 class="fw-bold">Shipping Address</h6>
                                            <p class="text-muted mb-1"><?php echo e($order['name']); ?></p>
                                            <p class="text-muted mb-1"><?php echo e($order['address']); ?></p>
                                            <p class="text-muted mb-1"><?php echo e($order['city']); ?>, <?php echo e($order['state']); ?> - <?php echo e($order['pincode']); ?></p>
                                            <p class="text-muted mb-0">Mobile: <?php echo e($order['mobile']); ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <h6 class="fw-bold">Order Information</h6>
                                            <p class="text-muted mb-1">Payment Method: <?php echo ucfirst(e($order['payment_method'] ?? 'N/A')); ?></p>
                                            <p class="text-muted mb-1">Payment Status: 
                                                <span class="badge <?php echo ($order['payment_status'] ?? '') === 'paid' ? 'bg-success' : 'bg-warning'; ?>">
                                                    <?php echo ucfirst($order['payment_status'] ?? 'Pending'); ?>
                                                </span>
                                            </p>
                                            <?php if ($order['coupon_code']): ?>
                                            <p class="text-muted mb-0">Coupon: <span class="badge bg-primary"><?php echo e($order['coupon_code']); ?></span></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <!-- Order Items -->
                                    <h6 class="fw-bold mb-3">Order Items</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th>Product</th>
                                                    <th class="text-center">Qty</th>
                                                    <th class="text-end">Price</th>
                                                    <th class="text-end">Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($orderItems[$order['id']] as $item): ?>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <img src="<?php echo getImageUrl($item['product_image'], 'products'); ?>" 
                                                                 width="40" height="40" class="rounded me-2" alt="">
                                                            <span><?php echo e($item['product_name']); ?></span>
                                                        </div>
                                                    </td>
                                                    <td class="text-center"><?php echo $item['quantity']; ?></td>
                                                    <td class="text-end"><?php echo formatCurrency($item['price']); ?></td>
                                                    <td class="text-end"><?php echo formatCurrency($item['price'] * $item['quantity']); ?></td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                            <tfoot class="bg-light">
                                                <tr>
                                                    <td colspan="3" class="text-end"><strong>Subtotal</strong></td>
                                                    <td class="text-end"><?php echo formatCurrency($order['total_amount'] + $order['discount_amount']); ?></td>
                                                </tr>
                                                <?php if ($order['discount_amount'] > 0): ?>
                                                <tr>
                                                    <td colspan="3" class="text-end"><strong>Discount</strong></td>
                                                    <td class="text-end text-success">-<?php echo formatCurrency($order['discount_amount']); ?></td>
                                                </tr>
                                                <?php endif; ?>
                                                <tr>
                                                    <td colspan="3" class="text-end"><strong>Total</strong></td>
                                                    <td class="text-end"><strong><?php echo formatCurrency($order['total_amount']); ?></strong></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
