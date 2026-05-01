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

<div class="container py-5">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 mb-4">
            <div class="card">
                <div class="card-body text-center">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['user_name']); ?>&background=f84183&color=fff&size=128" 
                         class="rounded-circle mb-3" width="100" alt="Profile">
                    <h5 class="fw-bold mb-1"><?php echo e($_SESSION['user_name']); ?></h5>
                    <p class="text-muted small mb-0"><?php echo e($_SESSION['user_email']); ?></p>
                    <h5 class="card-title">My Account</h5>
                    <div class="list-group list-group-flush">
                        <a href="<?php echo BASE_URL; ?>user/profile.php" class="list-group-item list-group-item-action active">
                            <i class="fas fa-user me-2"></i>Profile
                        </a>
                        <a href="<?php echo BASE_URL; ?>user/addresses.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-map-marker-alt me-2"></i>Addresses
                        </a>
                        <a href="<?php echo BASE_URL; ?>user/orders.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-shopping-bag me-2"></i>Orders
                        </a>
                        <a href="<?php echo BASE_URL; ?>user/logout.php" class="list-group-item list-group-item-action text-danger">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Profile Form -->
        <div class="col-md-9">
            <div class="card">
                <div class="card-body p-4">
                    <h4 class="fw-bold mb-4">Edit Profile</h4>
                    
                    <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                            <li><?php echo e($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                    
                    <form action="<?php echo BASE_URL; ?>user/profile.php" method="POST" id="profileForm">
                        <h6 class="fw-bold text-primary mb-3">Personal Information</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Full Name *</label>
                                <input type="text" name="name" class="form-control" required
                                       value="<?php echo e($_POST['name'] ?? $user['name'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email Address *</label>
                                <input type="email" name="email" class="form-control" required
                                       value="<?php echo e($_POST['email'] ?? $user['email'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Mobile Number *</label>
                                <input type="tel" name="mobile" class="form-control" required
                                       pattern="[0-9]{10}" maxlength="10"
                                       value="<?php echo e($_POST['mobile'] ?? $user['mobile'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <h6 class="fw-bold text-primary mb-3">Address Information</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <label class="form-label">Address</label>
                                <textarea name="address" class="form-control" rows="3"><?php echo e($_POST['address'] ?? $user['address'] ?? ''); ?></textarea>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">City</label>
                                <input type="text" name="city" class="form-control"
                                       value="<?php echo e($_POST['city'] ?? $user['city'] ?? ''); ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">State</label>
                                <input type="text" name="state" class="form-control"
                                       value="<?php echo e($_POST['state'] ?? $user['state'] ?? ''); ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Pincode</label>
                                <input type="text" name="pincode" class="form-control" maxlength="6"
                                       value="<?php echo e($_POST['pincode'] ?? $user['pincode'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <h6 class="fw-bold text-primary mb-3">Change Password</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label">Current Password</label>
                                <input type="password" name="current_password" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">New Password</label>
                                <input type="password" name="new_password" class="form-control" minlength="6">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Confirm New Password</label>
                                <input type="password" name="confirm_new_password" class="form-control">
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Save Changes
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
