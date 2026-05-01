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
                <a class="nav-link" href="<?php echo BASE_URL; ?>admin/manage_orders.php">
                    <i class="fas fa-shopping-cart"></i>Orders
                </a>
                <a class="nav-link active" href="<?php echo BASE_URL; ?>admin/manage_coupons.php">
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
            <h2 class="fw-bold mb-4">Manage Coupons</h2>
            
            <div class="row">
                <!-- Coupon Form -->
                <div class="col-lg-4 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title fw-bold mb-4">
                                <?php echo $editCoupon ? 'Edit Coupon' : 'Add New Coupon'; ?>
                            </h5>
                            
                            <form action="<?php echo BASE_URL; ?>admin/manage_coupons.php" method="POST">
                                <input type="hidden" name="form_action" value="<?php echo $editCoupon ? 'edit' : 'add'; ?>">
                                <?php if ($editCoupon): ?>
                                <input type="hidden" name="coupon_id" value="<?php echo $editCoupon['id']; ?>">
                                <?php endif; ?>
                                
                                <div class="mb-3">
                                    <label class="form-label">Coupon Code *</label>
                                    <input type="text" name="code" class="form-control text-uppercase" required maxlength="50"
                                           value="<?php echo $editCoupon ? e($editCoupon['code']) : ''; ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Discount Type *</label>
                                    <select name="type" class="form-select" required>
                                        <option value="percent" <?php echo ($editCoupon && $editCoupon['type'] === 'percent') ? 'selected' : ''; ?>>Percentage (%)</option>
                                        <option value="fixed" <?php echo ($editCoupon && $editCoupon['type'] === 'fixed') ? 'selected' : ''; ?>>Fixed Amount (₹)</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Discount Value *</label>
                                    <input type="number" name="value" class="form-control" step="0.01" min="0" required
                                           value="<?php echo $editCoupon ? $editCoupon['value'] : ''; ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Minimum Order Amount</label>
                                    <input type="number" name="min_order" class="form-control" step="0.01" min="0"
                                           value="<?php echo $editCoupon ? $editCoupon['min_order'] : ''; ?>">
                                    <small class="text-muted">Leave 0 for no minimum</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Maximum Discount (for % type)</label>
                                    <input type="number" name="max_discount" class="form-control" step="0.01" min="0"
                                           value="<?php echo $editCoupon ? $editCoupon['max_discount'] : ''; ?>">
                                    <small class="text-muted">Leave 0 for no limit</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Expiry Date *</label>
                                    <input type="date" name="expiry_date" class="form-control" required
                                           value="<?php echo $editCoupon ? $editCoupon['expiry_date'] : ''; ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Usage Limit</label>
                                    <input type="number" name="usage_limit" class="form-control" min="0"
                                           value="<?php echo $editCoupon ? $editCoupon['usage_limit'] : ''; ?>">
                                    <small class="text-muted">Leave 0 for unlimited</small>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="status" value="1" 
                                               <?php echo (!$editCoupon || $editCoupon['status']) ? 'checked' : ''; ?>>
                                        <label class="form-check-label">Active</label>
                                    </div>
                                </div>
                                
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-save me-2"></i>
                                    <?php echo $editCoupon ? 'Update Coupon' : 'Add Coupon'; ?>
                                </button>
                                
                                <?php if ($editCoupon): ?>
                                <a href="<?php echo BASE_URL; ?>admin/manage_coupons.php" class="btn btn-outline-secondary w-100 mt-2">
                                    Cancel
                                </a>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Coupons List -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title fw-bold mb-4">Coupons List</h5>
                            
                            <div class="table-responsive">
                                <table class="table">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Code</th>
                                            <th>Type</th>
                                            <th>Value</th>
                                            <th>Used</th>
                                            <th>Expiry</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($coupons as $coupon): ?>
                                        <tr>
                                            <td><span class="fw-bold"><?php echo e($coupon['code']); ?></span></td>
                                            <td><?php echo $coupon['type'] === 'percent' ? 'Percentage' : 'Fixed'; ?></td>
                                            <td>
                                                <?php echo $coupon['type'] === 'percent' ? $coupon['value'] . '%' : '₹' . $coupon['value']; ?>
                                                <?php if ($coupon['max_discount'] > 0 && $coupon['type'] === 'percent'): ?>
                                                <br><small class="text-muted">Max: ₹<?php echo $coupon['max_discount']; ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo $coupon['used_count']; ?> / <?php echo $coupon['usage_limit'] > 0 ? $coupon['usage_limit'] : '∞'; ?></td>
                                            <td>
                                                <?php echo date('M d, Y', strtotime($coupon['expiry_date'])); ?>
                                                <?php if (strtotime($coupon['expiry_date']) < time()): ?>
                                                <br><span class="badge bg-danger">Expired</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="<?php echo BASE_URL; ?>admin/manage_coupons.php?toggle=<?php echo $coupon['id']; ?>" 
                                                   class="badge bg-<?php echo $coupon['status'] ? 'success' : 'danger'; ?>">
                                                    <?php echo $coupon['status'] ? 'Active' : 'Inactive'; ?>
                                                </a>
                                            </td>
                                            <td>
                                                <a href="<?php echo BASE_URL; ?>admin/manage_coupons.php?edit=<?php echo $coupon['id']; ?>" 
                                                   class="btn btn-sm btn-outline-primary me-1">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="<?php echo BASE_URL; ?>admin/manage_coupons.php?delete=<?php echo $coupon['id']; ?>" 
                                                   class="btn btn-sm btn-outline-danger"
                                                   onclick="return confirm('Delete this coupon?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                        
                                        <?php if (empty($coupons)): ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-4">No coupons found</td>
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
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
