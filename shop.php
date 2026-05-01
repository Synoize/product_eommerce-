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
<section class="bg-primary-light py-4">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>">Home</a></li>
                <li class="breadcrumb-item active">Shop</li>
            </ol>
        </nav>
        <h1 class="fw-bold mt-2">
            <?php echo $selectedCategory ? e($selectedCategory['name']) : 'All Products'; ?>
        </h1>
        <p class="text-muted mb-0">Showing <?php echo count($products); ?> of <?php echo $totalProducts; ?> products</p>
    </div>
</section>

<!-- Shop Content -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <!-- Sidebar Filters -->
            <div class="col-lg-3 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-4">
                            <i class="fas fa-filter me-2"></i>Filters
                        </h5>
                        
                        <!-- Category Filter -->
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3">Categories</h6>
                            <div class="list-group list-group-flush">
                                <a href="<?php echo BASE_URL; ?>shop.php" 
                                   class="list-group-item list-group-item-action <?php echo $categoryId == 0 ? 'active' : ''; ?>">
                                    All Categories
                                </a>
                                <?php foreach ($categories as $category): ?>
                                <a href="<?php echo BASE_URL; ?>shop.php?category=<?php echo $category['id']; ?>" 
                                   class="list-group-item list-group-item-action <?php echo $categoryId == $category['id'] ? 'active' : ''; ?>">
                                    <?php if ($category['image']): ?>
                                        <img src="<?php echo getImageUrl($category['image'], 'categories'); ?>" width="20" height="20" class="me-2 rounded-circle">
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
                            
                            <div class="mb-4">
                                <h6 class="fw-bold mb-3">Price Range</h6>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <input type="number" name="min_price" class="form-control" placeholder="Min" 
                                               value="<?php echo $minPrice > 0 ? $minPrice : ''; ?>">
                                    </div>
                                    <div class="col-6">
                                        <input type="number" name="max_price" class="form-control" placeholder="Max"
                                               value="<?php echo $maxPrice > 0 ? $maxPrice : ''; ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-filter me-2"></i>Apply Filters
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Product Grid -->
            <div class="col-lg-9">
                <!-- Sort and Results -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <p class="text-muted mb-0">
                        <?php if (!empty($searchQuery)): ?>
                            Search results for "<strong><?php echo e($searchQuery); ?></strong>"
                        <?php endif; ?>
                    </p>
                    
                    <form action="<?php echo BASE_URL; ?>shop.php" method="GET" class="d-flex align-items-center">
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
                        
                        <label class="me-2 text-muted">Sort by:</label>
                        <select name="sort" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()">
                            <option value="newest" <?php echo $sortBy == 'newest' ? 'selected' : ''; ?>>Newest First</option>
                            <option value="price_low" <?php echo $sortBy == 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                            <option value="price_high" <?php echo $sortBy == 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                            <option value="name_asc" <?php echo $sortBy == 'name_asc' ? 'selected' : ''; ?>>Name: A to Z</option>
                            <option value="name_desc" <?php echo $sortBy == 'name_desc' ? 'selected' : ''; ?>>Name: Z to A</option>
                        </select>
                    </form>
                </div>
                
                <!-- Products Grid -->
                <div class="row g-4">
                    <?php foreach ($products as $product): ?>
                    <div class="col-6 col-md-4">
                        <div class="card product-card h-100">
                            <div class="position-relative">
                                <?php 
                                $imageUrl = getImageUrl($product['image'], 'products');
                                ?>
                                <img src="<?php echo $imageUrl; ?>" class="card-img-top" alt="<?php echo e($product['name']); ?>">
                                
                                <?php if ($product['stock'] <= 5 && $product['stock'] > 0): ?>
                                    <span class="badge bg-warning position-absolute top-0 start-0 m-2">Low Stock</span>
                                <?php elseif ($product['stock'] == 0): ?>
                                    <span class="badge bg-danger position-absolute top-0 start-0 m-2">Out of Stock</span>
                                <?php endif; ?>
                                
                                <div class="product-actions position-absolute top-0 end-0 m-2 d-flex flex-column gap-2">
                                    <a href="<?php echo BASE_URL; ?>product.php?id=<?php echo $product['id']; ?>" 
                                       class="btn btn-light btn-sm rounded-circle" title="View Details">
                                        <i class="fas fa-eye text-primary"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="card-body d-flex flex-column">
                                <small class="text-muted"><?php echo e($product['category_name'] ?? 'Uncategorized'); ?></small>
                                <h5 class="card-title"><?php echo e($product['name']); ?></h5>
                                <p class="card-text flex-grow-1"><?php echo substr(e($product['description']), 0, 50) . '...'; ?></p>
                                <div class="d-flex justify-content-between align-items-center mt-auto">
                                    <span class="price"><?php echo formatCurrency($product['price']); ?></span>
                                    <?php if ($product['stock'] > 0): ?>
                                    <form action="<?php echo BASE_URL; ?>cart.php" method="POST" class="d-inline">
                                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                        <input type="hidden" name="action" value="add">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            <i class="fas fa-cart-plus"></i>
                                        </button>
                                    </form>
                                    <?php else: ?>
                                    <button class="btn btn-secondary btn-sm" disabled>Out of Stock</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- No Results -->
                <?php if (empty($products)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                    <h4>No products found</h4>
                    <p class="text-muted">Try adjusting your filters or search query</p>
                    <a href="<?php echo BASE_URL; ?>shop.php" class="btn btn-primary">Clear Filters</a>
                </div>
                <?php endif; ?>
                
                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                <nav class="mt-5">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?php echo BASE_URL; ?>shop.php?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                            <a class="page-link" href="<?php echo BASE_URL; ?>shop.php?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?php echo BASE_URL; ?>shop.php?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">
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
