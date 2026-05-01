<?php

/**
 * Header Template
 * Included on all frontend pages
 */

// Ensure session and database are loaded
require_once __DIR__ . '/../../includes/db_connect.php';

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

    <!-- Navigation -->
    <nav class="fixed top-0 left-0 right-0 bg-slate-50 shadow-sm z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <!-- Logo -->
                <a href="<?php echo BASE_URL; ?>" class="flex items-center text-primary-500 font-bold text-xl">
                    <img src="<?php echo ASSETS_URL; ?>/public/logo.png" alt="logo" class="h-20">
                </a>

                <!-- Right Side Actions -->
                <div class="flex items-center space-x-4">
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

                </div>
            </div>
        </div>

    </nav>