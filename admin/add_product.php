<?php
/**
 * Admin - Add Product
 */

$pageTitle = 'Add Product';
require_once __DIR__ . '/../includes/header.php';
requireAdmin();

// Fetch categories
$categories = [];
try {
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    // Silent fail
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $categoryId = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
    $price = isset($_POST['price']) ? (float)$_POST['price'] : 0;
    $originalPrice = isset($_POST['original_price']) ? (float)$_POST['original_price'] : 0;
    $stock = isset($_POST['stock']) ? (int)$_POST['stock'] : 0;
    
    // Validation
    if (empty($name)) $errors[] = 'Product name is required';
    if (empty($description)) $errors[] = 'Description is required';
    if ($categoryId <= 0) $errors[] = 'Please select a category';
    if ($price <= 0) $errors[] = 'Price must be greater than 0';
    if ($stock < 0) $errors[] = 'Stock cannot be negative';
    
    // Handle main image upload
    $imageName = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = PRODUCTS_PATH;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileName = time() . '_' . basename($_FILES['image']['name']);
        $targetPath = $uploadDir . $fileName;
        
        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        $fileType = $_FILES['image']['type'];
        
        if (!in_array($fileType, $allowedTypes)) {
            $errors[] = 'Only JPG, PNG, and WEBP images are allowed';
        } elseif ($_FILES['image']['size'] > 2 * 1024 * 1024) {
            $errors[] = 'Image size must be less than 2MB';
        } elseif (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $imageName = $fileName;
        } else {
            $errors[] = 'Failed to upload image';
        }
    }
    
    // Handle gallery images
    $galleryImages = [];
    if (isset($_FILES['gallery']) && is_array($_FILES['gallery']['name'])) {
        $uploadDir = PRODUCTS_PATH;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
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
    
    if (empty($errors)) {
        try {
            $galleryJson = !empty($galleryImages) ? json_encode($galleryImages) : null;
            
            $stmt = $pdo->prepare("INSERT INTO products (name, description, category_id, price, original_price, stock, image, gallery, status, created_at) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, NOW())");
            $stmt->execute([$name, $description, $categoryId, $price, $originalPrice, $stock, $imageName, $galleryJson]);
            
            setFlash('Product added successfully!', 'success');
            redirect(BASE_URL . 'admin/manage_products.php');
        } catch (PDOException $e) {
            $errors[] = 'Failed to add product: ' . $e->getMessage();
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
                <a href="<?php echo BASE_URL; ?>admin/manage_products.php" class="flex items-center px-4 py-3 bg-primary-500 rounded-lg text-white">
                    <i class="fas fa-box w-6"></i>Products
                </a>
                <a href="<?php echo BASE_URL; ?>admin/manage_categories.php" class="flex items-center px-4 py-3 text-gray-300 hover:bg-gray-800 rounded-lg transition">
                    <i class="fas fa-tags w-6"></i>Categories
                </a>
                <a href="<?php echo BASE_URL; ?>admin/manage_orders.php" class="flex items-center px-4 py-3 text-gray-300 hover:bg-gray-800 rounded-lg transition">
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
                <h2 class="text-2xl font-bold text-gray-900">Add New Product</h2>
                <a href="<?php echo BASE_URL; ?>admin/manage_products.php" class="inline-flex items-center border-2 border-gray-300 hover:border-gray-400 text-gray-700 font-medium py-2 px-4 rounded-lg transition">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Products
                </a>
            </div>
            
            <?php if (!empty($errors)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
                <ul class="mb-0 list-disc list-inside">
                    <?php foreach ($errors as $error): ?>
                    <li><?php echo e($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
            
            <div class="bg-white rounded-xl shadow-md p-6">
                <form action="<?php echo BASE_URL; ?>admin/add_product.php" method="POST" enctype="multipart/form-data" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Product Name *</label>
                            <input type="text" name="name" required
                                   value="<?php echo isset($_POST['name']) ? e($_POST['name']) : ''; ?>"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                            <select name="category_id" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>" 
                                    <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                    <?php echo e($category['name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description *</label>
                            <textarea name="description" rows="4" required
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition"><?php echo isset($_POST['description']) ? e($_POST['description']) : ''; ?></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Price (₹) *</label>
                            <input type="number" name="price" step="0.01" min="0" required
                                   value="<?php echo isset($_POST['price']) ? e($_POST['price']) : ''; ?>"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Original Price (₹)</label>
                            <input type="number" name="original_price" step="0.01" min="0"
                                   value="<?php echo isset($_POST['original_price']) ? e($_POST['original_price']) : ''; ?>"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                            <p class="text-xs text-gray-500 mt-1">For showing discount</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Stock Quantity *</label>
                            <input type="number" name="stock" min="0" required
                                   value="<?php echo isset($_POST['stock']) ? e($_POST['stock']) : ''; ?>"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Main Product Image</label>
                            <input type="file" name="image" accept="image/jpeg,image/png,image/webp"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                            <p class="text-xs text-gray-500 mt-1">Max 2MB (JPG, PNG, WEBP)</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Gallery Images</label>
                            <input type="file" name="gallery[]" accept="image/jpeg,image/png,image/webp" multiple
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                            <p class="text-xs text-gray-500 mt-1">Select multiple images. Max 2MB each.</p>
                        </div>
                    </div>
                    
                    <div class="border-t border-gray-200 pt-6 flex flex-wrap gap-3">
                        <button type="submit" class="bg-primary-500 hover:bg-primary-600 text-white font-semibold py-3 px-6 rounded-full transition shadow-lg hover:shadow-xl">
                            <i class="fas fa-save mr-2"></i>Add Product
                        </button>
                        <a href="<?php echo BASE_URL; ?>admin/manage_products.php" class="inline-flex items-center border-2 border-gray-300 hover:border-gray-400 text-gray-700 font-medium py-3 px-6 rounded-full transition">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
