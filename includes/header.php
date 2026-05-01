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
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- MDB UI Kit CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.0/mdb.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="<?php echo ASSETS_URL; ?>css/style.css" rel="stylesheet">
</head>
<body>

<!-- Flash Messages -->
<?php $flash = getFlash(); if ($flash): ?>
<div class="flash-message alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show" role="alert">
    <?php echo e($flash['message']); ?>
    <button type="button" class="btn-close" data-mdb-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Navigation -->
<nav class="navbar navbar-expand-lg fixed-top">
    <div class="container">
        <!-- Logo -->
        <a class="navbar-brand" href="<?php echo BASE_URL; ?>">
            <?php 
            $logoPath = IMAGES_PATH . 'logo/logo.png';
            $logoUrl = IMAGES_URL . 'logo/logo.png';
            if (file_exists($logoPath)): 
            ?>
                <img src="<?php echo $logoUrl; ?>" alt="WebStore Logo">
            <?php else: ?>
                <i class="fas fa-shopping-bag me-2"></i>WebStore
            <?php endif; ?>
        </a>
        
        <!-- Mobile Toggle -->
        <button class="navbar-toggler" type="button" data-mdb-toggle="collapse" data-mdb-target="#navbarNav">
            <i class="fas fa-bars"></i>
        </button>
        
        <!-- Navigation Links -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>">
                        <i class="fas fa-home me-1"></i>Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'shop.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>shop.php">
                        <i class="fas fa-store me-1"></i>Shop
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="categoriesDropdown" role="button" data-mdb-toggle="dropdown">
                        <i class="fas fa-th-large me-1"></i>Categories
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="categoriesDropdown">
                        <?php foreach ($categories as $category): ?>
                        <li>
                            <a class="dropdown-item" href="<?php echo BASE_URL; ?>shop.php?category=<?php echo $category['id']; ?>">
                                <?php if ($category['image']): ?>
                                    <img src="<?php echo getImageUrl($category['image'], 'categories'); ?>" width="20" height="20" class="me-2 rounded-circle">
                                <?php endif; ?>
                                <?php echo e($category['name']); ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>shop.php">All Categories</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'about-us.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>about-us.php">
                        <i class="fas fa-info-circle me-1"></i>About Us
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'contact-us.php' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>contact-us.php">
                        <i class="fas fa-envelope me-1"></i>Contact Us
                    </a>
                </li>
            </ul>
            
            <!-- Right Side Actions -->
            <ul class="navbar-nav align-items-center">
                <!-- Search -->
                <li class="nav-item me-3">
                    <form class="d-flex" action="<?php echo BASE_URL; ?>shop.php" method="GET">
                        <div class="input-group">
                            <input type="search" name="search" class="form-control" placeholder="Search products..." value="<?php echo isset($_GET['search']) ? e($_GET['search']) : ''; ?>">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </li>
                
                <!-- Cart -->
                <li class="nav-item me-3">
                    <a class="nav-link position-relative" href="<?php echo BASE_URL; ?>cart.php">
                        <i class="fas fa-shopping-cart fa-lg"></i>
                        <?php if ($cartCount > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            <?php echo $cartCount; ?>
                        </span>
                        <?php endif; ?>
                    </a>
                </li>
                
                <!-- User Account -->
                <?php if (isLoggedIn()): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-mdb-toggle="dropdown">
                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['user_name'] ?? 'User'); ?>&background=f84183&color=fff" 
                             class="rounded-circle me-2" width="32" height="32" alt="User">
                        <span><?php echo e($_SESSION['user_name'] ?? 'User'); ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>user/profile.php">
                            <i class="fas fa-user me-2"></i>My Profile
                        </a></li>
                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>user/orders.php">
                            <i class="fas fa-box me-2"></i>My Orders
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="<?php echo BASE_URL; ?>user/logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a></li>
                    </ul>
                </li>
                <?php else: ?>
                <li class="nav-item">
                    <a class="btn btn-primary btn-sm" href="<?php echo BASE_URL; ?>user/login.php">
                        <i class="fas fa-sign-in-alt me-1"></i>Login
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Spacer for fixed navbar -->
<div style="height: 76px;"></div>
