<?php
/**
 * Manage Addresses Page
 */

require_once __DIR__ . '/../includes/db_connect.php';
requireLogin();

$userId = $_SESSION['user_id'];
$errors = [];
$success = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $addressId = isset($_POST['address_id']) ? (int)$_POST['address_id'] : 0;
    
    switch ($action) {
        case 'add':
        case 'edit':
            $name = trim($_POST['name'] ?? '');
            $mobile = trim($_POST['mobile'] ?? '');
            $address = trim($_POST['address'] ?? '');
            $city = trim($_POST['city'] ?? '');
            $state = trim($_POST['state'] ?? '');
            $pincode = trim($_POST['pincode'] ?? '');
            $isDefault = isset($_POST['is_default']) ? 1 : 0;
            
            // Validation
            if (empty($name)) $errors[] = 'Name is required';
            if (empty($mobile) || !preg_match('/^[0-9]{10}$/', $mobile)) $errors[] = 'Valid 10-digit mobile number is required';
            if (empty($address)) $errors[] = 'Address is required';
            if (empty($city)) $errors[] = 'City is required';
            if (empty($state)) $errors[] = 'State is required';
            if (empty($pincode) || !preg_match('/^[0-9]{6}$/', $pincode)) $errors[] = 'Valid 6-digit pincode is required';
            
            if (empty($errors)) {
                try {
                    if ($isDefault) {
                        // Remove default from other addresses
                        $stmt = $pdo->prepare("UPDATE addresses SET is_default = 0 WHERE user_id = ?");
                        $stmt->execute([$userId]);
                    }
                    
                    if ($action === 'edit' && $addressId > 0) {
                        // Update existing
                        $stmt = $pdo->prepare("UPDATE addresses SET name = ?, mobile = ?, address = ?, city = ?, state = ?, pincode = ?, is_default = ? WHERE id = ? AND user_id = ?");
                        $stmt->execute([$name, $mobile, $address, $city, $state, $pincode, $isDefault, $addressId, $userId]);
                        setFlash('Address updated successfully!', 'success');
                    } else {
                        // Add new
                        $stmt = $pdo->prepare("INSERT INTO addresses (user_id, name, mobile, address, city, state, pincode, is_default) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmt->execute([$userId, $name, $mobile, $address, $city, $state, $pincode, $isDefault]);
                        setFlash('Address added successfully!', 'success');
                    }
                    
                    redirect(BASE_URL . 'user/addresses.php');
                    exit;
                } catch (PDOException $e) {
                    $errors[] = 'Failed to save address. Please try again.';
                }
            }
            break;
            
        case 'delete':
            if ($addressId > 0) {
                try {
                    $stmt = $pdo->prepare("DELETE FROM addresses WHERE id = ? AND user_id = ?");
                    $stmt->execute([$addressId, $userId]);
                    setFlash('Address deleted successfully!', 'success');
                } catch (PDOException $e) {
                    setFlash('Failed to delete address.', 'danger');
                }
            }
            redirect(BASE_URL . 'user/addresses.php');
            exit;
            
        case 'set_default':
            if ($addressId > 0) {
                try {
                    $pdo->beginTransaction();
                    $stmt = $pdo->prepare("UPDATE addresses SET is_default = 0 WHERE user_id = ?");
                    $stmt->execute([$userId]);
                    $stmt = $pdo->prepare("UPDATE addresses SET is_default = 1 WHERE id = ? AND user_id = ?");
                    $stmt->execute([$addressId, $userId]);
                    $pdo->commit();
                    setFlash('Default address updated!', 'success');
                } catch (PDOException $e) {
                    $pdo->rollBack();
                    setFlash('Failed to update default address.', 'danger');
                }
            }
            redirect(BASE_URL . 'user/addresses.php');
            exit;
    }
}

// Get edit address
$editAddress = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    try {
        $stmt = $pdo->prepare("SELECT * FROM addresses WHERE id = ? AND user_id = ?");
        $stmt->execute([$editId, $userId]);
        $editAddress = $stmt->fetch();
    } catch (PDOException $e) {
        // Ignore
    }
}

// Fetch all addresses
$addresses = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC, created_at DESC");
    $stmt->execute([$userId]);
    $addresses = $stmt->fetchAll();
} catch (PDOException $e) {
    $errors[] = 'Failed to load addresses.';
}

$pageTitle = 'Manage Addresses';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-3 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">My Account</h5>
                    <div class="list-group list-group-flush">
                        <a href="<?php echo BASE_URL; ?>user/profile.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-user me-2"></i>Profile
                        </a>
                        <a href="<?php echo BASE_URL; ?>user/addresses.php" class="list-group-item list-group-item-action active">
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
        
        <!-- Main Content -->
        <div class="col-lg-9">
            <h3 class="fw-bold mb-4">Manage Addresses</h3>
            
            <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                    <li><?php echo e($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
            
            <!-- Add/Edit Form -->
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-3"><?php echo $editAddress ? 'Edit Address' : 'Add New Address'; ?></h5>
                    <form method="POST">
                        <input type="hidden" name="action" value="<?php echo $editAddress ? 'edit' : 'add'; ?>">
                        <?php if ($editAddress): ?>
                        <input type="hidden" name="address_id" value="<?php echo $editAddress['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="name" class="form-control" required
                                       value="<?php echo $editAddress ? e($editAddress['name']) : e($_SESSION['user_name']); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Mobile Number</label>
                                <input type="tel" name="mobile" class="form-control" required pattern="[0-9]{10}" maxlength="10"
                                       value="<?php echo $editAddress ? e($editAddress['mobile']) : ''; ?>">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control" rows="2" required><?php echo $editAddress ? e($editAddress['address']) : ''; ?></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">City</label>
                                <input type="text" name="city" class="form-control" required
                                       value="<?php echo $editAddress ? e($editAddress['city']) : ''; ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">State</label>
                                <input type="text" name="state" class="form-control" required
                                       value="<?php echo $editAddress ? e($editAddress['state']) : ''; ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Pincode</label>
                                <input type="text" name="pincode" class="form-control" required pattern="[0-9]{6}" maxlength="6"
                                       value="<?php echo $editAddress ? e($editAddress['pincode']) : ''; ?>">
                            </div>
                        </div>
                        
                        <div class="form-check mb-3">
                            <input type="checkbox" class="form-check-input" name="is_default" id="is_default" value="1"
                                   <?php echo ($editAddress && $editAddress['is_default']) || (!$editAddress && empty($addresses)) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="is_default">Set as default address</label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <?php echo $editAddress ? 'Update Address' : 'Add Address'; ?>
                        </button>
                        <?php if ($editAddress): ?>
                        <a href="<?php echo BASE_URL; ?>user/addresses.php" class="btn btn-outline-secondary ms-2">Cancel</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
            
            <!-- Saved Addresses -->
            <?php if (!empty($addresses)): ?>
            <h5 class="fw-bold mb-3">Saved Addresses</h5>
            <div class="row">
                <?php foreach ($addresses as $address): ?>
                <div class="col-md-6 mb-3">
                    <div class="card h-100 <?php echo $address['is_default'] ? 'border-primary' : ''; ?>">
                        <div class="card-body">
                            <?php if ($address['is_default']): ?>
                            <span class="badge bg-primary mb-2">Default</span>
                            <?php endif; ?>
                            <h6 class="card-title fw-bold"><?php echo e($address['name']); ?></h6>
                            <p class="card-text mb-1"><?php echo e($address['mobile']); ?></p>
                            <p class="card-text text-muted"><?php echo e($address['address']); ?>, <?php echo e($address['city']); ?>, <?php echo e($address['state']); ?> - <?php echo e($address['pincode']); ?></p>
                            
                            <div class="d-flex gap-2">
                                <a href="?edit=<?php echo $address['id']; ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <form method="POST" class="d-inline" onsubmit="return confirm('Delete this address?')">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="address_id" value="<?php echo $address['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                                <?php if (!$address['is_default']): ?>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="action" value="set_default">
                                    <input type="hidden" name="address_id" value="<?php echo $address['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-success">Set Default</button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-map-marker-alt fa-3x text-muted mb-3"></i>
                <p class="text-muted">No addresses saved yet.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
