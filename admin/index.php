<?php
/**
 * Admin Dashboard
 */

$pageTitle = 'Admin Dashboard';
require_once __DIR__ . '/../includes/header.php';
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

<div class="container-fluid">
    <div class="row">
        <!-- Admin Sidebar -->
        <div class="col-md-2 d-none d-md-block admin-sidebar">
            <nav class="nav flex-column">
                <a class="nav-link active" href="<?php echo BASE_URL; ?>admin/index.php">
                    <i class="fas fa-tachometer-alt"></i>Dashboard
                </a>
                <a class="nav-link" href="<?php echo BASE_URL; ?>admin/manage_products.php">
                    <i class="fas fa-box"></i>Products
                </a>
                <a class="nav-link" href="<?php echo BASE_URL; ?>admin/manage_categories.php">
                    <i class="fas fa-tags"></i>Categories
                </a>
                <a class="nav-link" href="<?php echo BASE_URL; ?>admin/manage_orders.php">
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
            <h2 class="fw-bold mb-4">Dashboard</h2>
            
            <!-- Stats Cards -->
            <div class="row g-4 mb-4">
                <div class="col-md-3 col-6">
                    <div class="stat-card">
                        <div class="icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3><?php echo number_format($totalUsers); ?></h3>
                        <p>Total Users</p>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-card">
                        <div class="icon">
                            <i class="fas fa-box"></i>
                        </div>
                        <h3><?php echo number_format($totalProducts); ?></h3>
                        <p>Total Products</p>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-card">
                        <div class="icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <h3><?php echo number_format($totalOrders); ?></h3>
                        <p>Total Orders</p>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-card">
                        <div class="icon">
                            <i class="fas fa-rupee-sign"></i>
                        </div>
                        <h3><?php echo formatCurrency($totalSales); ?></h3>
                        <p>Total Sales</p>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <!-- Sales Chart -->
                <div class="col-lg-8 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title fw-bold mb-4">Sales Overview (Last 6 Months)</h5>
                            <canvas id="salesChart" height="100"></canvas>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="col-lg-4 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title fw-bold mb-4">Quick Actions</h5>
                            <div class="d-grid gap-2">
                                <a href="<?php echo BASE_URL; ?>admin/add_product.php" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Add New Product
                                </a>
                                <a href="<?php echo BASE_URL; ?>admin/manage_orders.php" class="btn btn-outline-primary">
                                    <i class="fas fa-shopping-cart me-2"></i>View Orders
                                </a>
                                <a href="<?php echo BASE_URL; ?>admin/manage_coupons.php" class="btn btn-outline-primary">
                                    <i class="fas fa-ticket-alt me-2"></i>Manage Coupons
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Orders -->
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title fw-bold mb-4">Recent Orders</h5>
                    <div class="table-responsive">
                        <table class="table">
                            <thead class="bg-light">
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Payment</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentOrders as $order): ?>
                                <tr>
                                    <td>#<?php echo $order['id']; ?></td>
                                    <td><?php echo e($order['user_name']); ?></td>
                                    <td><?php echo formatCurrency($order['total_amount']); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $order['status']; ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo ($order['payment_status'] ?? '') === 'paid' ? 'bg-success' : 'bg-warning'; ?>">
                                            <?php echo ucfirst($order['payment_status'] ?? 'Pending'); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                    <td>
                                        <a href="<?php echo BASE_URL; ?>admin/view_order.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                
                                <?php if (empty($recentOrders)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4">No orders yet</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Sales Chart
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
        maintainAspectRatio: false,
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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
