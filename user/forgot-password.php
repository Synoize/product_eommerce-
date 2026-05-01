<?php
/**
 * Forgot Password Page
 */

require_once __DIR__ . '/../includes/db_connect.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect(BASE_URL);
}

$message = '';
$success = false;
$resetLink = '';
$dbError = '';  // For debugging

// Process form BEFORE including header
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please enter a valid email address';
    } else {
        // Check if email exists
        try {
            $stmt = $pdo->prepare("SELECT id, name FROM users WHERE email = ? AND status = 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Generate reset token
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Save token to database
                $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?");
                $stmt->execute([$token, $expires, $user['id']]);
                
                // Create reset link
                $resetLink = BASE_URL . 'user/reset-password.php?token=' . $token;
                $success = true;
                $message = 'Password reset link generated! Click the link below to reset your password.';
            } else {
                $message = 'No account found with this email address';
            }
        } catch (PDOException $e) {
            $message = 'An error occurred. Please try again.';
            $dbError = $e->getMessage();  // Show actual error for debugging
        }
    }
}

$pageTitle = 'Forgot Password';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="min-h-screen bg-gradient-to-br from-pink-50 to-purple-50 py-12 px-4">
    <div class="max-w-md mx-auto">
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <div class="text-center mb-8">
                <div class="w-20 h-20 bg-primary-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-lock text-4xl text-primary-500"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-900">Forgot Password?</h3>
                <p class="text-gray-500">Enter your email and we'll send you reset instructions</p>
            </div>
            
            <?php if ($message): ?>
            <div class="px-4 py-3 rounded-lg mb-6 <?php echo $success ? 'bg-green-100 border border-green-400 text-green-700' : 'bg-red-100 border border-red-400 text-red-700'; ?>">
                <?php echo e($message); ?>
            </div>
            <?php endif; ?>
            
            <?php if ($success && $resetLink): ?>
            <!-- Since email is not configured, show the reset link directly -->
            <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded-lg mb-6">
                <p class="mb-2 font-medium">Your Reset Link:</p>
                <a href="<?php echo $resetLink; ?>" class="block w-full bg-green-500 hover:bg-green-600 text-white text-center font-semibold py-3 px-4 rounded-lg transition mb-2">
                    <i class="fas fa-link mr-2"></i>Click Here to Reset Password
                </a>
                <p class="text-sm">Or copy this link: <span class="break-all text-xs"><?php echo $resetLink; ?></span></p>
            </div>
            <?php endif; ?>
            
            <?php if ($dbError): ?>
            <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded-lg mb-6 text-sm">
                <strong>Database Error:</strong> <?php echo e($dbError); ?>
            </div>
            <?php endif; ?>
            
            <?php if (!$success): ?>
            <form action="<?php echo BASE_URL; ?>user/forgot-password.php" method="POST" class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <input type="email" name="email" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                </div>
                
                <button type="submit" class="w-full bg-primary-500 hover:bg-primary-600 text-white font-semibold py-4 rounded-full transition shadow-lg hover:shadow-xl">
                    <i class="fas fa-paper-plane mr-2"></i>Send Reset Link
                </button>
            </form>
            <?php endif; ?>
            
            <div class="text-center mt-6">
                <p class="text-gray-600">Remember your password? 
                    <a href="<?php echo BASE_URL; ?>user/login.php" class="font-semibold text-primary-500 hover:text-primary-600">Sign in</a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
