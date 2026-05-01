<?php
/**
 * Admin - Edit Product
 */

$pageTitle = 'Edit Product';
require_once __DIR__ . '/../includes/header.php';
requireAdmin();

// Get product ID
$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($productId <= 0) {
    setFlash('Invalid product ID', 'danger');
    redirect(BASE_URL . 'admin/manage_products.php');
}

// Fetch product
try {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();
    
    if (!$product) {
        setFlash('Product not found', 'danger');
        redirect(BASE_URL . 'admin/manage_products.php');
    }
} catch (PDOException $e) {
    setFlash('Error loading product', 'danger');
    redirect(BASE_URL . 'admin/manage_products.php');
}

// Parse gallery
$gallery = [];
if (!empty($product['gallery'])) {
    $gallery = json_decode($product['gallery'], true) ?: [];
}

// Fetch categories
$categories = [];
try {
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    // Silent fail
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $categoryId = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
    $price = isset($_POST['price']) ? (float)$_POST['price'] : 0;
    $originalPrice = isset($_POST['original_price']) ? (float)$_POST['original_price'] : 0;
    $stock = isset($_POST['stock']) ? (int)$_POST['stock'] : 0;
    $status = isset($_POST['status']) ? 1 : 0;
    
    // Validation
    if (empty($name)) $errors[] = 'Product name is required';
    if (empty($description)) $errors[] = 'Description is required';
    if ($categoryId <= 0) $errors[] = 'Please select a category';
    if ($price <= 0) $errors[] = 'Price must be greater than 0';
    if ($stock < 0) $errors[] = 'Stock cannot be negative';
    
    $imageName = $product['image'];
    
    // Handle main image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = PRODUCTS_PATH;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileName = time() . '_' . basename($_FILES['image']['name']);
        $targetPath = $uploadDir . $fileName;
        
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $fileType = $_FILES['image']['type'];
        
        if (!in_array($fileType, $allowedTypes)) {
            $errors[] = 'Only JPG, PNG, and WEBP images are allowed';
        } elseif ($_FILES['image']['size'] > 2 * 1024 * 1024) {
            $errors[] = 'Image size must be less than 2MB';
        } elseif (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            // Delete old image
            if ($product['image'] && file_exists($uploadDir . $product['image'])) {
                unlink($uploadDir . $product['image']);
            }
            $imageName = $fileName;
        } else {
            $errors[] = 'Failed to upload image';
        }
    }
    
    // Handle new gallery images
    $galleryImages = $gallery;
    if (isset($_FILES['gallery']) && is_array($_FILES['gallery']['name'])) {
        $uploadDir = PRODUCTS_PATH;
        
        foreach ($_FILES['gallery']['name'] as $key => $galleryFileName) {
            if ($_FILES['gallery']['error'][$key] === UPLOAD_ERR_OK) {
                $fileName = time() . '_gallery_' . $key . '_' . basename($galleryFileName);
                $targetPath = $uploadDir . $fileName;
                
                $fileType = $_FILES['gallery']['type'][$key];
                if (in_array($fileType, ['image/jpeg', 'image/png', 'image/webp']) && 
                    $_FILES['gallery']['size'][$key] <= 2 * 1024 * 1024 &&
                    move_uploaded_file($_FILES['gallery']['tmp_name'][$key], $targetPath)) {
                    $galleryImages[] = $fileName;
                }
            }
        }
    }
    
    // Handle removed gallery images
    if (isset($_POST['remove_gallery']) && is_array($_POST['remove_gallery'])) {
        foreach ($_POST['remove_gallery'] as $removeImage) {
            $uploadDir = PRODUCTS_PATH;
            if (file_exists($uploadDir . $removeImage)) {
                unlink($uploadDir . $removeImage);
            }
            $galleryImages = array_diff($galleryImages, [$removeImage]);
        }
    }
    
    if (empty($errors)) {
        try {
            $galleryJson = !empty($galleryImages) ? json_encode(array_values($galleryImages)) : null;
            
            $stmt = $pdo->prepare("UPDATE products SET 
                name = ?, description = ?, category_id = ?, price = ?, original_price = ?, 
                stock = ?, image = ?, gallery = ?, status = ?, updated_at = NOW() 
                WHERE id = ?");
            $stmt->execute([$name, $description, $categoryId, $price, $originalPrice, 
                          $stock, $imageName, $galleryJson, $status, $productId]);
            
            setFlash('Product updated successfully!', 'success');
            redirect(BASE_URL . 'admin/manage_products.php');
        } catch (PDOException $e) {
            $errors[] = 'Failed to update product: ' . $e->getMessage();
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
                <h2 class="fw-bold mb-0">Edit Product</h2>
                <a href="<?php echo BASE_URL; ?>admin/manage_products.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Products
                </a>
            </div>
            
            <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                    <li><?php echo e($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-body p-4">
                    <form action="<?php echo BASE_URL; ?>admin/edit_product.php?id=<?php echo $productId; ?>" method="POST" enctype="multipart/form-data">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Product Name *</label>
                                <input type="text" name="name" class="form-control" required
                                       value="<?php echo e($_POST['name'] ?? $product['name']); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Category *</label>
                                <select name="category_id" class="form-select" required>
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>" 
                                        <?php echo (($product['category_id'] ?? 0) == $category['id']) ? 'selected' : ''; ?>>
                                        <?php echo e($category['name']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Description *</label>
                                <textarea name="description" class="form-control" rows="4" required><?php echo e($_POST['description'] ?? $product['description']); ?></textarea>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Price (₹) *</label>
                                <input type="number" name="price" class="form-control" step="0.01" min="0" required
                                       value="<?php echo e($_POST['price'] ?? $product['price']); ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Original Price (₹)</label>
                                <input type="number" name="original_price" class="form-control" step="0.01" min="0"
                                       value="<?php echo e($_POST['original_price'] ?? $product['original_price']); ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Stock Quantity *</label>
                                <input type="number" name="stock" class="form-control" min="0" required
                                       value="<?php echo e($_POST['stock'] ?? $product['stock']); ?>">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Status</label>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" name="status" value="1" 
                                           <?php echo ($product['status'] ?? 1) ? 'checked' : ''; ?>>
                                    <label class="form-check-label">Active</label>
                                </div>
                            </div>
                            
                            <!-- Main Image -->
                            <div class="col-md-6">
                                <label class="form-label">Main Product Image</label>
                                <?php if ($product['image']): ?>
                                <div class="mb-2">
                                    <img src="<?php echo getImageUrl($product['image'], 'products'); ?>" width="100" class="rounded" alt="">
                                </div>
                                <?php endif; ?>
                                <input type="file" name="image" class="form-control" accept="image/jpeg,image/png,image/webp">
                                <small class="text-muted">Leave empty to keep current image. Max 2MB.</small>
                            </div>
                            
                            <!-- Gallery Images -->
                            <div class="col-md-6">
                                <label class="form-label">Gallery Images</label>
                                <?php if (!empty($gallery)): ?>
                                <div class="mb-2 d-flex gap-2 flex-wrap">
                                    <?php foreach ($gallery as $galleryImage): ?>
                                    <div class="position-relative">
                                        <img src="<?php echo getImageUrl($galleryImage, 'products'); ?>" width="80" height="80" class="rounded" alt="">
                                        <div class="form-check position-absolute top-0 start-0 m-1">
                                            <input class="form-check-input" type="checkbox" name="remove_gallery[]" value="<?php echo e($galleryImage); ?>" title="Remove">
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <small class="text-muted d-block mb-2">Check to remove images</small>
                                <?php endif; ?>
                                <input type="file" name="gallery[]" class="form-control" accept="image/jpeg,image/png,image/webp" multiple>
                                <small class="text-muted">Add more images. Max 2MB each.</small>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Update Product
                            </button>
                            <a href="<?php echo BASE_URL; ?>admin/manage_products.php" class="btn btn-outline-secondary">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
