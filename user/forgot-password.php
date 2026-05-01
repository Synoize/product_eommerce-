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

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="fas fa-lock fa-3x text-primary mb-3"></i>
                        <h3 class="fw-bold">Forgot Password?</h3>
                        <p class="text-muted">Enter your email and we'll send you reset instructions</p>
                    </div>
                    
                    <?php if ($message): ?>
                    <div class="alert alert-<?php echo $success ? 'success' : 'danger'; ?>">
                        <?php echo e($message); ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($success && $resetLink): ?>
                    <!-- Since email is not configured, show the reset link directly -->
                    <div class="alert alert-info">
                        <p class="mb-2"><strong>Your Reset Link:</strong></p>
                        <a href="<?php echo $resetLink; ?>" class="btn btn-success w-100 mb-2">
                            <i class="fas fa-link me-2"></i>Click Here to Reset Password
                        </a>
                        <p class="small mb-0 text-muted">Or copy this link: <?php echo $resetLink; ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($dbError): ?>
                    <div class="alert alert-warning small">
                        <strong>Database Error:</strong> <?php echo e($dbError); ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!$success): ?>
                    <form action="<?php echo BASE_URL; ?>user/forgot-password.php" method="POST">
                        <div class="form-outline mb-4">
                            <input type="email" name="email" class="form-control" required>
                            <label class="form-label">Email Address</label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-block btn-lg mb-4">
                            <i class="fas fa-paper-plane me-2"></i>Send Reset Link
                        </button>
                    </form>
                    <?php endif; ?>
                    
                    <div class="text-center">
                        <p class="mb-0">Remember your password? 
                            <a href="<?php echo BASE_URL; ?>user/login.php" class="fw-bold text-primary">Sign in</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
