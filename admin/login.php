<?php
/**
 * Admin Login Page
 */

require_once __DIR__ . '/../includes/db_connect.php';

// Redirect if already logged in as admin
if (isAdmin()) {
    redirect(BASE_URL . 'admin/index.php');
}

// Redirect regular users to home
if (isLoggedIn() && !isAdmin()) {
    setFlash('Access denied. Admin privileges required.', 'danger');
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
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = 'admin' AND status = 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                // Set session data
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                
                setFlash('Welcome back, Admin!', 'success');
                redirect(BASE_URL . 'admin/index.php');
                exit; // Stop execution after redirect
            } else {
                $errors[] = 'Invalid admin credentials';
            }
        } catch (PDOException $e) {
            $errors[] = 'Login failed. Please try again.';
        }
    }
}

$pageTitle = 'Admin Login';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="fas fa-user-shield fa-3x text-primary mb-3"></i>
                        <h3 class="fw-bold">Admin Login</h3>
                        <p class="text-muted">Access the admin panel</p>
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
                    
                    <form action="<?php echo BASE_URL; ?>admin/login.php" method="POST">
                        <div class="form-outline mb-4">
                            <input type="email" name="email" class="form-control" required>
                            <label class="form-label">Admin Email</label>
                        </div>
                        
                        <div class="form-outline mb-4">
                            <input type="password" name="password" class="form-control" required>
                            <label class="form-label">Password</label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-block btn-lg mb-4">
                            <i class="fas fa-sign-in-alt me-2"></i>Admin Sign In
                        </button>
                    </form>
                    
                    <div class="text-center">
                        <a href="<?php echo BASE_URL; ?>" class="text-muted">
                            <i class="fas fa-arrow-left me-2"></i>Back to Website
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
