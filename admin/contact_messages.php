<?php
/**
 * Admin - Contact Messages
 */

$pageTitle = 'Contact Messages';
require_once __DIR__ . '/../includes/header.php';
requireAdmin();

// Check if table exists, create if not
try {
    $pdo->query("SELECT 1 FROM contact_messages LIMIT 1");
} catch (PDOException $e) {
    // Table doesn't exist - show message but don't crash
    $tableExists = false;
}

// Fetch messages
$messages = [];
if (isset($tableExists) && $tableExists === false) {
    // Table doesn't exist
} else {
    try {
        $stmt = $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC");
        $messages = $stmt->fetchAll();
    } catch (PDOException $e) {
        // Silent fail
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
                <a href="<?php echo BASE_URL; ?>admin/manage_products.php" class="flex items-center px-4 py-3 text-gray-300 hover:bg-gray-800 rounded-lg transition">
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
                <a href="<?php echo BASE_URL; ?>admin/contact_messages.php" class="flex items-center px-4 py-3 bg-primary-500 rounded-lg text-white">
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
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Contact Messages</h2>
            
            <?php if (isset($tableExists) && $tableExists === false): ?>
            <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded-lg mb-6 flex items-center">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                The contact_messages table doesn't exist yet. Please run the database setup SQL to create it.
            </div>
            <?php endif; ?>
            
            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">ID</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Name</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Contact</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Message</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($messages as $message): ?>
                            <tr>
                                <td class="px-4 py-3 text-sm"><?php echo $message['id']; ?></td>
                                <td class="px-4 py-3 text-sm font-medium text-gray-900"><?php echo e($message['name']); ?></td>
                                <td class="px-4 py-3">
                                    <div class="text-sm text-gray-900"><?php echo e($message['email']); ?></div>
                                    <div class="text-sm text-gray-500"><?php echo e($message['phone']); ?></div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="max-w-xs truncate text-sm text-gray-600" title="<?php echo e($message['message']); ?>">
                                        <?php echo e($message['message']); ?>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500"><?php echo date('M d, Y H:i', strtotime($message['created_at'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                            
                            <?php if (empty($messages)): ?>
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-gray-500">No messages yet</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
