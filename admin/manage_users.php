<?php
/**
 * Admin - Manage Users
 */

$pageTitle = 'Manage Users';
require_once __DIR__ . '/includes/header.php';
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

<div class="bg-white mt-20">
    <div class="h-[calc(100vh-80px)] flex">
        <!-- Admin Sidebar -->
        <?php include __DIR__ . '/includes/sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="flex-1 overflow-y-auto p-6 md:p-8">
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-6 gap-4">
                <h2 class="text-2xl font-bold text-gray-900">Manage Users</h2>
                <form action="<?php echo BASE_URL; ?>admin/manage_users.php" method="GET" class="flex gap-2">
                    <input type="search" name="search" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 w-64" 
                           placeholder="Search users..." value="<?php echo e($search); ?>">
                    <button type="submit" class="bg-primary-500 hover:bg-primary-600 text-white font-semibold py-2 px-4 rounded-lg transition">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
            
            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">ID</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">User</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Contact</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Status</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Registered</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td class="px-4 py-3 text-sm"><?php echo $user['id']; ?></td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center">
                                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['name']); ?>&background=f84183&color=fff" 
                                             class="w-10 h-10 rounded-full mr-3" alt="">
                                        <div>
                                            <div class="font-medium text-gray-900"><?php echo e($user['name']); ?></div>
                                            <div class="text-sm text-gray-500"><?php echo e($user['email']); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-sm text-gray-900"><?php echo e($user['mobile'] ?? 'N/A'); ?></div>
                                    <div class="text-sm text-gray-500">
                                        <?php echo e($user['city'] ?? ''); ?><?php echo $user['city'] && $user['state'] ? ', ' : ''; ?><?php echo e($user['state'] ?? ''); ?>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <a href="<?php echo BASE_URL; ?>admin/manage_users.php?toggle=<?php echo $user['id']; ?>" 
                                       class="inline-block px-2 py-1 rounded text-xs font-medium <?php echo $user['status'] ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                                        <?php echo $user['status'] ? 'Active' : 'Inactive'; ?>
                                    </a>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                <td class="px-4 py-3">
                                    <div class="flex gap-2">
                                        <a href="<?php echo BASE_URL; ?>admin/view_user.php?id=<?php echo $user['id']; ?>" 
                                           class="inline-flex items-center border-2 border-primary-500 text-primary-500 hover:bg-primary-500 hover:text-white font-medium py-1 px-2 rounded-lg transition text-sm" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?php echo BASE_URL; ?>admin/manage_users.php?delete=<?php echo $user['id']; ?>" 
                                           class="inline-flex items-center border-2 border-red-500 text-red-500 hover:bg-red-500 hover:text-white font-medium py-1 px-2 rounded-lg transition text-sm" title="Delete"
                                           onclick="return confirm('Delete this user?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            
                            <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500">No users found</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                <div class="mt-6 flex justify-center">
                    <div class="flex space-x-2">
                        <?php if ($page > 1): ?>
                        <a href="<?php echo BASE_URL; ?>admin/manage_users.php?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" 
                           class="w-10 h-10 flex items-center justify-center border-2 border-gray-300 hover:border-primary-500 hover:text-primary-500 rounded-lg transition">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="<?php echo BASE_URL; ?>admin/manage_users.php?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" 
                           class="w-10 h-10 flex items-center justify-center rounded-lg transition <?php echo $i == $page ? 'bg-primary-500 text-white' : 'border-2 border-gray-300 hover:border-primary-500 hover:text-primary-500'; ?>">
                            <?php echo $i; ?>
                        </a>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                        <a href="<?php echo BASE_URL; ?>admin/manage_users.php?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" 
                           class="w-10 h-10 flex items-center justify-center border-2 border-gray-300 hover:border-primary-500 hover:text-primary-500 rounded-lg transition">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
