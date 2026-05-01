<?php
/**
 * Admin - Manage Categories
 */

$pageTitle = 'Manage Categories';
require_once __DIR__ . '/../includes/header.php';
requireAdmin();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['form_action'] ?? '';
    
    if ($action === 'add' || $action === 'edit') {
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        $categoryId = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
        
        if (empty($name)) {
            setFlash('Category name is required', 'danger');
        } else {
            // Handle image upload
            $imageName = isset($_POST['existing_image']) ? $_POST['existing_image'] : '';
            
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = CATEGORIES_PATH;
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $fileName = time() . '_' . basename($_FILES['image']['name']);
                $targetPath = $uploadDir . $fileName;
                
                $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
                
                if (!in_array($_FILES['image']['type'], $allowedTypes)) {
                    setFlash('Only JPG, PNG, and WEBP images are allowed', 'danger');
                } elseif ($_FILES['image']['size'] > 2 * 1024 * 1024) {
                    setFlash('Image size must be less than 2MB', 'danger');
                } elseif (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                    // Delete old image
                    if ($imageName && file_exists($uploadDir . $imageName)) {
                        unlink($uploadDir . $imageName);
                    }
                    $imageName = $fileName;
                }
            }
            
            try {
                if ($action === 'add') {
                    $stmt = $pdo->prepare("INSERT INTO categories (name, image, created_at) VALUES (?, ?, NOW())");
                    $stmt->execute([$name, $imageName]);
                    setFlash('Category added successfully!', 'success');
                } else {
                    $stmt = $pdo->prepare("UPDATE categories SET name = ?, image = ? WHERE id = ?");
                    $stmt->execute([$name, $imageName, $categoryId]);
                    setFlash('Category updated successfully!', 'success');
                }
            } catch (PDOException $e) {
                setFlash('Error saving category', 'danger');
            }
        }
    }
    
    redirect(BASE_URL . 'admin/manage_categories.php');
}

// Handle delete
if (isset($_GET['delete'])) {
    $categoryId = (int)$_GET['delete'];
    
    try {
        // Check if category has products
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM products WHERE category_id = ?");
        $stmt->execute([$categoryId]);
        $hasProducts = $stmt->fetch()['count'] > 0;
        
        if ($hasProducts) {
            setFlash('Cannot delete category - it contains products', 'danger');
        } else {
            // Get image to delete
            $stmt = $pdo->prepare("SELECT image FROM categories WHERE id = ?");
            $stmt->execute([$categoryId]);
            $category = $stmt->fetch();
            
            if ($category && $category['image']) {
                $imagePath = CATEGORIES_PATH . $category['image'];
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
            
            $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
            $stmt->execute([$categoryId]);
            setFlash('Category deleted successfully', 'success');
        }
    } catch (PDOException $e) {
        setFlash('Error deleting category', 'danger');
    }
    
    redirect(BASE_URL . 'admin/manage_categories.php');
}

// Fetch category for edit
$editCategory = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    try {
        $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->execute([$editId]);
        $editCategory = $stmt->fetch();
    } catch (PDOException $e) {
        // Silent fail
    }
}

// Fetch all categories
try {
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    $categories = [];
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
                <a class="nav-link active" href="<?php echo BASE_URL; ?>admin/manage_categories.php">
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
            <h2 class="fw-bold mb-4">Manage Categories</h2>
            
            <div class="row">
                <!-- Category Form -->
                <div class="col-lg-4 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title fw-bold mb-4">
                                <?php echo $editCategory ? 'Edit Category' : 'Add New Category'; ?>
                            </h5>
                            
                            <form action="<?php echo BASE_URL; ?>admin/manage_categories.php" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="form_action" value="<?php echo $editCategory ? 'edit' : 'add'; ?>">
                                <?php if ($editCategory): ?>
                                <input type="hidden" name="category_id" value="<?php echo $editCategory['id']; ?>">
                                <input type="hidden" name="existing_image" value="<?php echo $editCategory['image']; ?>">
                                <?php endif; ?>
                                
                                <div class="mb-3">
                                    <label class="form-label">Category Name *</label>
                                    <input type="text" name="name" class="form-control" required
                                           value="<?php echo $editCategory ? e($editCategory['name']) : ''; ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Category Image</label>
                                    <?php if ($editCategory && $editCategory['image']): ?>
                                    <div class="mb-2">
                                        <img src="<?php echo getImageUrl($editCategory['image'], 'categories'); ?>" width="80" class="rounded" alt="">
                                    </div>
                                    <?php endif; ?>
                                    <input type="file" name="image" class="form-control" accept="image/jpeg,image/png,image/webp">
                                    <small class="text-muted">Max 2MB (JPG, PNG, WEBP)</small>
                                </div>
                                
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-save me-2"></i>
                                    <?php echo $editCategory ? 'Update Category' : 'Add Category'; ?>
                                </button>
                                
                                <?php if ($editCategory): ?>
                                <a href="<?php echo BASE_URL; ?>admin/manage_categories.php" class="btn btn-outline-secondary w-100 mt-2">
                                    Cancel
                                </a>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Categories List -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title fw-bold mb-4">Categories List</h5>
                            
                            <div class="table-responsive">
                                <table class="table">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>ID</th>
                                            <th>Image</th>
                                            <th>Name</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($categories as $category): ?>
                                        <tr>
                                            <td><?php echo $category['id']; ?></td>
                                            <td>
                                                <?php if ($category['image']): ?>
                                                <img src="<?php echo getImageUrl($category['image'], 'categories'); ?>" width="50" height="50" class="rounded" alt="">
                                                <?php else: ?>
                                                <span class="text-muted">No image</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo e($category['name']); ?></td>
                                            <td>
                                                <a href="<?php echo BASE_URL; ?>admin/manage_categories.php?edit=<?php echo $category['id']; ?>" 
                                                   class="btn btn-sm btn-outline-primary me-1">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="<?php echo BASE_URL; ?>admin/manage_categories.php?delete=<?php echo $category['id']; ?>" 
                                                   class="btn btn-sm btn-outline-danger"
                                                   onclick="return confirm('Delete this category?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                        
                                        <?php if (empty($categories)): ?>
                                        <tr>
                                            <td colspan="4" class="text-center py-4">No categories found</td>
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
