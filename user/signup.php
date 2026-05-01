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

<div class="min-h-screen bg-gradient-to-br from-pink-50 to-purple-50 py-12 px-4">
    <div class="max-w-md mx-auto">
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <div class="text-center mb-8">
                <div class="w-20 h-20 bg-primary-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-user-plus text-4xl text-primary-500"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-900">Create Account</h3>
                <p class="text-gray-500">Join WebStore today</p>
            </div>
            
            <?php if (!empty($errors)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
                <ul class="mb-0 list-disc list-inside">
                    <?php foreach ($errors as $error): ?>
                    <li><?php echo e($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
            
            <form action="<?php echo BASE_URL; ?>user/signup.php" method="POST" id="signupForm" class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                    <input type="text" name="name" required
                           value="<?php echo isset($_POST['name']) ? e($_POST['name']) : ''; ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <input type="email" name="email" required
                           value="<?php echo isset($_POST['email']) ? e($_POST['email']) : ''; ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Mobile Number</label>
                    <input type="tel" name="mobile" required
                           pattern="[0-9]{10}" maxlength="10"
                           value="<?php echo isset($_POST['mobile']) ? e($_POST['mobile']) : ''; ?>"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                    <p class="text-xs text-gray-500 mt-1">Enter 10-digit mobile number</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password (min 6 characters)</label>
                    <input type="password" name="password" required minlength="6"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                    <input type="password" name="confirm_password" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                </div>
                
                <div class="flex items-start">
                    <input type="checkbox" id="terms" required class="w-4 h-4 text-primary-500 border-gray-300 rounded focus:ring-primary-500 mt-1">
                    <label class="ml-2 text-sm text-gray-600" for="terms">
                        I agree to the <a href="#" class="text-primary-500 hover:text-primary-600 font-medium">Terms of Service</a> and <a href="#" class="text-primary-500 hover:text-primary-600 font-medium">Privacy Policy</a>
                    </label>
                </div>
                
                <button type="submit" class="w-full bg-primary-500 hover:bg-primary-600 text-white font-semibold py-4 rounded-full transition shadow-lg hover:shadow-xl">
                    <i class="fas fa-user-plus mr-2"></i>Create Account
                </button>
            </form>
            
            <div class="text-center mt-6">
                <p class="text-gray-600">Already have an account? 
                    <a href="<?php echo BASE_URL; ?>user/login.php" class="font-semibold text-primary-500 hover:text-primary-600">Sign in</a>
                </p>
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
