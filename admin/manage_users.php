<?php
/**
 * Admin - Manage Users
 */

$pageTitle = 'Manage Users';
require_once __DIR__ . '/../includes/header.php';
requireAdmin();

// Handle delete
if (isset($_GET['delete'])) {
    $userId = (int)$_GET['delete'];
    
    // Prevent deleting self
    if ($userId === $_SESSION['user_id']) {
        setFlash('Cannot delete your own account', 'danger');
    } else {
        try {
            // Check if user has orders
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM orders WHERE user_id = ?");
            $stmt->execute([$userId]);
            $hasOrders = $stmt->fetch()['count'] > 0;
            
            if ($hasOrders) {
                // Soft delete - just disable
                $stmt = $pdo->prepare("UPDATE users SET status = 0 WHERE id = ?");
                $stmt->execute([$userId]);
                setFlash('User has been disabled (cannot delete - has order history)', 'warning');
            } else {
                // Hard delete
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'");
                $stmt->execute([$userId]);
                setFlash('User deleted successfully', 'success');
            }
        } catch (PDOException $e) {
            setFlash('Error deleting user', 'danger');
        }
    }
    
    redirect(BASE_URL . 'admin/manage_users.php');
}

// Handle toggle status
if (isset($_GET['toggle'])) {
    $userId = (int)$_GET['toggle'];
    
    if ($userId === $_SESSION['user_id']) {
        setFlash('Cannot disable your own account', 'danger');
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE users SET status = NOT status WHERE id = ? AND role != 'admin'");
            $stmt->execute([$userId]);
            setFlash('User status updated', 'success');
        } catch (PDOException $e) {
            setFlash('Error updating user status', 'danger');
        }
    }
    
    redirect(BASE_URL . 'admin/manage_users.php');
}

// Get search parameter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build query
$query = "SELECT * FROM users WHERE role = 'user'";
$params = [];

if ($search) {
    $query .= " AND (name LIKE ? OR email LIKE ? OR mobile LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$query .= " ORDER BY created_at DESC";

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Count total
$countQuery = str_replace("*", "COUNT(*) as total", $query);
$countStmt = $pdo->prepare($countQuery);
$countStmt->execute($params);
$totalUsers = $countStmt->fetch()['total'];
$totalPages = ceil($totalUsers / $perPage);

// Add limit
$query .= " LIMIT ? OFFSET ?";
$params[] = $perPage;
$params[] = $offset;

// Fetch users
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$users = $stmt->fetchAll();
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
                <a class="nav-link" href="<?php echo BASE_URL; ?>admin/manage_coupons.php">
                    <i class="fas fa-ticket-alt"></i>Coupons
                </a>
                <a class="nav-link active" href="<?php echo BASE_URL; ?>admin/manage_users.php">
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
                <h2 class="fw-bold mb-0">Manage Users</h2>
                <form action="<?php echo BASE_URL; ?>admin/manage_users.php" method="GET" class="d-flex gap-2">
                    <input type="search" name="search" class="form-control" placeholder="Search users..." 
                           value="<?php echo e($search); ?>" style="width: 250px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="bg-light">
                                <tr>
                                    <th>ID</th>
                                    <th>User</th>
                                    <th>Contact</th>
                                    <th>Status</th>
                                    <th>Registered</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo $user['id']; ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['name']); ?>&background=f84183&color=fff" 
                                                 class="rounded-circle me-2" width="40" alt="">
                                            <div>
                                                <div class="fw-bold"><?php echo e($user['name']); ?></div>
                                                <small class="text-muted"><?php echo e($user['email']); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div><?php echo e($user['mobile'] ?? 'N/A'); ?></div>
                                        <small class="text-muted">
                                            <?php echo e($user['city'] ?? ''); ?><?php echo $user['city'] && $user['state'] ? ', ' : ''; ?><?php echo e($user['state'] ?? ''); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <a href="<?php echo BASE_URL; ?>admin/manage_users.php?toggle=<?php echo $user['id']; ?>" 
                                           class="badge bg-<?php echo $user['status'] ? 'success' : 'danger'; ?>">
                                            <?php echo $user['status'] ? 'Active' : 'Inactive'; ?>
                                        </a>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <a href="<?php echo BASE_URL; ?>admin/view_user.php?id=<?php echo $user['id']; ?>" 
                                           class="btn btn-sm btn-outline-primary me-1" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?php echo BASE_URL; ?>admin/manage_users.php?delete=<?php echo $user['id']; ?>" 
                                           class="btn btn-sm btn-outline-danger" title="Delete"
                                           onclick="return confirm('Delete this user?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                
                                <?php if (empty($users)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4">No users found</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                    <nav class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?php echo BASE_URL; ?>admin/manage_users.php?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                <a class="page-link" href="<?php echo BASE_URL; ?>admin/manage_users.php?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                            <?php endfor; ?>
                            
                            <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?php echo BASE_URL; ?>admin/manage_users.php?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">
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
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
