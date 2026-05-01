<?php
/**
 * Create Razorpay Order Endpoint
 * AJAX endpoint for creating Razorpay order
 */

require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/razorpay.php';

// Set JSON header
header('Content-Type: application/json');

// Require login
if (!isLoggedIn()) {
    echo json_encode(['error' => 'Please login to continue']);
    exit;
}

// Validate request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request method']);
    exit;
}

// Get amount from session (never trust frontend)
if (!isset($_SESSION['checkout_data'])) {
    echo json_encode(['error' => 'Checkout data not found']);
    exit;
}

$amount = $_SESSION['checkout_data']['total'] ?? 0;

if ($amount <= 0) {
    echo json_encode(['error' => 'Invalid order amount']);
    exit;
}

// Create Razorpay order
try {
    $userId = $_SESSION['user_id'];
    $orderReceipt = 'order_' . time() . '_' . $userId;
    
    $razorpayOrder = createRazorpayOrder($amount, $orderReceipt);
    
    if ($razorpayOrder && isset($razorpayOrder['id'])) {
        // Store order ID in session
        $_SESSION['razorpay_order_id'] = $razorpayOrder['id'];
        
        echo json_encode([
            'success' => true,
            'razorpay_order_id' => $razorpayOrder['id'],
            'amount' => $amount * 100, // Amount in paise
            'currency' => RAZORPAY_CURRENCY,
            'key_id' => RAZORPAY_KEY_ID
        ]);
    } else {
        echo json_encode(['error' => 'Failed to create Razorpay order']);
    }
} catch (Exception $e) {
    error_log('Razorpay Create Order Error: ' . $e->getMessage());
    echo json_encode(['error' => 'Payment initialization failed: ' . $e->getMessage()]);
}
