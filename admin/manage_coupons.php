<?php
/**
 * Admin - Manage Coupons
 */

$pageTitle = 'Manage Coupons';
require_once __DIR__ . '/../includes/header.php';
requireAdmin();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['form_action'] ?? '';
    
    if ($action === 'add' || $action === 'edit') {
        $code = isset($_POST['code']) ? strtoupper(trim($_POST['code'])) : '';
        $type = isset($_POST['type']) ? $_POST['type'] : 'percent';
        $value = isset($_POST['value']) ? (float)$_POST['value'] : 0;
        $minOrder = isset($_POST['min_order']) ? (float)$_POST['min_order'] : 0;
        $maxDiscount = isset($_POST['max_discount']) ? (float)$_POST['max_discount'] : 0;
        $expiryDate = isset($_POST['expiry_date']) ? $_POST['expiry_date'] : '';
        $usageLimit = isset($_POST['usage_limit']) ? (int)$_POST['usage_limit'] : 0;
        $status = isset($_POST['status']) ? 1 : 0;
        $couponId = isset($_POST['coupon_id']) ? (int)$_POST['coupon_id'] : 0;
        
        // Validation
        if (empty($code)) $errors[] = 'Coupon code is required';
        if ($value <= 0) $errors[] = 'Discount value must be greater than 0';
        if (empty($expiryDate)) $errors[] = 'Expiry date is required';
        
        if (empty($errors)) {
            try {
                if ($action === 'add') {
                    $stmt = $pdo->prepare("INSERT INTO coupons (code, type, value, min_order, max_discount, expiry_date, usage_limit, status, created_at) 
                                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                    $stmt->execute([$code, $type, $value, $minOrder, $maxDiscount, $expiryDate, $usageLimit, $status]);
                    setFlash('Coupon added successfully!', 'success');
                } else {
                    $stmt = $pdo->prepare("UPDATE coupons SET code = ?, type = ?, value = ?, min_order = ?, max_discount = ?, expiry_date = ?, usage_limit = ?, status = ? WHERE id = ?");
                    $stmt->execute([$code, $type, $value, $minOrder, $maxDiscount, $expiryDate, $usageLimit, $status, $couponId]);
                    setFlash('Coupon updated successfully!', 'success');
                }
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    setFlash('Coupon code already exists', 'danger');
                } else {
                    setFlash('Error saving coupon', 'danger');
                }
            }
        }
        
        redirect(BASE_URL . 'admin/manage_coupons.php');
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $couponId = (int)$_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM coupons WHERE id = ?");
        $stmt->execute([$couponId]);
        setFlash('Coupon deleted successfully', 'success');
    } catch (PDOException $e) {
        setFlash('Error deleting coupon', 'danger');
    }
    redirect(BASE_URL . 'admin/manage_coupons.php');
}

// Handle toggle status
if (isset($_GET['toggle'])) {
    $couponId = (int)$_GET['toggle'];
    try {
        $stmt = $pdo->prepare("UPDATE coupons SET status = NOT status WHERE id = ?");
        $stmt->execute([$couponId]);
        setFlash('Coupon status updated', 'success');
    } catch (PDOException $e) {
        setFlash('Error updating coupon status', 'danger');
    }
    redirect(BASE_URL . 'admin/manage_coupons.php');
}

// Fetch coupon for edit
$editCoupon = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    try {
        $stmt = $pdo->prepare("SELECT * FROM coupons WHERE id = ?");
        $stmt->execute([$editId]);
        $editCoupon = $stmt->fetch();
    } catch (PDOException $e) {
        // Silent fail
    }
}

// Fetch all coupons
try {
    $stmt = $pdo->query("SELECT * FROM coupons ORDER BY created_at DESC");
    $coupons = $stmt->fetchAll();
} catch (PDOException $e) {
    $coupons = [];
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
                <a href="<?php echo BASE_URL; ?>admin/manage_orders.php" class="flex items-center px-4 py-3 text-gray-300 hover:bg-gray-800 rounded-lg transition">
                    <i class="fas fa-shopping-cart w-6"></i>Orders
                </a>
                <a href="<?php echo BASE_URL; ?>admin/manage_coupons.php" class="flex items-center px-4 py-3 bg-primary-500 rounded-lg text-white">
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
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Manage Coupons</h2>
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Coupon Form -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <h5 class="font-bold text-gray-900 mb-4">
                            <?php echo $editCoupon ? 'Edit Coupon' : 'Add New Coupon'; ?>
                        </h5>
                        
                        <form action="<?php echo BASE_URL; ?>admin/manage_coupons.php" method="POST" class="space-y-4">
                            <input type="hidden" name="form_action" value="<?php echo $editCoupon ? 'edit' : 'add'; ?>">
                            <?php if ($editCoupon): ?>
                            <input type="hidden" name="coupon_id" value="<?php echo $editCoupon['id']; ?>">
                            <?php endif; ?>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Coupon Code *</label>
                                <input type="text" name="code" required maxlength="50"
                                       value="<?php echo $editCoupon ? e($editCoupon['code']) : ''; ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition uppercase">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Discount Type *</label>
                                <select name="type" required
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                                    <option value="percent" <?php echo ($editCoupon && $editCoupon['type'] === 'percent') ? 'selected' : ''; ?>>Percentage (%)</option>
                                    <option value="fixed" <?php echo ($editCoupon && $editCoupon['type'] === 'fixed') ? 'selected' : ''; ?>>Fixed Amount (₹)</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Discount Value *</label>
                                <input type="number" name="value" step="0.01" min="0" required
                                       value="<?php echo $editCoupon ? $editCoupon['value'] : ''; ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Minimum Order Amount</label>
                                <input type="number" name="min_order" step="0.01" min="0"
                                       value="<?php echo $editCoupon ? $editCoupon['min_order'] : ''; ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                                <p class="text-xs text-gray-500 mt-1">Leave 0 for no minimum</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Maximum Discount (for % type)</label>
                                <input type="number" name="max_discount" step="0.01" min="0"
                                       value="<?php echo $editCoupon ? $editCoupon['max_discount'] : ''; ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                                <p class="text-xs text-gray-500 mt-1">Leave 0 for no limit</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Expiry Date *</label>
                                <input type="date" name="expiry_date" required
                                       value="<?php echo $editCoupon ? $editCoupon['expiry_date'] : ''; ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Usage Limit</label>
                                <input type="number" name="usage_limit" min="0"
                                       value="<?php echo $editCoupon ? $editCoupon['usage_limit'] : ''; ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                                <p class="text-xs text-gray-500 mt-1">Leave 0 for unlimited</p>
                            </div>
                            
                            <div class="flex items-center">
                                <input type="checkbox" name="status" value="1" 
                                       <?php echo (!$editCoupon || $editCoupon['status']) ? 'checked' : ''; ?>
                                       class="w-4 h-4 text-primary-500 border-gray-300 rounded focus:ring-primary-500">
                                <label class="ml-2 text-sm text-gray-600">Active</label>
                            </div>
                            
                            <button type="submit" class="w-full bg-primary-500 hover:bg-primary-600 text-white font-semibold py-3 rounded-full transition shadow-lg hover:shadow-xl">
                                <i class="fas fa-save mr-2"></i>
                                <?php echo $editCoupon ? 'Update Coupon' : 'Add Coupon'; ?>
                            </button>
                            
                            <?php if ($editCoupon): ?>
                            <a href="<?php echo BASE_URL; ?>admin/manage_coupons.php" class="block w-full text-center border-2 border-gray-300 hover:border-gray-400 text-gray-700 font-medium py-3 rounded-full transition">
                                Cancel
                            </a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
                
                <!-- Coupons List -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <h5 class="font-bold text-gray-900 mb-4">Coupons List</h5>
                        
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Code</th>
                                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Type</th>
                                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Value</th>
                                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Used</th>
                                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Expiry</th>
                                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Status</th>
                                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <?php foreach ($coupons as $coupon): ?>
                                    <tr>
                                        <td class="px-4 py-3 font-bold text-gray-900"><?php echo e($coupon['code']); ?></td>
                                        <td class="px-4 py-3 text-sm"><?php echo $coupon['type'] === 'percent' ? 'Percentage' : 'Fixed'; ?></td>
                                        <td class="px-4 py-3 text-sm">
                                            <?php echo $coupon['type'] === 'percent' ? $coupon['value'] . '%' : '₹' . $coupon['value']; ?>
                                            <?php if ($coupon['max_discount'] > 0 && $coupon['type'] === 'percent'): ?>
                                            <div class="text-xs text-gray-500">Max: ₹<?php echo $coupon['max_discount']; ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-4 py-3 text-sm"><?php echo $coupon['used_count']; ?> / <?php echo $coupon['usage_limit'] > 0 ? $coupon['usage_limit'] : '∞'; ?></td>
                                        <td class="px-4 py-3 text-sm">
                                            <?php echo date('M d, Y', strtotime($coupon['expiry_date'])); ?>
                                            <?php if (strtotime($coupon['expiry_date']) < time()): ?>
                                            <div class="inline-block bg-red-100 text-red-700 px-2 py-1 rounded text-xs font-medium mt-1">Expired</div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-4 py-3">
                                            <a href="<?php echo BASE_URL; ?>admin/manage_coupons.php?toggle=<?php echo $coupon['id']; ?>" 
                                               class="inline-block px-2 py-1 rounded text-xs font-medium <?php echo $coupon['status'] ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                                                <?php echo $coupon['status'] ? 'Active' : 'Inactive'; ?>
                                            </a>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex gap-2">
                                                <a href="<?php echo BASE_URL; ?>admin/manage_coupons.php?edit=<?php echo $coupon['id']; ?>" 
                                                   class="inline-flex items-center border-2 border-primary-500 text-primary-500 hover:bg-primary-500 hover:text-white font-medium py-1 px-2 rounded-lg transition text-sm">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="<?php echo BASE_URL; ?>admin/manage_coupons.php?delete=<?php echo $coupon['id']; ?>" 
                                                   class="inline-flex items-center border-2 border-red-500 text-red-500 hover:bg-red-500 hover:text-white font-medium py-1 px-2 rounded-lg transition text-sm"
                                                   onclick="return confirm('Delete this coupon?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    
                                    <?php if (empty($coupons)): ?>
                                    <tr>
                                        <td colspan="7" class="px-4 py-8 text-center text-gray-500">No coupons found</td>
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
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
