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

<div class="min-h-screen bg-gray-900 py-12 px-4">
    <div class="max-w-md mx-auto">
        <div class="bg-white rounded-2xl shadow-2xl p-8">
            <div class="text-center mb-8">
                <div class="w-20 h-20 bg-gray-900 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-user-shield text-4xl text-white"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-900">Admin Login</h3>
                <p class="text-gray-500">Access the admin panel</p>
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
            
            <form action="<?php echo BASE_URL; ?>admin/login.php" method="POST" class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Admin Email</label>
                    <input type="email" name="email" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent transition">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input type="password" name="password" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-900 focus:border-transparent transition">
                </div>
                
                <button type="submit" class="w-full bg-gray-900 hover:bg-gray-800 text-white font-semibold py-4 rounded-full transition shadow-lg hover:shadow-xl">
                    <i class="fas fa-sign-in-alt mr-2"></i>Admin Sign In
                </button>
            </form>
            
            <div class="text-center mt-6">
                <a href="<?php echo BASE_URL; ?>" class="text-gray-500 hover:text-gray-700 inline-flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Website
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
