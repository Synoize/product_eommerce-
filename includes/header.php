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
    <link rel="icon" href="<?= ASSETS_URL; ?>/public/favicon.ico">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

    <!-- Tailwind Config -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            DEFAULT: '#2E7D32',
                            50: '#e8f5e9',
                            100: '#c8e6c9',
                            200: '#a5d6a7',
                            300: '#81c784',
                            400: '#66bb6a',
                            500: '#4caf50',
                            600: '#43a047',
                            700: '#388e3c',
                            800: '#2e7d32',
                            900: '#1b5e20',
                        },
                        accent: {
                            DEFAULT: '#FBC02D',
                            50: '#fffde7',
                            100: '#fff9c4',
                            200: '#fff59d',
                            300: '#fff176',
                            400: '#ffee58',
                            500: '#ffeb3b',
                            600: '#fdd835',
                            700: '#fbc02d',
                            800: '#f9a825',
                            900: '#f57f17',
                        },
                        spice: {
                            DEFAULT: '#D84315',
                            500: '#D84315',
                            600: '#BF360C',
                        },
                        neutral: {
                            light: '#F9FBF7',
                            dark: '#1F2937',
                        }
                    },

                    fontFamily: {
                        sans: ['Poppins', 'sans-serif'],
                    }






                    // colors: {
                    //     primary: {
                    //         DEFAULT: '#f84183',
                    //         50: '#fef1f5',
                    //         100: '#fde4ec',
                    //         200: '#fdcde0',
                    //         300: '#fca6c6',
                    //         400: '#f970a2',
                    //         500: '#f84183',
                    //         600: '#e91e63',
                    //         700: '#c2185b',
                    //         800: '#9d174d',
                    //         900: '#831843',
                    //     },
                    //     secondary: {
                    //         DEFAULT: '#6366f1',
                    //         50: '#eef2ff',
                    //         100: '#e0e7ff',
                    //         500: '#6366f1',
                    //         600: '#4f46e5',
                    //     }
                    // },
                    // fontFamily: {
                    //     sans: ['Inter', 'sans-serif'],
                    // }
                }
            }
        }
    </script>

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>

<body>

    <!-- Flash Messages -->
    <?php $flash = getFlash();
    if ($flash): ?>
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
        <script>
            setTimeout(() => document.getElementById('flashMessage')?.remove(), 5000);
        </script>
    <?php endif; ?>

    <!-- Navigation -->
    <nav class="fixed top-0 left-0 right-0 bg-white shadow-sm z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <!-- Mobile Menu Button -->
                <button id="mobileMenuBtn" class="md:hidden text-gray-400 hover:text-gray-500 text-xl">
                    <i class="fas fa-bars"></i>
                </button>

                <!-- Logo -->
                <a href="<?php echo BASE_URL; ?>" class="flex items-center text-primary-500 font-bold text-xl">
                    <img src="<?php echo ASSETS_URL; ?>/public/logo.png" alt="logo" class="h-20">
                </a>

                <!-- Desktop Navigation -->
                <div class="hidden md:flex items-center gap-8">
                    <a href="<?php echo BASE_URL; ?>" class="text-gray-700 hover:text-primary-500 font-medium <?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'text-primary-500' : ''; ?>">
                        Home
                    </a>
                    <a href="<?php echo BASE_URL; ?>." class="text-gray-700 hover:text-primary-500 font-medium <?php echo basename($_SERVER['PHP_SELF']) === 'shop.php' ? 'text-primary-500' : ''; ?>">
                        Shop
                    </a>

                    <!-- Categories Dropdown -->
                    <div class="relative group">

                        <!-- Button -->
                        <button class="text-gray-700 hover:text-primary-600 font-medium flex items-center gap-1">
                            Categories
                            <i class="fas fa-chevron-down text-xs transition-transform duration-300 group-hover:rotate-180"></i>
                        </button>

                        <!-- Dropdown -->
                        <div class="
        absolute left-0 mt-3 w-54
        bg-white rounded-lg border border-gray-100
        
        opacity-0 invisible translate-y-3
        transition-all duration-300 ease-out
        
        group-hover:opacity-100 
        group-hover:visible 
        group-hover:translate-y-0
    ">

                            <div class="py-2">
                                <?php foreach ($categories as $category): ?>
                                    <a
                                        href="<?php echo BASE_URL; ?>shop.php?category=<?php echo $category['id']; ?>"
                                        class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-50 hover:text-primary-600 transition">
                                        <?php if ($category['image']): ?>
                                            <img
                                                src="<?php echo getImageUrl($category['image'], 'categories'); ?>"
                                                class="w-6 h-6 rounded-full mr-3 object-cover">
                                        <?php endif; ?>

                                        <span class="text-sm font-medium">
                                            <?php echo e($category['name']); ?>
                                        </span>
                                    </a>
                                <?php endforeach; ?>
                            </div>

                        </div>
                    </div>

                    <a href="<?php echo BASE_URL; ?>about-us.php" class="text-gray-700 hover:text-primary-500 font-medium <?php echo basename($_SERVER['PHP_SELF']) === 'about-us.php' ? 'text-primary-500' : ''; ?>">
                        About
                    </a>
                    <a href="<?php echo BASE_URL; ?>contact-us.php" class="text-gray-700 hover:text-primary-500 font-medium <?php echo basename($_SERVER['PHP_SELF']) === 'contact-us.php' ? 'text-primary-500' : ''; ?>">
                        Contact
                    </a>
                </div>

                <!-- Right Side Actions -->
                <div class="flex items-center space-x-5">
                    <!-- Search -->
                    <form action="<?php echo BASE_URL; ?>shop.php" method="GET" class="hidden md:flex items-center">

                        <div class="
        flex items-center w-64 
        bg-gray-100 border border-slate-100 
        rounded-full px-2 py-1
        focus-within:bg-white
        focus-within:border-primary-400
        transition-all duration-300
    ">

                            <!-- Input -->
                            <input
                                type="search"
                                name="search"
                                placeholder="Search makhana, spices..."
                                value="<?php echo isset($_GET['search']) ? e($_GET['search']) : ''; ?>"

                                class="
            flex-1 px-3 py-2 
            bg-transparent text-sm text-gray-700
            placeholder-gray-400
            outline-none
            ">

                            <!-- Button -->
                            <button
                                type="submit"
                                class="
            h-9 w-9 flex items-center justify-center
            
            bg-primary-500 text-white rounded-full
            hover:bg-primary-600
            shadow-sm hover:shadow-md
            transition-all duration-200
            ">
                                <i class="fas fa-search text-sm"></i>
                            </button>

                        </div>

                    </form>

                    <!-- Cart -->
                    <a href="<?php echo BASE_URL; ?>cart.php"
                        class="relative text-gray-400 hover:text-gray-500 transition">

                        <i class="fas fa-shopping-cart text-xl"></i>

                        <?php if ($cartCount > 0): ?>
                            <span class="
                absolute -top-1.5 -right-1.5 
                bg-accent-600 text-black 
                text-[10px] font-semibold 
                rounded-full h-5 min-w-[20px] px-1
                flex items-center justify-center
                shadow
            ">
                                <?php echo $cartCount; ?>
                            </span>
                        <?php endif; ?>
                    </a>

                    <!-- User Account -->
                    <?php if (isLoggedIn()): ?>
                        <div class="relative group hidden md:block">

                            <!-- User Button -->
                            <button class="flex items-center gap-2 text-gray-700 hover:text-primary-600 transition">

                                <img
                                    src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['user_name'] ?? 'User'); ?>&background=66bb6a&color=fff"
                                    class="w-9 h-9 rounded-full object-cover border border-gray-200">

                                <span class="hidden sm:block text-sm font-medium">
                                    <?php echo e($_SESSION['user_name'] ?? 'User'); ?>
                                </span>

                                <i class="fas fa-chevron-down text-xs transition-transform duration-300 group-hover:rotate-180"></i>
                            </button>

                            <!-- Dropdown -->
                            <div class="
                absolute right-0 mt-3 w-52 
                bg-white rounded-lg border border-gray-100
                
                opacity-0 invisible translate-y-3
                transition-all duration-300 ease-out
                
                group-hover:opacity-100 
                group-hover:visible 
                group-hover:translate-y-0
            ">

                                <div class="py-2 text-sm">

                                    <a href="<?php echo BASE_URL; ?>user/profile.php"
                                        class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-50 hover:text-primary-600 transition">
                                        <i class="fas fa-user mr-3"></i> My Profile
                                    </a>

                                    <a href="<?php echo BASE_URL; ?>user/addresses.php"
                                        class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-50 hover:text-primary-600 transition">
                                        <i class="fas fa-map-marker-alt mr-3"></i> Addresses
                                    </a>

                                    <a href="<?php echo BASE_URL; ?>user/wishlist.php"
                                        class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-50 hover:text-primary-600 transition">
                                        <i class="fas fa-heart mr-3"></i> Wishlist
                                    </a>

                                    <a href="<?php echo BASE_URL; ?>user/orders.php"
                                        class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-50 hover:text-primary-600 transition">
                                        <i class="fas fa-box mr-3"></i> My Orders
                                    </a>

                                    <a href="<?php echo BASE_URL; ?>checkout.php"
                                        class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-50 hover:text-primary-600 transition">
                                        <i class="fas fa-shopping-cart mr-3"></i> Checkout
                                    </a>

                                    <a href="<?php echo BASE_URL; ?>help.php"
                                        class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-50 hover:text-primary-600 transition">
                                        <i class="fas fa-question-circle mr-3"></i> Help & Support
                                    </a>

                                    <div class="border-t border-gray-100 my-1"></div>

                                    <a href="<?php echo BASE_URL; ?>user/logout.php"
                                        class="flex items-center px-4 py-2 text-red-500 hover:bg-red-50 transition">
                                        <i class="fas fa-sign-out-alt mr-3"></i> Logout
                                    </a>

                                </div>
                            </div>
                        </div>

                    <?php else: ?>

                        <!-- Login Button -->
                        <a href="<?php echo BASE_URL; ?>user/login.php"
                            class="
           bg-primary-500 hover:bg-primary-600 
           text-white px-5 py-2.5 rounded-full 
           text-sm font-medium 
           shadow-sm hover:shadow-md
           transition-all
           "> Register/Login
                        </a>

                    <?php endif; ?>
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