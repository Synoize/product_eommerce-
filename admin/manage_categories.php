<?php
/**
 * Admin - Manage Categories
 */

$pageTitle = 'Manage Categories';
require_once __DIR__ . '/includes/header.php';
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

<div class="bg-white mt-20">
    <div class="h-[calc(100vh-80px)] flex">
        <!-- Admin Sidebar -->
        <?php include __DIR__ . '/includes/sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="flex-1 overflow-y-auto p-6 md:p-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Manage Categories</h2>
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Category Form -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <h5 class="font-bold text-gray-900 mb-4">
                            <?php echo $editCategory ? 'Edit Category' : 'Add New Category'; ?>
                        </h5>
                        
                        <form action="<?php echo BASE_URL; ?>admin/manage_categories.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                            <input type="hidden" name="form_action" value="<?php echo $editCategory ? 'edit' : 'add'; ?>">
                            <?php if ($editCategory): ?>
                            <input type="hidden" name="category_id" value="<?php echo $editCategory['id']; ?>">
                            <input type="hidden" name="existing_image" value="<?php echo $editCategory['image']; ?>">
                            <?php endif; ?>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Category Name *</label>
                                <input type="text" name="name" required
                                       value="<?php echo $editCategory ? e($editCategory['name']) : ''; ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Category Image</label>
                                <?php if ($editCategory && $editCategory['image']): ?>
                                <div class="mb-2">
                                    <img src="<?php echo getImageUrl($editCategory['image'], 'categories'); ?>" class="w-20 h-20 rounded-lg object-cover" alt="">
                                </div>
                                <?php endif; ?>
                                <input type="file" name="image" accept="image/jpeg,image/png,image/webp"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                                <p class="text-xs text-gray-500 mt-1">Max 2MB (JPG, PNG, WEBP)</p>
                            </div>
                            
                            <button type="submit" class="w-full bg-primary-500 hover:bg-primary-600 text-white font-semibold py-3 rounded-full transition shadow-lg hover:shadow-xl">
                                <i class="fas fa-save mr-2"></i>
                                <?php echo $editCategory ? 'Update Category' : 'Add Category'; ?>
                            </button>
                            
                            <?php if ($editCategory): ?>
                            <a href="<?php echo BASE_URL; ?>admin/manage_categories.php" class="block w-full text-center border-2 border-gray-300 hover:border-gray-400 text-gray-700 font-medium py-3 rounded-full transition">
                                Cancel
                            </a>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
                
                <!-- Categories List -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <h5 class="font-bold text-gray-900 mb-4">Categories List</h5>
                        
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">ID</th>
                                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Image</th>
                                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Name</th>
                                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <?php foreach ($categories as $category): ?>
                                    <tr>
                                        <td class="px-4 py-3 text-sm"><?php echo $category['id']; ?></td>
                                        <td class="px-4 py-3">
                                            <?php if ($category['image']): ?>
                                            <img src="<?php echo getImageUrl($category['image'], 'categories'); ?>" class="w-12 h-12 rounded-lg object-cover" alt="">
                                            <?php else: ?>
                                            <span class="text-gray-500 text-sm">No image</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900"><?php echo e($category['name']); ?></td>
                                        <td class="px-4 py-3">
                                            <div class="flex gap-2">
                                                <a href="<?php echo BASE_URL; ?>admin/manage_categories.php?edit=<?php echo $category['id']; ?>" 
                                                   class="inline-flex items-center border-2 border-primary-500 text-primary-500 hover:bg-primary-500 hover:text-white font-medium py-1 px-2 rounded-lg transition text-sm">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="<?php echo BASE_URL; ?>admin/manage_categories.php?delete=<?php echo $category['id']; ?>" 
                                                   class="inline-flex items-center border-2 border-red-500 text-red-500 hover:bg-red-500 hover:text-white font-medium py-1 px-2 rounded-lg transition text-sm"
                                                   onclick="return confirm('Delete this category?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    
                                    <?php if (empty($categories)): ?>
                                    <tr>
                                        <td colspan="4" class="px-4 py-8 text-center text-gray-500">No categories found</td>
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
