<?php

/**
 * Admin Dashboard
 */

$pageTitle = 'Admin Dashboard';
require_once __DIR__ . '/includes/header.php';
requireAdmin();

// Get statistics
try {
    // Total users
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'user'");
    $totalUsers = $stmt->fetch()['total'];

    // Total products
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM products");
    $totalProducts = $stmt->fetch()['total'];

    // Total orders
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM orders");
    $totalOrders = $stmt->fetch()['total'];

    // Total sales
    $stmt = $pdo->query("SELECT SUM(total_amount) as total FROM orders WHERE payment_status = 'paid'");
    $totalSales = $stmt->fetch()['total'] ?? 0;

    // Recent orders
    $stmt = $pdo->query("SELECT o.*, u.name as user_name 
                        FROM orders o 
                        JOIN users u ON o.user_id = u.id 
                        ORDER BY o.created_at DESC 
                        LIMIT 10");
    $recentOrders = $stmt->fetchAll();

    // Monthly sales data for chart
    $stmt = $pdo->query("SELECT 
                            DATE_FORMAT(created_at, '%Y-%m') as month,
                            SUM(total_amount) as total 
                        FROM orders 
                        WHERE payment_status = 'paid' 
                        AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                        ORDER BY month ASC");
    $monthlySales = $stmt->fetchAll();
} catch (PDOException $e) {
    $totalUsers = $totalProducts = $totalOrders = $totalSales = 0;
    $recentOrders = [];
    $monthlySales = [];
}

// Prepare chart data
$chartLabels = [];
$chartData = [];
foreach ($monthlySales as $sale) {
    $chartLabels[] = date('M Y', strtotime($sale['month'] . '-01'));
    $chartData[] = (float)$sale['total'];
}
?>

<div class="bg-white mt-20">
    <div class="h-[calc(100vh-80px)] flex">
        <!-- Admin Sidebar -->
        <?php include __DIR__ . '/includes/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="flex-1 overflow-y-auto p-6 md:p-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Dashboard</h2>

            <!-- Stats Cards -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6 mb-8">
                <div class="bg-white rounded-xl shadow-md p-6">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
                        <i class="fas fa-users text-xl text-blue-600"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900"><?php echo number_format($totalUsers); ?></h3>
                    <p class="text-gray-500 text-sm">Total Users</p>
                </div>
                <div class="bg-white rounded-xl shadow-md p-6">
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mb-4">
                        <i class="fas fa-box text-xl text-green-600"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900"><?php echo number_format($totalProducts); ?></h3>
                    <p class="text-gray-500 text-sm">Total Products</p>
                </div>
                <div class="bg-white rounded-xl shadow-md p-6">
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mb-4">
                        <i class="fas fa-shopping-cart text-xl text-purple-600"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900"><?php echo number_format($totalOrders); ?></h3>
                    <p class="text-gray-500 text-sm">Total Orders</p>
                </div>
                <div class="bg-white rounded-xl shadow-md p-6">
                    <div class="w-12 h-12 bg-pink-100 rounded-lg flex items-center justify-center mb-4">
                        <i class="fas fa-rupee-sign text-xl text-pink-600"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900"><?php echo formatCurrency($totalSales); ?></h3>
                    <p class="text-gray-500 text-sm">Total Sales</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <!-- Sales Chart -->
                <div class="lg:col-span-2 bg-white rounded-xl shadow-md p-6 h-[350px]">
                    <h5 class="font-bold text-gray-900 mb-4">
                        Sales Overview (Last 6 Months)
                    </h5>

                    <div class="h-[280px]">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h5 class="font-bold text-gray-900 mb-4">Quick Actions</h5>
                    <div class="space-y-3">
                        <a href="<?php echo BASE_URL; ?>admin/add_product.php" class="flex items-center justify-center w-full bg-primary-500 hover:bg-primary-600 text-white font-semibold py-3 px-4 rounded-lg transition">
                            <i class="fas fa-plus mr-2"></i>Add New Product
                        </a>
                        <a href="<?php echo BASE_URL; ?>admin/manage_orders.php" class="flex items-center justify-center w-full border-2 border-primary-500 text-primary-500 hover:bg-primary-500 hover:text-white font-semibold py-3 px-4 rounded-lg transition">
                            <i class="fas fa-shopping-cart mr-2"></i>View Orders
                        </a>
                        <a href="<?php echo BASE_URL; ?>admin/manage_coupons.php" class="flex items-center justify-center w-full border-2 border-primary-500 text-primary-500 hover:bg-primary-500 hover:text-white font-semibold py-3 px-4 rounded-lg transition">
                            <i class="fas fa-ticket-alt mr-2"></i>Manage Coupons
                        </a>
                    </div>
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h5 class="font-bold text-gray-900 mb-4">Recent Orders</h5>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Order ID</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Customer</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Total</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Status</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Payment</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Date</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($recentOrders as $order):
                                $statusColors = [
                                    'pending' => 'bg-yellow-100 text-yellow-700',
                                    'processing' => 'bg-blue-100 text-blue-700',
                                    'shipped' => 'bg-purple-100 text-purple-700',
                                    'delivered' => 'bg-green-100 text-green-700',
                                    'cancelled' => 'bg-red-100 text-red-700'
                                ];
                                $statusClass = $statusColors[$order['status']] ?? 'bg-gray-100 text-gray-700';
                            ?>
                                <tr>
                                    <td class="px-4 py-3 text-sm">#<?php echo $order['id']; ?></td>
                                    <td class="px-4 py-3 text-sm"><?php echo e($order['user_name']); ?></td>
                                    <td class="px-4 py-3 text-sm font-medium"><?php echo formatCurrency($order['total_amount']); ?></td>
                                    <td class="px-4 py-3">
                                        <span class="inline-block px-2 py-1 rounded text-xs font-medium <?php echo $statusClass; ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="inline-block px-2 py-1 rounded text-xs font-medium <?php echo ($order['payment_status'] ?? '') === 'paid' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'; ?>">
                                            <?php echo ucfirst($order['payment_status'] ?? 'Pending'); ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-500"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                    <td class="px-4 py-3">
                                        <a href="<?php echo BASE_URL; ?>admin/view_order.php?id=<?php echo $order['id']; ?>" class="inline-flex items-center border-2 border-primary-500 text-primary-500 hover:bg-primary-500 hover:text-white font-medium py-1 px-3 rounded-lg transition text-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>

                            <?php if (empty($recentOrders)): ?>
                                <tr>
                                    <td colspan="7" class="px-4 py-8 text-center text-gray-500">No orders yet</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('salesChart').getContext('2d');

    const salesChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($chartLabels); ?>,
            datasets: [{
                label: 'Sales (₹)',
                data: <?php echo json_encode($chartData); ?>,
                borderColor: '#f84183',
                backgroundColor: 'rgba(248, 65, 131, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false, // IMPORTANT
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₹' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
</script>