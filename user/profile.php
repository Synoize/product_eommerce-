<?php
/**
 * User Profile Page
 */

$pageTitle = 'My Profile';
require_once __DIR__ . '/../includes/header.php';

// Require login
requireLogin();

$userId = $_SESSION['user_id'];
$errors = [];
$success = false;

// Fetch user data
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
} catch (PDOException $e) {
    $errors[] = 'Failed to load profile data.';
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $mobile = isset($_POST['mobile']) ? trim($_POST['mobile']) : '';
    $address = isset($_POST['address']) ? trim($_POST['address']) : '';
    $city = isset($_POST['city']) ? trim($_POST['city']) : '';
    $state = isset($_POST['state']) ? trim($_POST['state']) : '';
    $pincode = isset($_POST['pincode']) ? trim($_POST['pincode']) : '';
    
    $currentPassword = isset($_POST['current_password']) ? $_POST['current_password'] : '';
    $newPassword = isset($_POST['new_password']) ? $_POST['new_password'] : '';
    $confirmNewPassword = isset($_POST['confirm_new_password']) ? $_POST['confirm_new_password'] : '';
    
    // Validation
    if (empty($name)) $errors[] = 'Name is required';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required';
    if (empty($mobile) || !preg_match('/^[0-9]{10}$/', $mobile)) $errors[] = 'Valid 10-digit mobile number is required';
    
    // Check email uniqueness (excluding current user)
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $userId]);
            if ($stmt->fetch()) {
                $errors[] = 'Email already in use by another account';
            }
        } catch (PDOException $e) {
            $errors[] = 'Error checking email availability';
        }
    }
    
    // Password change validation
    if (!empty($newPassword)) {
        if (empty($currentPassword)) {
            $errors[] = 'Current password is required to change password';
        } elseif (!password_verify($currentPassword, $user['password'])) {
            $errors[] = 'Current password is incorrect';
        } elseif (strlen($newPassword) < 6) {
            $errors[] = 'New password must be at least 6 characters';
        } elseif ($newPassword !== $confirmNewPassword) {
            $errors[] = 'New passwords do not match';
        }
    }
    
    if (empty($errors)) {
        try {
            // Update user data
            if (!empty($newPassword)) {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, mobile = ?, address = ?, city = ?, state = ?, pincode = ?, password = ? WHERE id = ?");
                $stmt->execute([$name, $email, $mobile, $address, $city, $state, $pincode, $hashedPassword, $userId]);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, mobile = ?, address = ?, city = ?, state = ?, pincode = ? WHERE id = ?");
                $stmt->execute([$name, $email, $mobile, $address, $city, $state, $pincode, $userId]);
            }
            
            // Update session
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;
            
            // Refresh user data
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            
            $success = true;
            setFlash('Profile updated successfully!', 'success');
        } catch (PDOException $e) {
            $errors[] = 'Failed to update profile. Please try again.';
            error_log('Profile Update Error: ' . $e->getMessage());
        }
    }
}
?>

<div class="min-h-screen bg-gray-50 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <!-- Sidebar -->
            <div class="md:col-span-1">
                <div class="bg-white rounded-2xl shadow-md p-6 sticky top-24">
                    <div class="text-center mb-6">
                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['user_name']); ?>&background=f84183&color=fff&size=128" 
                             class="w-24 h-24 rounded-full mx-auto mb-3" alt="Profile">
                        <h5 class="font-bold text-gray-900 mb-1"><?php echo e($_SESSION['user_name']); ?></h5>
                        <p class="text-gray-500 text-sm"><?php echo e($_SESSION['user_email']); ?></p>
                    </div>
                    <nav class="space-y-2">
                        <a href="<?php echo BASE_URL; ?>user/profile.php" class="flex items-center px-4 py-2 rounded-lg bg-primary-50 text-primary-600 font-medium">
                            <i class="fas fa-user mr-3"></i>Profile
                        </a>
                        <a href="<?php echo BASE_URL; ?>user/addresses.php" class="flex items-center px-4 py-2 rounded-lg text-gray-600 hover:bg-gray-50 transition">
                            <i class="fas fa-map-marker-alt mr-3"></i>Addresses
                        </a>
                        <a href="<?php echo BASE_URL; ?>user/orders.php" class="flex items-center px-4 py-2 rounded-lg text-gray-600 hover:bg-gray-50 transition">
                            <i class="fas fa-shopping-bag mr-3"></i>Orders
                        </a>
                        <a href="<?php echo BASE_URL; ?>user/logout.php" class="flex items-center px-4 py-2 rounded-lg text-red-600 hover:bg-red-50 transition">
                            <i class="fas fa-sign-out-alt mr-3"></i>Logout
                        </a>
                    </nav>
                </div>
            </div>
            
            <!-- Profile Form -->
            <div class="md:col-span-3">
                <div class="bg-white rounded-2xl shadow-md p-6 md:p-8">
                    <h4 class="text-2xl font-bold text-gray-900 mb-6">Edit Profile</h4>
                    
                    <?php if (!empty($errors)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
                        <ul class="mb-0 list-disc list-inside">
                            <?php foreach ($errors as $error): ?>
                            <li><?php echo e($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                    
                    <form action="<?php echo BASE_URL; ?>user/profile.php" method="POST" id="profileForm" class="space-y-6">
                        <div>
                            <h6 class="font-bold text-primary-500 mb-4">Personal Information</h6>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
                                    <input type="text" name="name" required
                                           value="<?php echo e($_POST['name'] ?? $user['name'] ?? ''); ?>"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Email Address *</label>
                                    <input type="email" name="email" required
                                           value="<?php echo e($_POST['email'] ?? $user['email'] ?? ''); ?>"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Mobile Number *</label>
                                    <input type="tel" name="mobile" required
                                           pattern="[0-9]{10}" maxlength="10"
                                           value="<?php echo e($_POST['mobile'] ?? $user['mobile'] ?? ''); ?>"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                                </div>
                            </div>
                        </div>
                        
                        <div class="border-t border-gray-200 pt-6">
                            <h6 class="font-bold text-primary-500 mb-4">Address Information</h6>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                                    <textarea name="address" rows="3"
                                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition"><?php echo e($_POST['address'] ?? $user['address'] ?? ''); ?></textarea>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                                        <input type="text" name="city"
                                               value="<?php echo e($_POST['city'] ?? $user['city'] ?? ''); ?>"
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">State</label>
                                        <input type="text" name="state"
                                               value="<?php echo e($_POST['state'] ?? $user['state'] ?? ''); ?>"
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Pincode</label>
                                        <input type="text" name="pincode" maxlength="6"
                                               value="<?php echo e($_POST['pincode'] ?? $user['pincode'] ?? ''); ?>"
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="border-t border-gray-200 pt-6">
                            <h6 class="font-bold text-primary-500 mb-4">Change Password</h6>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                                    <input type="password" name="current_password"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                                    <input type="password" name="new_password" minlength="6"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                                    <input type="password" name="confirm_new_password"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" class="bg-primary-500 hover:bg-primary-600 text-white font-semibold py-3 px-8 rounded-full transition shadow-lg hover:shadow-xl inline-flex items-center">
                            <i class="fas fa-save mr-2"></i>Save Changes
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Mobile and pincode validation
document.querySelector('input[name="mobile"]').addEventListener('input', function(e) {
    this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);
});

document.querySelector('input[name="pincode"]').addEventListener('input', function(e) {
    this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6);
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
