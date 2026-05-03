<?php
/**
 * Wishlist Action Handler
 */

require_once __DIR__ . '/includes/db_connect.php';

function getWishlistRedirectUrl($fallback) {
    $redirect = $_POST['redirect_url'] ?? ($_SERVER['HTTP_REFERER'] ?? $fallback);
    $appBase = rtrim(BASE_URL, '/') . '/';

    if (strpos($redirect, $appBase) === 0) {
        return $redirect;
    }

    return $fallback;
}

$fallbackUrl = BASE_URL . 'shop.php';
$redirectUrl = getWishlistRedirectUrl($fallbackUrl);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect($fallbackUrl);
}

$productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$action = $_POST['wishlist_action'] ?? '';

if ($productId <= 0 || !in_array($action, ['add', 'remove'], true)) {
    setFlash('Invalid wishlist request.', 'danger');
    redirect($redirectUrl);
}

if (!isLoggedIn()) {
    $_SESSION['redirect_after_login'] = $redirectUrl;
    setFlash('Please login to manage your wishlist.', 'warning');
    redirect(BASE_URL . 'user/login.php');
}

try {
    $stmt = $pdo->prepare("SELECT id FROM products WHERE id = ? AND status = 1");
    $stmt->execute([$productId]);

    if (!$stmt->fetch()) {
        setFlash('Product not found.', 'danger');
        redirect($redirectUrl);
    }
} catch (PDOException $e) {
    setFlash('Error loading product.', 'danger');
    redirect($redirectUrl);
}

if ($action === 'add') {
    if (addToWishlist($productId)) {
        setFlash('Added to wishlist!', 'success');
    } else {
        setFlash('Error adding to wishlist.', 'danger');
    }
} else {
    if (removeFromWishlist($productId)) {
        setFlash('Removed from wishlist.', 'success');
    } else {
        setFlash('Error removing from wishlist.', 'danger');
    }
}

redirect($redirectUrl);
