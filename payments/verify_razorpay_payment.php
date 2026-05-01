<?php
/**
 * Razorpay Payment Verification
 * Verifies payment and creates order
 */

require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/razorpay.php';

// Require login
if (!isLoggedIn()) {
    setFlash('Please login to continue.', 'warning');
    redirect(BASE_URL . 'user/login.php');
}

// Check for required data
if (!isset($_SESSION['checkout_data']) || !isset($_SESSION['razorpay_order_id'])) {
    setFlash('Checkout data not found. Please try again.', 'danger');
    redirect(BASE_URL . 'checkout.php');
}

// Verify POST data
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setFlash('Invalid request method.', 'danger');
    redirect(BASE_URL . 'checkout.php');
}

$razorpayPaymentId = isset($_POST['razorpay_payment_id']) ? trim($_POST['razorpay_payment_id']) : '';
$razorpayOrderId = isset($_POST['razorpay_order_id']) ? trim($_POST['razorpay_order_id']) : '';
$razorpaySignature = isset($_POST['razorpay_signature']) ? trim($_POST['razorpay_signature']) : '';

// Validate required fields
if (empty($razorpayPaymentId) || empty($razorpayOrderId) || empty($razorpaySignature)) {
    setFlash('Payment verification failed: Missing payment data.', 'danger');
    redirect(BASE_URL . 'checkout.php');
}

// Verify signature
try {
    $isValid = verifyRazorpaySignature($razorpayOrderId, $razorpayPaymentId, $razorpaySignature);
    
    if (!$isValid) {
        setFlash('Payment verification failed: Invalid signature.', 'danger');
        error_log('Razorpay Signature Verification Failed: Order=' . $razorpayOrderId . ', Payment=' . $razorpayPaymentId);
        redirect(BASE_URL . 'checkout.php');
    }
    
    // Signature verified - create order
    $checkoutData = $_SESSION['checkout_data'];
    $userId = $_SESSION['user_id'];
    $cart = $_SESSION['cart'];
    
    try {
        // Start transaction
        $pdo->beginTransaction();
        
        // Insert order
        $orderQuery = "INSERT INTO orders (user_id, total_amount, discount_amount, coupon_code, status, 
                       payment_method, payment_status, razorpay_payment_id, razorpay_order_id,
                       name, email, mobile, address, city, state, pincode, created_at) 
                       VALUES (?, ?, ?, ?, 'pending', 'razorpay', 'paid', ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $orderStmt = $pdo->prepare($orderQuery);
        $orderStmt->execute([
            $userId,
            $checkoutData['total'],
            $checkoutData['discount'],
            $checkoutData['coupon_code'],
            $razorpayPaymentId,
            $razorpayOrderId,
            $checkoutData['name'],
            $checkoutData['email'],
            $checkoutData['mobile'],
            $checkoutData['address'],
            $checkoutData['city'],
            $checkoutData['state'],
            $checkoutData['pincode']
        ]);
        
        $orderId = $pdo->lastInsertId();
        
        // Insert order items and update stock
        $itemQuery = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
        $itemStmt = $pdo->prepare($itemQuery);
        
        $stockQuery = "UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?";
        $stockStmt = $pdo->prepare($stockQuery);
        
        foreach ($cart as $productId => $item) {
            // Insert order item
            $itemStmt->execute([
                $orderId,
                $productId,
                $item['quantity'],
                $item['price']
            ]);
            
            // Decrement stock
            $stockStmt->execute([
                $item['quantity'],
                $productId,
                $item['quantity']
            ]);
            
            // Check if stock update succeeded
            if ($stockStmt->rowCount() === 0) {
                throw new Exception('Insufficient stock for product: ' . $item['name']);
            }
        }
        
        // Update coupon usage count if coupon was used
        if (!empty($checkoutData['coupon_code'])) {
            $couponUpdateQuery = "UPDATE coupons SET used_count = used_count + 1 WHERE code = ?";
            $couponStmt = $pdo->prepare($couponUpdateQuery);
            $couponStmt->execute([$checkoutData['coupon_code']]);
        }
        
        // Commit transaction
        $pdo->commit();
        
        // Clear cart and checkout data
        unset($_SESSION['cart']);
        unset($_SESSION['coupon']);
        unset($_SESSION['checkout_data']);
        unset($_SESSION['razorpay_order_id']);
        
        // Store order ID for success page
        $_SESSION['last_order_id'] = $orderId;
        
        // Redirect to success page
        setFlash('Order placed successfully! Thank you for your purchase.', 'success');
        redirect(BASE_URL . 'order-success.php');
        
    } catch (Exception $e) {
        // Rollback on error
        $pdo->rollBack();
        error_log('Order Creation Error: ' . $e->getMessage());
        setFlash('Error creating order: ' . $e->getMessage(), 'danger');
        redirect(BASE_URL . 'checkout.php');
    }
    
} catch (Exception $e) {
    error_log('Razorpay Verification Error: ' . $e->getMessage());
    setFlash('Payment verification error: ' . $e->getMessage(), 'danger');
    redirect(BASE_URL . 'checkout.php');
}
