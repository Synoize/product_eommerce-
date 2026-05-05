<?php
/**
 * Database Configuration
 * Core PHP eCommerce Project
 */

// Database credentials
define('DB_HOST', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'ecommerce_db');

// Base URL Configuration
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];
$folder = '/kd/';
define('BASE_URL', $protocol . $host . $folder);

// Asset URLs
define('ASSETS_URL', BASE_URL . 'assets/');
define('PUBLIC_URL', ASSETS_URL . 'public/');
define('IMAGES_URL', ASSETS_URL . 'images/');
define('PRODUCTS_URL', IMAGES_URL . 'products/');
define('CATEGORIES_URL', IMAGES_URL . 'categories/');
define('UPLOADS_URL', IMAGES_URL . 'uploads/');

// Physical Paths
define('ROOT_PATH', dirname(__DIR__) . '/');
define('ASSETS_PATH', ROOT_PATH . 'assets/');
define('IMAGES_PATH', ASSETS_PATH . 'images/');
define('PRODUCTS_PATH', IMAGES_PATH . 'products/');
define('CATEGORIES_PATH', IMAGES_PATH . 'categories/');
define('UPLOADS_PATH', IMAGES_PATH . 'uploads/');

// Create database connection using PDO
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    $pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD, $options);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Helper function to get PDO instance
function getDB() {
    global $pdo;
    return $pdo;
}

// Security helper function
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Redirect helper
function redirect($url) {
    if (!headers_sent()) {
        header("Location: " . $url);
        exit();
    }

    $safeUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
    echo '<script>window.location.href="' . $safeUrl . '";</script>';
    echo '<noscript><meta http-equiv="refresh" content="0;url=' . $safeUrl . '" /></noscript>';
    exit();
}

// Flash message helper
function setFlash($message, $type = 'success') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
}

function getFlash() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? 'success';
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
        return ['message' => $message, 'type' => $type];
    }
    return null;
}

// Format currency
function formatCurrency($amount) {
    return '₹' . number_format($amount, 2);
}

// Image URL helper
function getImageUrl($image, $type = 'products') {
    if (empty($image)) {
        return ASSETS_URL . 'images/placeholder.png';
    }
    
    // Check if external URL
    if (strpos($image, 'http') === 0) {
        return $image;
    }
    
    // Local path
    switch($type) {
        case 'products':
            return PRODUCTS_URL . $image;
        case 'categories':
            return CATEGORIES_URL . $image;
        case 'uploads':
            return UPLOADS_URL . $image;
        default:
            return ASSETS_URL . 'images/' . $image;
    }
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Check if user is admin
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// Require admin access
function requireAdmin() {
    if (!isAdmin()) {
        setFlash('Access denied. Admin privileges required.', 'danger');
        redirect(BASE_URL . 'user/login.php');
    }
}

// Require login
function requireLogin() {
    if (!isLoggedIn()) {
        setFlash('Please login to continue.', 'warning');
        redirect(BASE_URL . 'user/login.php');
    }
}

// Wishlist helper functions
function isInWishlist($productId) {
    if (!isLoggedIn()) {
        return false;
    }
    
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM wishlist WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$_SESSION['user_id'], $productId]);
        return $stmt->fetch()['count'] > 0;
    } catch (PDOException $e) {
        return false;
    }
}

function addToWishlist($productId) {
    if (!isLoggedIn()) {
        return false;
    }
    
    global $pdo;
    try {
        $stmt = $pdo->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE created_at = NOW()");
        $stmt->execute([$_SESSION['user_id'], $productId]);
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

function removeFromWishlist($productId) {
    if (!isLoggedIn()) {
        return false;
    }
    
    global $pdo;
    try {
        $stmt = $pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$_SESSION['user_id'], $productId]);
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

function getWishlistCount() {
    if (!isLoggedIn()) {
        return 0;
    }
    
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM wishlist WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch()['count'];
    } catch (PDOException $e) {
        return 0;
    }
}

function getCurrentPageUrl() {
    if (empty($_SERVER['HTTP_HOST'])) {
        return BASE_URL;
    }

    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';

    return $protocol . $_SERVER['HTTP_HOST'] . $requestUri;
}

function renderWishlistIconButton($productId, $formClass = '', $buttonClass = '') {
    $productId = (int)$productId;
    $inWishlist = isInWishlist($productId);
    $action = $inWishlist ? 'remove' : 'add';
    $label = $inWishlist ? 'Remove from wishlist' : 'Add to wishlist';
    $iconClass = $inWishlist ? 'fas' : 'far';
    $stateClass = $inWishlist
        ? 'bg-red-500 text-white hover:bg-red-600'
        : 'bg-white/95 text-gray-600 hover:bg-white hover:text-red-500';
    $classes = trim($stateClass . ' w-8 h-8 rounded-full flex items-center justify-center shadow-md hover:shadow-lg transition ' . $buttonClass);
    ?>
    <form action="<?php echo BASE_URL; ?>wishlist_action.php" method="POST" class="<?php echo e($formClass); ?>">
        <input type="hidden" name="product_id" value="<?php echo $productId; ?>">
        <input type="hidden" name="wishlist_action" value="<?php echo $action; ?>">
        <input type="hidden" name="redirect_url" value="<?php echo e(getCurrentPageUrl()); ?>">
        <button type="submit" class="<?php echo e($classes); ?>" title="<?php echo e($label); ?>" aria-label="<?php echo e($label); ?>">
            <i class="<?php echo $iconClass; ?> fa-heart"></i>
        </button>
    </form>
    <?php
}


function  getOrderCount() {
    if (!isLoggedIn()) {
        return 0;
    }
    
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM orders WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch()['count'];
    } catch (PDOException $e) {
        return 0;
    }
}
