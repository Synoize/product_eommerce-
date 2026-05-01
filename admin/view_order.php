<?php
/**
 * Admin - View Order Details
 */

$pageTitle = 'Order Details';
require_once __DIR__ . '/../includes/header.php';
requireAdmin();

// Get order ID
$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($orderId <= 0) {
    setFlash('Invalid order ID', 'danger');
    redirect(BASE_URL . 'admin/manage_orders.php');
}

// Fetch order details
try {
    $stmt = $pdo->prepare("SELECT o.*, u.name as user_name, u.email as user_email, u.mobile as user_mobile 
                          FROM orders o 
                          JOIN users u ON o.user_id = u.id 
                          WHERE o.id = ?");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch();
    
    if (!$order) {
        setFlash('Order not found', 'danger');
        redirect(BASE_URL . 'admin/manage_orders.php');
    }
} catch (PDOException $e) {
    setFlash('Error loading order', 'danger');
    redirect(BASE_URL . 'admin/manage_orders.php');
}

// Fetch order items
try {
    $stmt = $pdo->prepare("SELECT oi.*, p.name as product_name, p.image as product_image 
                          FROM order_items oi 
                          JOIN products p ON oi.product_id = p.id 
                          WHERE oi.order_id = ?");
    $stmt->execute([$orderId]);
    $orderItems = $stmt->fetchAll();
} catch (PDOException $e) {
    $orderItems = [];
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $newStatus = $_POST['status'] ?? '';
    $allowedStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
    
    if (in_array($newStatus, $allowedStatuses)) {
        try {
            $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $stmt->execute([$newStatus, $orderId]);
            
            // Refresh order data
            $stmt = $pdo->prepare("SELECT o.*, u.name as user_name, u.email as user_email, u.mobile as user_mobile 
                                  FROM orders o 
                                  JOIN users u ON o.user_id = u.id 
                                  WHERE o.id = ?");
            $stmt->execute([$orderId]);
            $order = $stmt->fetch();
            
            setFlash('Order status updated successfully!', 'success');
        } catch (PDOException $e) {
            setFlash('Error updating order status', 'danger');
        }
    }
}
?>

<div class="container-fluid">
    <div class="row">
        <!-- Admin Sidebar -->
        <div class="col-md-2 d-none d-md-block admin-sidebar">
            <nav class="nav flex-column">
                <a class="nav-link" href="<?php echo BASE_URL; ?>admin/index.php">
                    <i class="fas fa-tachometer-alt"></i>Dashboard
                </a>
                <a class="nav-link" href="<?php echo BASE_URL; ?>admin/manage_products.php">
                    <i class="fas fa-box"></i>Products
                </a>
                <a class="nav-link" href="<?php echo BASE_URL; ?>admin/manage_categories.php">
                    <i class="fas fa-tags"></i>Categories
                </a>
                <a class="nav-link active" href="<?php echo BASE_URL; ?>admin/manage_orders.php">
                    <i class="fas fa-shopping-cart"></i>Orders
                </a>
                <a class="nav-link" href="<?php echo BASE_URL; ?>admin/manage_coupons.php">
                    <i class="fas fa-ticket-alt"></i>Coupons
                </a>
                <a class="nav-link" href="<?php echo BASE_URL; ?>admin/manage_users.php">
                    <i class="fas fa-users"></i>Users
                </a>
                <a class="nav-link" href="<?php echo BASE_URL; ?>admin/contact_messages.php">
                    <i class="fas fa-envelope"></i>Messages
                </a>
                <a class="nav-link" href="<?php echo BASE_URL; ?>">
                    <i class="fas fa-arrow-left"></i>Back to Site
                </a>
            </nav>
        </div>
        
        <!-- Main Content -->
        <div class="col-md-10 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold mb-0">Order #<?php echo $orderId; ?></h2>
                <a href="<?php echo BASE_URL; ?>admin/manage_orders.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Orders
                </a>
            </div>
            
            <div class="row">
                <!-- Order Info -->
                <div class="col-lg-8">
                    <!-- Order Status -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title fw-bold mb-3">Order Status</h5>
                            <form action="<?php echo BASE_URL; ?>admin/view_order.php?id=<?php echo $orderId; ?>" method="POST">
                                <div class="row align-items-center">
                                    <div class="col-md-4">
                                        <span class="badge badge-<?php echo $order['status']; ?> fs-6">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </div>
                                    <div class="col-md-5">
                                        <select name="status" class="form-select">
                                            <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                            <option value="shipped" <?php echo $order['status'] === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                            <option value="delivered" <?php echo $order['status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                            <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <button type="submit" name="update_status" class="btn btn-primary w-100">
                                            <i class="fas fa-sync me-2"></i>Update
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Order Items -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title fw-bold mb-3">Order Items</h5>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Product</th>
                                            <th class="text-center">Qty</th>
                                            <th class="text-end">Price</th>
                                            <th class="text-end">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($orderItems as $item): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="<?php echo getImageUrl($item['product_image'], 'products'); ?>" 
                                                         width="50" height="50" class="rounded me-2" alt="">
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
                    
                    <!-- Shipping Address -->
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title fw-bold mb-3">Shipping Information</h5>
                            <p class="mb-1"><strong><?php echo e($order['name']); ?></strong></p>
                            <p class="mb-1"><?php echo e($order['address']); ?></p>
                            <p class="mb-1"><?php echo e($order['city']); ?>, <?php echo e($order['state']); ?> - <?php echo e($order['pincode']); ?></p>
                            <p class="mb-1">Email: <?php echo e($order['email']); ?></p>
                            <p class="mb-0">Mobile: <?php echo e($order['mobile']); ?></p>
                        </div>
                    </div>
                </div>
                
                <!-- Order Summary -->
                <div class="col-lg-4">
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title fw-bold mb-3">Order Summary</h5>
                            <p class="mb-2"><strong>Order Date:</strong> <?php echo date('F d, Y H:i', strtotime($order['created_at'])); ?></p>
                            <p class="mb-2"><strong>Order ID:</strong> #<?php echo $order['id']; ?></p>
                            
                            <hr>
                            
                            <h6 class="fw-bold">Payment Information</h6>
                            <p class="mb-1"><strong>Method:</strong> <?php echo ucfirst(e($order['payment_method'] ?? 'N/A')); ?></p>
                            <p class="mb-1"><strong>Status:</strong> 
                                <span class="badge <?php echo ($order['payment_status'] ?? '') === 'paid' ? 'bg-success' : 'bg-warning'; ?>">
                                    <?php echo ucfirst($order['payment_status'] ?? 'Pending'); ?>
                                </span>
                            </p>
                            <?php if ($order['razorpay_payment_id']): ?>
                            <p class="mb-1"><strong>Payment ID:</strong> <span class="text-muted small"><?php echo e($order['razorpay_payment_id']); ?></span></p>
                            <?php endif; ?>
                            <?php if ($order['razorpay_order_id']): ?>
                            <p class="mb-0"><strong>Order ID:</strong> <span class="text-muted small"><?php echo e($order['razorpay_order_id']); ?></span></p>
                            <?php endif; ?>
                            
                            <?php if ($order['coupon_code']): ?>
                            <hr>
                            <h6 class="fw-bold">Coupon Applied</h6>
                            <p class="mb-0"><span class="badge bg-primary"><?php echo e($order['coupon_code']); ?></span></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Customer Info -->
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title fw-bold mb-3">Customer Information</h5>
                            <p class="mb-1"><strong>Name:</strong> <?php echo e($order['user_name']); ?></p>
                            <p class="mb-1"><strong>Email:</strong> <?php echo e($order['user_email']); ?></p>
                            <p class="mb-0"><strong>Mobile:</strong> <?php echo e($order['user_mobile'] ?? 'N/A'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
