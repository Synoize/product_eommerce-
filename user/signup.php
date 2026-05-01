<?php
/**
 * User Signup Page
 */

require_once __DIR__ . '/../includes/db_connect.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect(BASE_URL);
}

$errors = [];

// Process form BEFORE including header (to avoid headers already sent error)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $mobile = isset($_POST['mobile']) ? trim($_POST['mobile']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirmPassword = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    
    // Validation
    if (empty($name)) $errors[] = 'Name is required';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required';
    if (empty($mobile) || !preg_match('/^[0-9]{10}$/', $mobile)) $errors[] = 'Valid 10-digit mobile number is required';
    if (empty($password) || strlen($password) < 6) $errors[] = 'Password must be at least 6 characters';
    if ($password !== $confirmPassword) $errors[] = 'Passwords do not match';
    
    if (empty($errors)) {
        try {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $errors[] = 'Email already registered. Please login.';
            } else {
                // Hash password
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert user
                $stmt = $pdo->prepare("INSERT INTO users (name, email, mobile, password, role, status, created_at) 
                                      VALUES (?, ?, ?, ?, 'user', 1, NOW())");
                $stmt->execute([$name, $email, $mobile, $hashedPassword]);
                
                $userId = $pdo->lastInsertId();
                
                // Set session
                $_SESSION['user_id'] = $userId;
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
                $_SESSION['user_role'] = 'user';
                
                setFlash('Welcome to WebStore! Your account has been created.', 'success');
                redirect(BASE_URL);
                exit; // Stop execution after redirect
            }
        } catch (PDOException $e) {
            $errors[] = 'Registration failed. Please try again.';
            error_log('Signup Error: ' . $e->getMessage());
        }
    }
}

$pageTitle = 'Sign Up';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="fas fa-user-plus fa-3x text-primary mb-3"></i>
                        <h3 class="fw-bold">Create Account</h3>
                        <p class="text-muted">Join WebStore today</p>
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
                    
                    <form action="<?php echo BASE_URL; ?>user/signup.php" method="POST" id="signupForm">
                        <div class="form-outline mb-4">
                            <input type="text" name="name" class="form-control" required
                                   value="<?php echo isset($_POST['name']) ? e($_POST['name']) : ''; ?>">
                            <label class="form-label">Full Name</label>
                        </div>
                        
                        <div class="form-outline mb-4">
                            <input type="email" name="email" class="form-control" required
                                   value="<?php echo isset($_POST['email']) ? e($_POST['email']) : ''; ?>">
                            <label class="form-label">Email Address</label>
                        </div>
                        
                        <div class="form-outline mb-4">
                            <input type="tel" name="mobile" class="form-control" required
                                   pattern="[0-9]{10}" maxlength="10"
                                   value="<?php echo isset($_POST['mobile']) ? e($_POST['mobile']) : ''; ?>">
                            <label class="form-label">Mobile Number</label>
                        </div>
                        
                        <div class="form-outline mb-4">
                            <input type="password" name="password" class="form-control" required minlength="6">
                            <label class="form-label">Password (min 6 characters)</label>
                        </div>
                        
                        <div class="form-outline mb-4">
                            <input type="password" name="confirm_password" class="form-control" required>
                            <label class="form-label">Confirm Password</label>
                        </div>
                        
                        <div class="form-check mb-4">
                            <input type="checkbox" class="form-check-input" id="terms" required>
                            <label class="form-check-label" for="terms">
                                I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>
                            </label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-block btn-lg mb-4">
                            <i class="fas fa-user-plus me-2"></i>Create Account
                        </button>
                    </form>
                    
                    <div class="text-center">
                        <p class="mb-0">Already have an account? 
                            <a href="<?php echo BASE_URL; ?>user/login.php" class="fw-bold text-primary">Sign in</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Mobile number validation
document.querySelector('input[name="mobile"]').addEventListener('input', function(e) {
    this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);
});

// Form validation
document.getElementById('signupForm').addEventListener('submit', function(e) {
    var mobile = document.querySelector('input[name="mobile"]');
    var password = document.querySelector('input[name="password"]');
    var confirmPassword = document.querySelector('input[name="confirm_password"]');
    
    if (!/^[0-9]{10}$/.test(mobile.value)) {
        e.preventDefault();
        alert('Please enter a valid 10-digit mobile number');
        mobile.focus();
        return false;
    }
    
    if (password.value !== confirmPassword.value) {
        e.preventDefault();
        alert('Passwords do not match');
        confirmPassword.focus();
        return false;
    }
    
    return true;
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
