<?php
/**
 * Admin - Contact Messages
 */

$pageTitle = 'Contact Messages';
require_once __DIR__ . '/includes/header.php';
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

<div class="bg-white mt-20">
    <div class="h-[calc(100vh-80px)] flex">
        <!-- Admin Sidebar -->
       <?php include __DIR__ . '/includes/sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="flex-1 overflow-y-auto p-6 md:p-8">
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
