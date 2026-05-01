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
                <a class="nav-link" href="<?php echo BASE_URL; ?>admin/manage_users.php">
                    <i class="fas fa-users"></i>Users
                </a>
                <a class="nav-link active" href="<?php echo BASE_URL; ?>admin/contact_messages.php">
                    <i class="fas fa-envelope"></i>Messages
                </a>
                <a class="nav-link" href="<?php echo BASE_URL; ?>">
                    <i class="fas fa-arrow-left"></i>Back to Site
                </a>
            </nav>
        </div>
        
        <!-- Main Content -->
        <div class="col-md-10 p-4">
            <h2 class="fw-bold mb-4">Contact Messages</h2>
            
            <?php if (isset($tableExists) && $tableExists === false): ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                The contact_messages table doesn't exist yet. Please run the database setup SQL to create it.
            </div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead class="bg-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Contact</th>
                                    <th>Message</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($messages as $message): ?>
                                <tr>
                                    <td><?php echo $message['id']; ?></td>
                                    <td><strong><?php echo e($message['name']); ?></strong></td>
                                    <td>
                                        <div><?php echo e($message['email']); ?></div>
                                        <small class="text-muted"><?php echo e($message['phone']); ?></small>
                                    </td>
                                    <td>
                                        <div style="max-width: 400px;" class="text-truncate" 
                                             title="<?php echo e($message['message']); ?>">
                                            <?php echo e($message['message']); ?>
                                        </div>
                                    </td>
                                    <td><?php echo date('M d, Y H:i', strtotime($message['created_at'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                                
                                <?php if (empty($messages)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4">No messages yet</td>
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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
