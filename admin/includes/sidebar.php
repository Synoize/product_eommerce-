<div class=" hidden md:flex flex-col justify-between w-64 bg-gray-900 text-white">
    <div class="p-6">
        <h3 class="text-xl font-bold">Admin Panel</h3>
    </div>
    <nav class="px-4 space-y-2 flex-1 overflow-y-auto">
        <a href="<?php echo BASE_URL; ?>admin/index.php" class="flex items-center px-4 py-3 bg-primary-500 rounded-lg text-white">
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