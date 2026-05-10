<?php
/**
 * Admin - Manage Product Reviews
 * View and delete all product reviews
 */

$pageTitle = 'Manage Reviews';
require_once __DIR__ . '/../includes/db_connect.php';
requireAdmin();

// Handle delete action
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $reviewId = (int)$_GET['delete'];
    
    try {
        $stmt = $pdo->prepare("DELETE FROM reviews WHERE id = ?");
        $stmt->execute([$reviewId]);
        
        if ($stmt->rowCount() > 0) {
            setFlash('Review deleted successfully!', 'success');
        } else {
            setFlash('Review not found.', 'warning');
        }
    } catch (PDOException $e) {
        setFlash('Error deleting review.', 'danger');
    }
    
    redirect(BASE_URL . 'admin/manage_reviews.php');
}

// Get filter parameters
$productFilter = isset($_GET['product']) ? (int)$_GET['product'] : 0;
$ratingFilter = isset($_GET['rating']) ? (int)$_GET['rating'] : 0;
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build query
$queryParams = [];
$whereClauses = [];

if ($productFilter > 0) {
    $whereClauses[] = "r.product_id = ?";
    $queryParams[] = $productFilter;
}

if ($ratingFilter > 0) {
    $whereClauses[] = "r.rating = ?";
    $queryParams[] = $ratingFilter;
}

if (!empty($searchQuery)) {
    $whereClauses[] = "(u.name LIKE ? OR p.name LIKE ? OR r.comment LIKE ?)";
    $searchParam = "%$searchQuery%";
    $queryParams[] = $searchParam;
    $queryParams[] = $searchParam;
    $queryParams[] = $searchParam;
}

$whereSql = empty($whereClauses) ? '' : 'WHERE ' . implode(' AND ', $whereClauses);

// Fetch reviews with product and user info
try {
    $sql = "SELECT r.*, p.name as product_name, p.image as product_image, u.name as user_name, u.email as user_email 
            FROM reviews r 
            JOIN products p ON r.product_id = p.id 
            JOIN users u ON r.user_id = u.id 
            $whereSql
            ORDER BY r.created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($queryParams);
    $reviews = $stmt->fetchAll();
} catch (PDOException $e) {
    $reviews = [];
    setFlash('Error loading reviews.', 'danger');
}

// Fetch products for filter dropdown
try {
    $stmt = $pdo->query("SELECT id, name FROM products WHERE status = 1 ORDER BY name ASC");
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    $products = [];
}

// Calculate statistics
try {
    $totalReviews = count($reviews);
    $avgRating = $totalReviews > 0 ? round(array_sum(array_column($reviews, 'rating')) / $totalReviews, 1) : 0;
    
    // Rating distribution
    $ratingDist = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
    foreach ($reviews as $review) {
        $ratingDist[(int)$review['rating']]++;
    }
} catch (Exception $e) {
    $totalReviews = 0;
    $avgRating = 0;
    $ratingDist = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="bg-white mt-20">
    <div class="h-[calc(100vh-80px)] flex">
        <?php include __DIR__ . '/includes/sidebar.php'; ?>

        <div class="flex-1 overflow-y-auto p-6 md:p-8">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-gray-900">Manage Reviews</h2>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-xl shadow-sm p-4 border">
                    <div class="text-2xl font-bold text-gray-900"><?php echo $totalReviews; ?></div>
                    <div class="text-sm text-gray-500">Total Reviews</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-4 border">
                    <div class="text-2xl font-bold text-yellow-500"><?php echo $avgRating; ?> <i class="fas fa-star text-sm"></i></div>
                    <div class="text-sm text-gray-500">Average Rating</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-4 border">
                    <div class="text-2xl font-bold text-green-600"><?php echo $ratingDist[5] + $ratingDist[4]; ?></div>
                    <div class="text-sm text-gray-500">Positive (4-5★)</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm p-4 border">
                    <div class="text-2xl font-bold text-red-500"><?php echo $ratingDist[2] + $ratingDist[1]; ?></div>
                    <div class="text-sm text-gray-500">Negative (1-2★)</div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-xl shadow-sm p-4 border mb-6">
                <form method="GET" class="flex flex-wrap gap-4 items-end">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Product</label>
                        <select name="product" class="w-full md:w-48 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <option value="">All Products</option>
                            <?php foreach ($products as $product): ?>
                                <option value="<?php echo $product['id']; ?>" <?php echo $productFilter == $product['id'] ? 'selected' : ''; ?>>
                                    <?php echo e($product['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Rating</label>
                        <select name="rating" class="w-full md:w-32 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <option value="">All Ratings</option>
                            <option value="5" <?php echo $ratingFilter == 5 ? 'selected' : ''; ?>>5 Stars</option>
                            <option value="4" <?php echo $ratingFilter == 4 ? 'selected' : ''; ?>>4 Stars</option>
                            <option value="3" <?php echo $ratingFilter == 3 ? 'selected' : ''; ?>>3 Stars</option>
                            <option value="2" <?php echo $ratingFilter == 2 ? 'selected' : ''; ?>>2 Stars</option>
                            <option value="1" <?php echo $ratingFilter == 1 ? 'selected' : ''; ?>>1 Star</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                        <input type="text" name="search" value="<?php echo e($searchQuery); ?>" 
                               placeholder="User, product, or comment..."
                               class="w-full md:w-64 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                    
                    <div class="flex gap-2">
                        <button type="submit" class="bg-primary-500 hover:bg-primary-600 text-white font-semibold py-2 px-4 rounded-lg transition">
                            <i class="fas fa-filter mr-1"></i> Filter
                        </button>
                        <a href="<?php echo BASE_URL; ?>admin/manage_reviews.php" class="border-2 border-gray-300 hover:bg-gray-50 text-gray-700 font-medium py-2 px-4 rounded-lg transition">
                            <i class="fas fa-undo mr-1"></i> Reset
                        </a>
                    </div>
                </form>
            </div>

            <!-- Reviews List -->
            <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
                <?php if (empty($reviews)): ?>
                    <div class="p-8 text-center text-gray-500">
                        <i class="fas fa-star text-4xl text-gray-300 mb-3"></i>
                        <p>No reviews found.</p>
                        <?php if ($productFilter > 0 || $ratingFilter > 0 || !empty($searchQuery)): ?>
                            <a href="<?php echo BASE_URL; ?>admin/manage_reviews.php" class="text-primary-500 hover:text-primary-600 mt-2 inline-block">
                                Clear filters
                            </a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Product</th>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Customer</th>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Rating</th>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Review</th>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Date</th>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php foreach ($reviews as $review): 
                                    $ratingColors = [
                                        5 => 'text-green-600 bg-green-100',
                                        4 => 'text-blue-600 bg-blue-100',
                                        3 => 'text-yellow-600 bg-yellow-100',
                                        2 => 'text-orange-600 bg-orange-100',
                                        1 => 'text-red-600 bg-red-100'
                                    ];
                                    $ratingClass = $ratingColors[(int)$review['rating']] ?? 'text-gray-600 bg-gray-100';
                                ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-3">
                                                <?php if (!empty($review['product_image'])): ?>
                                                    <img src="<?php echo getImageUrl($review['product_image'], 'products'); ?>" 
                                                         alt="" class="w-10 h-10 rounded-lg object-cover">
                                                <?php else: ?>
                                                    <div class="w-10 h-10 rounded-lg bg-gray-200 flex items-center justify-center">
                                                        <i class="fas fa-box text-gray-400"></i>
                                                    </div>
                                                <?php endif; ?>
                                                <div>
                                                    <div class="font-medium text-gray-900 text-sm"><?php echo e($review['product_name']); ?></div>
                                                    <a href="<?php echo BASE_URL; ?>product.php?id=<?php echo $review['product_id']; ?>" 
                                                       target="_blank" class="text-xs text-primary-500 hover:text-primary-600">
                                                        View Product <i class="fas fa-external-link-alt text-xs"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="font-medium text-gray-900 text-sm"><?php echo e($review['user_name']); ?></div>
                                            <div class="text-xs text-gray-500"><?php echo e($review['user_email']); ?></div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded text-xs font-bold <?php echo $ratingClass; ?>">
                                                <?php echo $review['rating']; ?> <i class="fas fa-star text-xs"></i>
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <p class="text-sm text-gray-600 max-w-xs line-clamp-2" title="<?php echo e($review['comment']); ?>">
                                                <?php echo e($review['comment']); ?>
                                            </p>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-500">
                                            <?php echo date('M d, Y', strtotime($review['created_at'])); ?>
                                            <div class="text-xs"><?php echo date('h:i A', strtotime($review['created_at'])); ?></div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <a href="<?php echo BASE_URL; ?>admin/manage_reviews.php?delete=<?php echo $review['id']; ?>" 
                                               onclick="return confirm('Are you sure you want to delete this review?')" 
                                               class="inline-flex items-center border-2 border-red-500 text-red-500 hover:bg-red-500 hover:text-white font-medium py-1 px-3 rounded-lg transition text-sm">
                                                <i class="fas fa-trash mr-1"></i> Delete
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>
</body>
</html>
