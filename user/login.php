<?php
/**
 * User Login Page
 */

require_once __DIR__ . '/../includes/db_connect.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect(BASE_URL);
}

$errors = [];

// Process form BEFORE including header (to avoid headers already sent error)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    // Validation
    if (empty($email)) $errors[] = 'Email is required';
    if (empty($password)) $errors[] = 'Password is required';
    
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND status = 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                // Set session data
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                
                setFlash('Welcome back, ' . $user['name'] . '!', 'success');
                
                // Redirect to intended page or home
                $redirect = isset($_SESSION['redirect_after_login']) ? $_SESSION['redirect_after_login'] : BASE_URL;
                unset($_SESSION['redirect_after_login']);
                redirect($redirect);
                exit; // Stop execution after redirect
            } else {
                $errors[] = 'Invalid email or password';
            }
        } catch (PDOException $e) {
            $errors[] = 'Login failed. Please try again.';
            error_log('Login Error: ' . $e->getMessage());
        }
    }
}

$pageTitle = 'Login';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="fas fa-user-circle fa-3x text-primary mb-3"></i>
                        <h3 class="fw-bold">Welcome Back</h3>
                        <p class="text-muted">Sign in to your account</p>
                    </div>
                    
                    <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                            <li><?php echo e($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                    
                    <form action="<?php echo BASE_URL; ?>user/login.php" method="POST">
                        <div class="form-outline mb-4">
                            <input type="email" name="email" class="form-control" required 
                                   value="<?php echo isset($_POST['email']) ? e($_POST['email']) : ''; ?>">
                            <label class="form-label">Email Address</label>
                        </div>
                        
                        <div class="form-outline mb-4">
                            <input type="password" name="password" class="form-control" required>
                            <label class="form-label">Password</label>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="remember">
                                <label class="form-check-label" for="remember">Remember me</label>
                            </div>
                            <a href="<?php echo BASE_URL; ?>user/forgot-password.php" class="text-primary">Forgot password?</a>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-block btn-lg mb-4">
                            <i class="fas fa-sign-in-alt me-2"></i>Sign In
                        </button>
                    </form>
                    
                    <div class="text-center">
                        <p class="mb-0">Don't have an account? 
                            <a href="<?php echo BASE_URL; ?>user/signup.php" class="fw-bold text-primary">Sign up</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
