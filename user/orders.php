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

<div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <!-- Sidebar -->
            <div class="md:col-span-1">
                <div class="bg-white rounded-2xl shadow-md p-6 sticky top-24">
                    <div class="text-center mb-6">
                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['user_name']); ?>&background=f84183&color=fff&size=128" 
                             class="w-24 h-24 rounded-full mx-auto mb-3" alt="Profile">
                        <h5 class="font-bold text-gray-900 mb-1"><?php echo e($_SESSION['user_name']); ?></h5>
                        <p class="text-gray-500 text-sm"><?php echo e($_SESSION['user_email']); ?></p>
                    </div>
                    <nav class="space-y-2">
                        <a href="<?php echo BASE_URL; ?>user/profile.php" class="flex items-center px-4 py-2 rounded-lg text-gray-600 hover:bg-gray-50 transition">
                            <i class="fas fa-user mr-3"></i>My Profile
                        </a>
                        <a href="<?php echo BASE_URL; ?>user/addresses.php" class="flex items-center px-4 py-2 rounded-lg text-gray-600 hover:bg-gray-50 transition">
                            <i class="fas fa-map-marker-alt mr-3"></i>Addresses
                        </a>
                        <a href="<?php echo BASE_URL; ?>user/orders.php" class="flex items-center px-4 py-2 rounded-lg bg-primary-50 text-primary-600 font-medium">
                            <i class="fas fa-box mr-3"></i>My Orders
                        </a>
                        <a href="<?php echo BASE_URL; ?>user/logout.php" class="flex items-center px-4 py-2 rounded-lg text-red-600 hover:bg-red-50 transition">
                            <i class="fas fa-sign-out-alt mr-3"></i>Logout
                        </a>
                    </nav>
                </div>
            </div>
            
            <!-- Orders List -->
            <div class="md:col-span-3">
                <div class="bg-white rounded-2xl shadow-md p-6 md:p-8">
                    <h4 class="text-2xl font-bold text-gray-900 mb-6">My Orders</h4>
                    
                    <?php if (empty($orders)): ?>
                    <div class="text-center py-12">
                        <i class="fas fa-box-open text-6xl text-gray-300 mb-4"></i>
                        <h5 class="text-xl font-semibold text-gray-900 mb-2">No orders yet</h5>
                        <p class="text-gray-500 mb-6">Start shopping to see your orders here!</p>
                        <a href="<?php echo BASE_URL; ?>shop.php" class="inline-flex items-center bg-primary-500 hover:bg-primary-600 text-white font-semibold py-3 px-6 rounded-full transition shadow-lg hover:shadow-xl">
                            <i class="fas fa-shopping-bag mr-2"></i>Start Shopping
                        </a>
                    </div>
                    <?php else: ?>
                    <div class="space-y-4" id="ordersAccordion">
                        <?php foreach ($orders as $index => $order): 
                            $statusColors = [
                                'pending' => 'bg-yellow-100 text-yellow-700',
                                'processing' => 'bg-blue-100 text-blue-700',
                                'shipped' => 'bg-purple-100 text-purple-700',
                                'delivered' => 'bg-green-100 text-green-700',
                                'cancelled' => 'bg-red-100 text-red-700'
                            ];
                            $statusClass = $statusColors[$order['status']] ?? 'bg-gray-100 text-gray-700';
                        ?>
                        <div class="border border-gray-200 rounded-xl overflow-hidden">
                            <button class="w-full px-6 py-4 flex justify-between items-center bg-gray-50 hover:bg-gray-100 transition text-left" 
                                    onclick="document.getElementById('order<?php echo $order['id']; ?>').classList.toggle('hidden')">
                                <div class="flex items-center space-x-4">
                                    <span class="font-bold text-gray-900">Order #<?php echo $order['id']; ?></span>
                                    <span class="text-gray-500"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></span>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <span class="px-3 py-1 rounded-full text-xs font-medium <?php echo $statusClass; ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                    <span class="font-bold text-primary-500"><?php echo formatCurrency($order['total_amount']); ?></span>
                                    <i class="fas fa-chevron-down text-gray-400"></i>
                                </div>
                            </button>
                            <div id="order<?php echo $order['id']; ?>" class="hidden px-6 py-4 border-t border-gray-200">
                                <!-- Order Details -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                    <div>
                                        <h6 class="font-bold text-gray-900 mb-2">Shipping Address</h6>
                                        <p class="text-gray-600 text-sm"><?php echo e($order['name']); ?></p>
                                        <p class="text-gray-600 text-sm"><?php echo e($order['address']); ?></p>
                                        <p class="text-gray-600 text-sm"><?php echo e($order['city']); ?>, <?php echo e($order['state']); ?> - <?php echo e($order['pincode']); ?></p>
                                        <p class="text-gray-600 text-sm">Mobile: <?php echo e($order['mobile']); ?></p>
                                    </div>
                                    <div>
                                        <h6 class="font-bold text-gray-900 mb-2">Order Information</h6>
                                        <p class="text-gray-600 text-sm">Payment Method: <?php echo ucfirst(e($order['payment_method'] ?? 'N/A')); ?></p>
                                        <p class="text-gray-600 text-sm">Payment Status: 
                                            <span class="inline-block px-2 py-1 rounded text-xs font-medium <?php echo ($order['payment_status'] ?? '') === 'paid' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'; ?>">
                                                <?php echo ucfirst($order['payment_status'] ?? 'Pending'); ?>
                                            </span>
                                        </p>
                                        <?php if ($order['coupon_code']): ?>
                                        <p class="text-gray-600 text-sm mt-1">Coupon: <span class="inline-block bg-primary-100 text-primary-700 px-2 py-1 rounded text-xs font-medium"><?php echo e($order['coupon_code']); ?></span></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <!-- Order Items -->
                                <h6 class="font-bold text-gray-900 mb-3">Order Items</h6>
                                <div class="overflow-x-auto">
                                    <table class="w-full text-sm">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-4 py-2 text-left font-medium text-gray-700">Product</th>
                                                <th class="px-4 py-2 text-center font-medium text-gray-700">Qty</th>
                                                <th class="px-4 py-2 text-right font-medium text-gray-700">Price</th>
                                                <th class="px-4 py-2 text-right font-medium text-gray-700">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200">
                                            <?php foreach ($orderItems[$order['id']] as $item): ?>
                                            <tr>
                                                <td class="px-4 py-3">
                                                    <div class="flex items-center">
                                                        <img src="<?php echo getImageUrl($item['product_image'], 'products'); ?>" 
                                                             class="w-10 h-10 rounded-lg mr-3 object-cover" alt="">
                                                        <span class="text-gray-900"><?php echo e($item['product_name']); ?></span>
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
                                                <td colspan="3" class="px-4 py-2 text-right font-bold text-gray-900">Total</td>
                                                <td class="px-4 py-2 text-right font-bold text-primary-500"><?php echo formatCurrency($order['total_amount']); ?></td>
                                            </tr>
                                        </tfoot>
                                    </table>
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
