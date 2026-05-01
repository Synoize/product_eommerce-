<?php
/**
 * Admin - Manage Products
 */

$pageTitle = 'Manage Products';
require_once __DIR__ . '/../includes/header.php';
requireAdmin();

// Handle delete action
if (isset($_GET['delete'])) {
    $productId = (int)$_GET['delete'];
    try {
        // Check if product exists in orders
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM order_items WHERE product_id = ?");
        $stmt->execute([$productId]);
        $inOrders = $stmt->fetch()['count'] > 0;
        
        if ($inOrders) {
            // Soft delete - just disable
            $stmt = $pdo->prepare("UPDATE products SET status = 0 WHERE id = ?");
            $stmt->execute([$productId]);
            setFlash('Product has been disabled (cannot delete - exists in orders)', 'warning');
        } else {
            // Hard delete
            $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$productId]);
            setFlash('Product deleted successfully', 'success');
        }
    } catch (PDOException $e) {
        setFlash('Error deleting product', 'danger');
    }
    redirect(BASE_URL . 'admin/manage_products.php');
}

// Fetch all products
try {
    $stmt = $pdo->query("SELECT p.*, c.name as category_name 
                        FROM products p 
                        LEFT JOIN categories c ON p.category_id = c.id 
                        ORDER BY p.created_at DESC");
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    $products = [];
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
                <a class="nav-link active" href="<?php echo BASE_URL; ?>admin/manage_products.php">
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
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold mb-0">Manage Products</h2>
                <a href="<?php echo BASE_URL; ?>admin/add_product.php" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Add New Product
                </a>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="bg-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Image</th>
                                    <th>Name</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $product): ?>
                                <tr>
                                    <td><?php echo $product['id']; ?></td>
                                    <td>
                                        <?php 
                                        $imageUrl = getImageUrl($product['image'], 'products');
                                        ?>
                                        <img src="<?php echo $imageUrl; ?>" width="50" height="50" class="rounded" alt="">
                                    </td>
                                    <td><?php echo e($product['name']); ?></td>
                                    <td><?php echo e($product['category_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo formatCurrency($product['price']); ?></td>
                                    <td><?php echo $product['stock']; ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $product['status'] ? 'success' : 'danger'; ?>">
                                            <?php echo $product['status'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="<?php echo BASE_URL; ?>admin/edit_product.php?id=<?php echo $product['id']; ?>" 
                                           class="btn btn-sm btn-outline-primary me-1" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?php echo BASE_URL; ?>product.php?id=<?php echo $product['id']; ?>" 
                                           class="btn btn-sm btn-outline-info me-1" title="View" target="_blank">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?php echo BASE_URL; ?>admin/manage_products.php?delete=<?php echo $product['id']; ?>" 
                                           class="btn btn-sm btn-outline-danger" title="Delete"
                                           onclick="return confirm('Are you sure you want to delete this product?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                
                                <?php if (empty($products)): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4">No products found. <a href="<?php echo BASE_URL; ?>admin/add_product.php">Add your first product</a></td>
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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
