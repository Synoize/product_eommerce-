<?php
/**
 * Header Template
 * Included on all frontend pages
 */

// Ensure session and database are loaded
require_once __DIR__ . '/db_connect.php';

// Get cart count
$cartCount = 0;
if (isset($_SESSION['cart'])) {
    $cartCount = array_sum(array_column($_SESSION['cart'], 'quantity'));
}

// Get all categories for navigation
$categories = [];
try {
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    // Categories table might not exist yet
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? e($pageTitle) . ' - ' : ''; ?>WebStore</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Tailwind Config -->
    <script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    primary: {
                        DEFAULT: '#f84183',
                        50: '#fef1f5',
                        100: '#fde4ec',
                        200: '#fdcde0',
                        300: '#fca6c6',
                        400: '#f970a2',
                        500: '#f84183',
                        600: '#e91e63',
                        700: '#c2185b',
                        800: '#9d174d',
                        900: '#831843',
                    },
                    secondary: {
                        DEFAULT: '#6366f1',
                        50: '#eef2ff',
                        100: '#e0e7ff',
                        500: '#6366f1',
                        600: '#4f46e5',
                    }
                },
                fontFamily: {
                    sans: ['Inter', 'sans-serif'],
                }
            }
        }
    }
    </script>
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body>

<!-- Flash Messages -->
<?php $flash = getFlash(); if ($flash): ?>
<?php 
$alertColors = [
    'success' => 'bg-green-100 border-green-400 text-green-700',
    'danger' => 'bg-red-100 border-red-400 text-red-700',
    'warning' => 'bg-yellow-100 border-yellow-400 text-yellow-700',
    'info' => 'bg-blue-100 border-blue-400 text-blue-700'
];
$alertClass = $alertColors[$flash['type']] ?? $alertColors['info'];
?>
<div class="fixed top-20 left-1/2 transform -translate-x-1/2 z-50 px-4 py-3 rounded border <?php echo $alertClass; ?> shadow-lg max-w-md w-full mx-4" role="alert" id="flashMessage">
    <div class="flex justify-between items-center">
        <span><?php echo e($flash['message']); ?></span>
        <button type="button" onclick="document.getElementById('flashMessage').remove()" class="text-current hover:opacity-75">
            <i class="fas fa-times"></i>
        </button>
    </div>
</div>
<script>setTimeout(() => document.getElementById('flashMessage')?.remove(), 5000);</script>
<?php endif; ?>

<!-- Navigation -->
<nav class="fixed top-0 left-0 right-0 bg-white shadow-md z-40">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- Logo -->
            <a href="<?php echo BASE_URL; ?>" class="flex items-center text-primary-500 font-bold text-xl">
                <i class="fas fa-shopping-bag mr-2"></i>
                <span>WebStore</span>
            </a>
            
            <!-- Desktop Navigation -->
            <div class="hidden md:flex items-center space-x-8">
                <a href="<?php echo BASE_URL; ?>" class="text-gray-700 hover:text-primary-500 font-medium <?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'text-primary-500' : ''; ?>">
                    <i class="fas fa-home mr-1"></i>Home
                </a>
                <a href="<?php echo BASE_URL; ?>shop.php" class="text-gray-700 hover:text-primary-500 font-medium <?php echo basename($_SERVER['PHP_SELF']) === 'shop.php' ? 'text-primary-500' : ''; ?>">
                    <i class="fas fa-store mr-1"></i>Shop
                </a>
                
                <!-- Categories Dropdown -->
                <div class="relative group">
                    <button class="text-gray-700 hover:text-primary-500 font-medium flex items-center">
                        <i class="fas fa-th-large mr-1"></i>Categories
                        <i class="fas fa-chevron-down ml-1 text-sm"></i>
                    </button>
                    <div class="absolute top-full left-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-100 hidden group-hover:block">
                        <?php foreach ($categories as $category): ?>
                        <a href="<?php echo BASE_URL; ?>shop.php?category=<?php echo $category['id']; ?>" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-50 hover:text-primary-500">
                            <?php if ($category['image']): ?>
                                <img src="<?php echo getImageUrl($category['image'], 'categories'); ?>" class="w-5 h-5 rounded-full mr-2">
                            <?php endif; ?>
                            <?php echo e($category['name']); ?>
                        </a>
                        <?php endforeach; ?>
                        <div class="border-t border-gray-100"></div>
                        <a href="<?php echo BASE_URL; ?>shop.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-50 hover:text-primary-500">All Categories</a>
                    </div>
                </div>
                
                <a href="<?php echo BASE_URL; ?>about-us.php" class="text-gray-700 hover:text-primary-500 font-medium <?php echo basename($_SERVER['PHP_SELF']) === 'about-us.php' ? 'text-primary-500' : ''; ?>">
                    <i class="fas fa-info-circle mr-1"></i>About
                </a>
                <a href="<?php echo BASE_URL; ?>contact-us.php" class="text-gray-700 hover:text-primary-500 font-medium <?php echo basename($_SERVER['PHP_SELF']) === 'contact-us.php' ? 'text-primary-500' : ''; ?>">
                    <i class="fas fa-envelope mr-1"></i>Contact
                </a>
            </div>
            
            <!-- Right Side Actions -->
            <div class="flex items-center space-x-4">
                <!-- Search -->
                <form action="<?php echo BASE_URL; ?>shop.php" method="GET" class="hidden md:flex items-center">
                    <div class="relative">
                        <input type="search" name="search" placeholder="Search..." 
                               value="<?php echo isset($_GET['search']) ? e($_GET['search']) : ''; ?>"
                               class="w-48 pl-4 pr-10 py-2 border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent text-sm">
                        <button type="submit" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-primary-500">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
                
                <!-- Cart -->
                <a href="<?php echo BASE_URL; ?>cart.php" class="relative text-gray-700 hover:text-primary-500">
                    <i class="fas fa-shopping-cart text-xl"></i>
                    <?php if ($cartCount > 0): ?>
                    <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center">
                        <?php echo $cartCount; ?>
                    </span>
                    <?php endif; ?>
                </a>
                
                <!-- User Account -->
                <?php if (isLoggedIn()): ?>
                <div class="relative group">
                    <button class="flex items-center text-gray-700 hover:text-primary-500">
                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['user_name'] ?? 'User'); ?>&background=f84183&color=fff" 
                             class="w-8 h-8 rounded-full mr-2">
                        <span class="hidden sm:block font-medium"><?php echo e($_SESSION['user_name'] ?? 'User'); ?></span>
                        <i class="fas fa-chevron-down ml-1 text-sm"></i>
                    </button>
                    <div class="absolute right-0 top-full mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-100 hidden group-hover:block">
                        <a href="<?php echo BASE_URL; ?>user/profile.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-50 hover:text-primary-500">
                            <i class="fas fa-user mr-2"></i>My Profile
                        </a>
                        <a href="<?php echo BASE_URL; ?>user/addresses.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-50 hover:text-primary-500">
                            <i class="fas fa-map-marker-alt mr-2"></i>Addresses
                        </a>
                        <a href="<?php echo BASE_URL; ?>user/orders.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-50 hover:text-primary-500">
                            <i class="fas fa-box mr-2"></i>My Orders
                        </a>
                        <div class="border-t border-gray-100"></div>
                        <a href="<?php echo BASE_URL; ?>user/logout.php" class="block px-4 py-2 text-red-600 hover:bg-gray-50">
                            <i class="fas fa-sign-out-alt mr-2"></i>Logout
                        </a>
                    </div>
                </div>
                <?php else: ?>
                <a href="<?php echo BASE_URL; ?>user/login.php" class="bg-primary-500 hover:bg-primary-600 text-white px-4 py-2 rounded-full font-medium text-sm transition">
                    <i class="fas fa-sign-in-alt mr-1"></i>Login
                </a>
                <?php endif; ?>
                
                <!-- Mobile Menu Button -->
                <button id="mobileMenuBtn" class="md:hidden text-gray-700 text-xl">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
    </div>
    
    <!-- Mobile Menu -->
    <div id="mobileMenu" class="hidden md:hidden bg-white border-t border-gray-100">
        <div class="px-4 py-2 space-y-2">
            <a href="<?php echo BASE_URL; ?>" class="block py-2 text-gray-700 hover:text-primary-500"><i class="fas fa-home mr-2"></i>Home</a>
            <a href="<?php echo BASE_URL; ?>shop.php" class="block py-2 text-gray-700 hover:text-primary-500"><i class="fas fa-store mr-2"></i>Shop</a>
            <a href="<?php echo BASE_URL; ?>about-us.php" class="block py-2 text-gray-700 hover:text-primary-500"><i class="fas fa-info-circle mr-2"></i>About</a>
            <a href="<?php echo BASE_URL; ?>contact-us.php" class="block py-2 text-gray-700 hover:text-primary-500"><i class="fas fa-envelope mr-2"></i>Contact</a>
        </div>
    </div>
</nav>

<!-- Spacer for fixed navbar -->
<div class="h-16"></div>

<script>
// Mobile menu toggle
document.getElementById('mobileMenuBtn')?.addEventListener('click', function() {
    document.getElementById('mobileMenu').classList.toggle('hidden');
});
</script>
