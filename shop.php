<?php
/**
 * Shop Page
 * Product Listing with Filters
 */

$pageTitle = 'Shop';
require_once 'includes/header.php';

// Get filter parameters
$categoryId = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
$sortBy = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$minPrice = isset($_GET['min_price']) ? (float)$_GET['min_price'] : 0;
$maxPrice = isset($_GET['max_price']) ? (float)$_GET['max_price'] : 0;

// Build query
$query = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.status = 1";
$params = [];

// Category filter
if ($categoryId > 0) {
    $query .= " AND p.category_id = ?";
    $params[] = $categoryId;
}

// Search filter
if (!empty($searchQuery)) {
    $query .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$searchQuery%";
    $params[] = "%$searchQuery%";
}

// Price filter
if ($minPrice > 0) {
    $query .= " AND p.price >= ?";
    $params[] = $minPrice;
}
if ($maxPrice > 0) {
    $query .= " AND p.price <= ?";
    $params[] = $maxPrice;
}

// Sorting
switch ($sortBy) {
    case 'price_low':
        $query .= " ORDER BY p.price ASC";
        break;
    case 'price_high':
        $query .= " ORDER BY p.price DESC";
        break;
    case 'name_asc':
        $query .= " ORDER BY p.name ASC";
        break;
    case 'name_desc':
        $query .= " ORDER BY p.name DESC";
        break;
    default: // newest
        $query .= " ORDER BY p.created_at DESC";
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 12;
$offset = ($page - 1) * $perPage;

// Count total products for pagination
$countQuery = str_replace("p.*, c.name as category_name", "COUNT(*) as total", $query);
$countStmt = $pdo->prepare($countQuery);
$countStmt->execute($params);
$totalProducts = $countStmt->fetch()['total'];
$totalPages = ceil($totalProducts / $perPage);

// Add limit for pagination
$query .= " LIMIT ? OFFSET ?";
$params[] = $perPage;
$params[] = $offset;

// Fetch products
$products = [];
try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    // Silent fail
}

// Fetch categories for filter
$categories = [];
try {
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    // Silent fail
}

// Get selected category name
$selectedCategory = null;
if ($categoryId > 0) {
    foreach ($categories as $cat) {
        if ($cat['id'] == $categoryId) {
            $selectedCategory = $cat;
            break;
        }
    }
}
?>

<!-- Page Header -->
<section class="bg-gradient-to-r from-pink-50 to-purple-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <nav class="text-sm text-gray-600 mb-2">
            <ol class="flex items-center space-x-2">
                <li><a href="<?php echo BASE_URL; ?>" class="hover:text-primary-500">Home</a></li>
                <li><i class="fas fa-chevron-right text-xs"></i></li>
                <li class="text-primary-500 font-medium">Shop</li>
            </ol>
        </nav>
        <h1 class="text-2xl md:text-3xl font-bold text-gray-900">
            <?php echo $selectedCategory ? e($selectedCategory['name']) : 'All Products'; ?>
        </h1>
        <p class="text-muted mb-0">Showing <?php echo count($products); ?> of <?php echo $totalProducts; ?> products</p>
    </div>
</section>

<!-- Shop Content -->
<section class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <!-- Sidebar Filters -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl shadow-md p-6 sticky top-24">
                    <h5 class="text-lg font-bold mb-4 text-gray-900">
                        <i class="fas fa-filter mr-2 text-primary-500"></i>Filters
                    </h5>
                    
                    <!-- Category Filter -->
                    <div class="mb-6">
                        <h6 class="font-semibold mb-3 text-gray-700">Categories</h6>
                        <div class="space-y-2">
                            <a href="<?php echo BASE_URL; ?>shop.php" 
                               class="block py-2 px-3 rounded-lg text-sm <?php echo $categoryId == 0 ? 'bg-primary-50 text-primary-600 font-medium' : 'text-gray-600 hover:bg-gray-50'; ?>">
                                All Categories
                            </a>
                            <?php foreach ($categories as $category): ?>
                            <a href="<?php echo BASE_URL; ?>shop.php?category=<?php echo $category['id']; ?>" 
                               class="block py-2 px-3 rounded-lg text-sm <?php echo $categoryId == $category['id'] ? 'bg-primary-50 text-primary-600 font-medium' : 'text-gray-600 hover:bg-gray-50'; ?>">
                                <?php if ($category['image']): ?>
                                    <img src="<?php echo getImageUrl($category['image'], 'categories'); ?>" class="w-5 h-5 rounded-full inline mr-2">
                                <?php endif; ?>
                                <?php echo e($category['name']); ?>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Price Filter -->
                    <form action="<?php echo BASE_URL; ?>shop.php" method="GET">
                        <?php if ($categoryId > 0): ?>
                        <input type="hidden" name="category" value="<?php echo $categoryId; ?>">
                        <?php endif; ?>
                        <?php if (!empty($searchQuery)): ?>
                        <input type="hidden" name="search" value="<?php echo e($searchQuery); ?>">
                        <?php endif; ?>
                        
                        <div class="mb-6">
                            <h6 class="font-semibold mb-3 text-gray-700">Price Range</h6>
                            <div class="grid grid-cols-2 gap-3">
                                <input type="number" name="min_price" placeholder="Min" 
                                       value="<?php echo $minPrice > 0 ? $minPrice : ''; ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <input type="number" name="max_price" placeholder="Max"
                                       value="<?php echo $maxPrice > 0 ? $maxPrice : ''; ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>
                        </div>
                        
                        <button type="submit" class="w-full bg-primary-500 hover:bg-primary-600 text-white font-medium py-2 px-4 rounded-lg transition">
                            <i class="fas fa-filter mr-2"></i>Apply Filters
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Product Grid -->
            <div class="lg:col-span-3">
                <!-- Sort and Results -->
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                    <p class="text-gray-600">
                        <?php if (!empty($searchQuery)): ?>
                            Search results for "<strong class="text-gray-900"><?php echo e($searchQuery); ?></strong>"
                        <?php else: ?>
                            Showing <?php echo count($products); ?> of <?php echo $totalProducts; ?> products
                        <?php endif; ?>
                    </p>
                    
                    <form action="<?php echo BASE_URL; ?>shop.php" method="GET" class="flex items-center space-x-2">
                        <?php if ($categoryId > 0): ?>
                        <input type="hidden" name="category" value="<?php echo $categoryId; ?>">
                        <?php endif; ?>
                        <?php if (!empty($searchQuery)): ?>
                        <input type="hidden" name="search" value="<?php echo e($searchQuery); ?>">
                        <?php endif; ?>
                        <?php if ($minPrice > 0): ?>
                        <input type="hidden" name="min_price" value="<?php echo $minPrice; ?>">
                        <?php endif; ?>
                        <?php if ($maxPrice > 0): ?>
                        <input type="hidden" name="max_price" value="<?php echo $maxPrice; ?>">
                        <?php endif; ?>
                        
                        <label class="text-sm text-gray-600">Sort by:</label>
                        <select name="sort" onchange="this.form.submit()"
                                class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <option value="newest" <?php echo $sortBy == 'newest' ? 'selected' : ''; ?>>Newest First</option>
                            <option value="price_low" <?php echo $sortBy == 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                            <option value="price_high" <?php echo $sortBy == 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                            <option value="name_asc" <?php echo $sortBy == 'name_asc' ? 'selected' : ''; ?>>Name: A to Z</option>
                            <option value="name_desc" <?php echo $sortBy == 'name_desc' ? 'selected' : ''; ?>>Name: Z to A</option>
                        </select>
                    </form>
                </div>
                
                <!-- Products Grid -->
                <div class="grid grid-cols-2 md:grid-cols-3 gap-6">
                    <?php foreach ($products as $product): ?>
                    <div class="bg-white rounded-2xl shadow-md hover:shadow-xl transition overflow-hidden group">
                        <div class="relative">
                            <?php $imageUrl = getImageUrl($product['image'], 'products'); ?>
                            <img src="<?php echo $imageUrl; ?>" alt="<?php echo e($product['name']); ?>" class="w-full h-48 object-cover">
                            
                            <?php if ($product['stock'] <= 5 && $product['stock'] > 0): ?>
                                <span class="absolute top-2 left-2 bg-yellow-400 text-yellow-900 text-xs font-bold px-2 py-1 rounded">Low Stock</span>
                            <?php elseif ($product['stock'] == 0): ?>
                                <span class="absolute top-2 left-2 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded">Out of Stock</span>
                            <?php endif; ?>
                            
                            <div class="absolute top-2 right-2 flex flex-col gap-2">
                                <?php renderWishlistIconButton($product['id']); ?>
                                <a href="<?php echo BASE_URL; ?>product.php?id=<?php echo $product['id']; ?>" 
                                   class="bg-white/90 hover:bg-white text-primary-500 w-10 h-10 rounded-full flex items-center justify-center shadow opacity-0 group-hover:opacity-100 transition"
                                   title="View Details"
                                   aria-label="View details">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </div>
                        <div class="p-4">
                            <small class="text-gray-400 text-xs"><?php echo e($product['category_name'] ?? 'Uncategorized'); ?></small>
                            <h5 class="font-semibold text-gray-900 mb-1 truncate"><?php echo e($product['name']); ?></h5>
                            <p class="text-gray-500 text-sm mb-3 line-clamp-2"><?php echo substr(e($product['description']), 0, 50) . '...'; ?></p>
                            <div class="flex justify-between items-center">
                                <span class="text-primary-600 font-bold text-lg"><?php echo formatCurrency($product['price']); ?></span>
                                <?php if ($product['stock'] > 0): ?>
                                <form action="<?php echo BASE_URL; ?>cart.php" method="POST">
                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                    <input type="hidden" name="action" value="add">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="bg-primary-500 hover:bg-primary-600 text-white w-10 h-10 rounded-full flex items-center justify-center transition">
                                        <i class="fas fa-cart-plus"></i>
                                    </button>
                                </form>
                                <?php else: ?>
                                <button class="bg-gray-300 text-white w-10 h-10 rounded-full flex items-center justify-center cursor-not-allowed" disabled>
                                    <i class="fas fa-times"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- No Results -->
                <?php if (empty($products)): ?>
                <div class="text-center py-12">
                    <i class="fas fa-search text-5xl text-gray-300 mb-4"></i>
                    <h4 class="text-xl font-semibold text-gray-700 mb-2">No products found</h4>
                    <p class="text-gray-500 mb-4">Try adjusting your filters or search query</p>
                    <a href="<?php echo BASE_URL; ?>shop.php" class="inline-flex items-center bg-primary-500 hover:bg-primary-600 text-white font-medium py-2 px-6 rounded-lg transition">
                        Clear Filters
                    </a>
                </div>
                <?php endif; ?>
                
                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                <nav class="mt-8">
                    <ul class="flex justify-center items-center space-x-2">
                        <?php if ($page > 1): ?>
                        <li>
                            <a href="<?php echo BASE_URL; ?>shop.php?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>"
                               class="w-10 h-10 rounded-lg bg-white border border-gray-300 flex items-center justify-center text-gray-600 hover:bg-gray-50 hover:text-primary-500 transition">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li>
                            <a href="<?php echo BASE_URL; ?>shop.php?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"
                               class="w-10 h-10 rounded-lg flex items-center justify-center transition <?php echo $i == $page ? 'bg-primary-500 text-white' : 'bg-white border border-gray-300 text-gray-600 hover:bg-gray-50 hover:text-primary-500'; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                        <li>
                            <a href="<?php echo BASE_URL; ?>shop.php?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>"
                               class="w-10 h-10 rounded-lg bg-white border border-gray-300 flex items-center justify-center text-gray-600 hover:bg-gray-50 hover:text-primary-500 transition">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
