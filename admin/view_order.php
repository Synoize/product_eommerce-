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

<div class="min-h-screen bg-gray-100">
    <div class="flex">
        <!-- Admin Sidebar -->
        <div class="hidden md:flex flex-col w-64 bg-gray-900 text-white min-h-screen">
            <div class="p-6">
                <h3 class="text-xl font-bold">Admin Panel</h3>
            </div>
            <nav class="flex-1 px-4 space-y-2">
                <a href="<?php echo BASE_URL; ?>admin/index.php" class="flex items-center px-4 py-3 text-gray-300 hover:bg-gray-800 rounded-lg transition">
                    <i class="fas fa-tachometer-alt w-6"></i>Dashboard
                </a>
                <a href="<?php echo BASE_URL; ?>admin/manage_products.php" class="flex items-center px-4 py-3 text-gray-300 hover:bg-gray-800 rounded-lg transition">
                    <i class="fas fa-box w-6"></i>Products
                </a>
                <a href="<?php echo BASE_URL; ?>admin/manage_categories.php" class="flex items-center px-4 py-3 text-gray-300 hover:bg-gray-800 rounded-lg transition">
                    <i class="fas fa-tags w-6"></i>Categories
                </a>
                <a href="<?php echo BASE_URL; ?>admin/manage_orders.php" class="flex items-center px-4 py-3 bg-primary-500 rounded-lg text-white">
                    <i class="fas fa-shopping-cart w-6"></i>Orders
                </a>
                <a href="<?php echo BASE_URL; ?>admin/manage_coupons.php" class="flex items-center px-4 py-3 text-gray-300 hover:bg-gray-800 rounded-lg transition">
                    <i class="fas fa-ticket-alt w-6"></i>Coupons
                </a>
                <a href="<?php echo BASE_URL; ?>admin/manage_users.php" class="flex items-center px-4 py-3 text-gray-300 hover:bg-gray-800 rounded-lg transition">
                    <i class="fas fa-users w-6"></i>Users
                </a>
                <a href="<?php echo BASE_URL; ?>admin/contact_messages.php" class="flex items-center px-4 py-3 text-gray-300 hover:bg-gray-800 rounded-lg transition">
                    <i class="fas fa-envelope w-6"></i>Messages
                </a>
            </nav>
            <div class="p-4">
                <a href="<?php echo BASE_URL; ?>" class="flex items-center px-4 py-3 text-gray-300 hover:bg-gray-800 rounded-lg transition">
                    <i class="fas fa-arrow-left w-6"></i>Back to Site
                </a>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="flex-1 p-6 md:p-8">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                <h2 class="text-2xl font-bold text-gray-900">Order #<?php echo $orderId; ?></h2>
                <a href="<?php echo BASE_URL; ?>admin/manage_orders.php" class="inline-flex items-center border-2 border-gray-300 hover:border-gray-400 text-gray-700 font-medium py-2 px-4 rounded-lg transition">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Orders
                </a>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Order Info -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Order Status -->
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <h5 class="font-bold text-gray-900 mb-4">Order Status</h5>
                        <?php
                        $statusColors = [
                            'pending' => 'bg-yellow-100 text-yellow-700',
                            'processing' => 'bg-blue-100 text-blue-700',
                            'shipped' => 'bg-purple-100 text-purple-700',
                            'delivered' => 'bg-green-100 text-green-700',
                            'cancelled' => 'bg-red-100 text-red-700'
                        ];
                        $currentStatusClass = $statusColors[$order['status']] ?? 'bg-gray-100 text-gray-700';
                        ?>
                        <form action="<?php echo BASE_URL; ?>admin/view_order.php?id=<?php echo $orderId; ?>" method="POST" class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                            <span class="inline-block px-3 py-1 rounded-lg text-sm font-medium <?php echo $currentStatusClass; ?>">
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                            <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                <option value="shipped" <?php echo $order['status'] === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                <option value="delivered" <?php echo $order['status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                            <button type="submit" name="update_status" class="inline-flex items-center bg-primary-500 hover:bg-primary-600 text-white font-semibold py-2 px-4 rounded-lg transition">
                                <i class="fas fa-sync mr-2"></i>Update
                            </button>
                        </form>
                    </div>
                    
                    <!-- Order Items -->
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <h5 class="font-bold text-gray-900 mb-4">Order Items</h5>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Product</th>
                                        <th class="px-4 py-2 text-center text-sm font-medium text-gray-700">Qty</th>
                                        <th class="px-4 py-2 text-right text-sm font-medium text-gray-700">Price</th>
                                        <th class="px-4 py-2 text-right text-sm font-medium text-gray-700">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <?php foreach ($orderItems as $item): ?>
                                    <tr>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center">
                                                <img src="<?php echo getImageUrl($item['product_image'], 'products'); ?>" 
                                                     class="w-12 h-12 rounded-lg mr-3 object-cover" alt="">
                                                <span class="text-sm"><?php echo e($item['product_name']); ?></span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-center"><?php echo $item['quantity']; ?></td>
                                        <td class="px-4 py-3 text-right"><?php echo formatCurrency($item['price']); ?></td>
                                        <td class="px-4 py-3 text-right font-medium"><?php echo formatCurrency($item['price'] * $item['quantity']); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot class="bg-gray-50 font-medium">
                                    <tr>
                                        <td colspan="3" class="px-4 py-2 text-right">Subtotal</td>
                                        <td class="px-4 py-2 text-right"><?php echo formatCurrency($order['total_amount'] + $order['discount_amount']); ?></td>
                                    </tr>
                                    <?php if ($order['discount_amount'] > 0): ?>
                                    <tr>
                                        <td colspan="3" class="px-4 py-2 text-right">Discount</td>
                                        <td class="px-4 py-2 text-right text-green-600">-<?php echo formatCurrency($order['discount_amount']); ?></td>
                                    </tr>
                                    <?php endif; ?>
                                    <tr>
                                        <td colspan="3" class="px-4 py-2 text-right font-bold">Total</td>
                                        <td class="px-4 py-2 text-right font-bold text-primary-500"><?php echo formatCurrency($order['total_amount']); ?></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Shipping Address -->
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <h5 class="font-bold text-gray-900 mb-4">Shipping Information</h5>
                        <p class="font-medium text-gray-900 mb-1"><?php echo e($order['name']); ?></p>
                        <p class="text-gray-600 text-sm mb-1"><?php echo e($order['address']); ?></p>
                        <p class="text-gray-600 text-sm mb-1"><?php echo e($order['city']); ?>, <?php echo e($order['state']); ?> - <?php echo e($order['pincode']); ?></p>
                        <p class="text-gray-600 text-sm mb-1">Email: <?php echo e($order['email']); ?></p>
                        <p class="text-gray-600 text-sm">Mobile: <?php echo e($order['mobile']); ?></p>
                    </div>
                </div>
                
                <!-- Order Summary -->
                <div class="space-y-6">
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <h5 class="font-bold text-gray-900 mb-4">Order Summary</h5>
                        <p class="text-sm mb-2"><span class="font-medium">Order Date:</span> <?php echo date('F d, Y H:i', strtotime($order['created_at'])); ?></p>
                        <p class="text-sm mb-4"><span class="font-medium">Order ID:</span> #<?php echo $order['id']; ?></p>
                        
                        <div class="border-t border-gray-200 pt-4 mb-4">
                            <h6 class="font-bold text-gray-900 mb-2">Payment Information</h6>
                            <p class="text-sm mb-1"><span class="font-medium">Method:</span> <?php echo ucfirst(e($order['payment_method'] ?? 'N/A')); ?></p>
                            <p class="text-sm mb-1"><span class="font-medium">Status:</span> 
                                <span class="inline-block px-2 py-1 rounded text-xs font-medium <?php echo ($order['payment_status'] ?? '') === 'paid' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'; ?>">
                                    <?php echo ucfirst($order['payment_status'] ?? 'Pending'); ?>
                                </span>
                            </p>
                            <?php if ($order['razorpay_payment_id']): ?>
                            <p class="text-sm mb-1"><span class="font-medium">Payment ID:</span> <span class="text-gray-500 text-xs"><?php echo e($order['razorpay_payment_id']); ?></span></p>
                            <?php endif; ?>
                            <?php if ($order['razorpay_order_id']): ?>
                            <p class="text-sm"><span class="font-medium">Order ID:</span> <span class="text-gray-500 text-xs"><?php echo e($order['razorpay_order_id']); ?></span></p>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($order['coupon_code']): ?>
                        <div class="border-t border-gray-200 pt-4">
                            <h6 class="font-bold text-gray-900 mb-2">Coupon Applied</h6>
                            <span class="inline-block bg-primary-100 text-primary-700 px-2 py-1 rounded text-xs font-medium"><?php echo e($order['coupon_code']); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Customer Info -->
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <h5 class="font-bold text-gray-900 mb-4">Customer Information</h5>
                        <p class="text-sm mb-1"><span class="font-medium">Name:</span> <?php echo e($order['user_name']); ?></p>
                        <p class="text-sm mb-1"><span class="font-medium">Email:</span> <?php echo e($order['user_email']); ?></p>
                        <p class="text-sm"><span class="font-medium">Mobile:</span> <?php echo e($order['user_mobile'] ?? 'N/A'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
