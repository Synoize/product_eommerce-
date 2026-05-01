<?php
/**
 * Contact Us Page
 */

$pageTitle = 'Contact Us';
require_once 'includes/header.php';

$success = false;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';
    
    // Validation
    if (empty($name)) $errors[] = 'Name is required';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required';
    if (empty($phone) || !preg_match('/^[0-9]{10}$/', $phone)) $errors[] = 'Valid 10-digit phone number is required';
    if (empty($message)) $errors[] = 'Message is required';
    
    if (empty($errors)) {
        // Check if table exists, create if not
        try {
            $pdo->exec("CREATE TABLE IF NOT EXISTS contact_messages (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(120) NOT NULL,
                email VARCHAR(190) NOT NULL,
                phone VARCHAR(30) NOT NULL,
                message TEXT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )");
            
            // Insert message
            $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, phone, message) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $email, $phone, $message]);
            
            $success = true;
        } catch (PDOException $e) {
            $errors[] = 'Failed to send message. Please try again.';
        }
    }
}
?>

<!-- Page Header -->
<section class="bg-primary-light py-5">
    <div class="container text-center">
        <h1 class="fw-bold mb-3">Contact Us</h1>
        <p class="text-muted mb-0">We'd love to hear from you. Get in touch with us.</p>
    </div>
</section>

<!-- Contact Content -->
<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <!-- Contact Info -->
            <div class="col-lg-4">
                <div class="contact-info h-100">
                    <h4 class="fw-bold mb-4">Get In Touch</h4>
                    
                    <div class="mb-4">
                        <i class="fas fa-map-marker-alt"></i>
                        <h6 class="fw-bold mb-1">Address</h6>
                        <p class="mb-0">123 Commerce Street<br>Business District, City 12345<br>Country</p>
                    </div>
                    
                    <div class="mb-4">
                        <i class="fas fa-phone"></i>
                        <h6 class="fw-bold mb-1">Phone</h6>
                        <p class="mb-0">+1 234 567 8900<br>+1 234 567 8901</p>
                    </div>
                    
                    <div class="mb-4">
                        <i class="fas fa-envelope"></i>
                        <h6 class="fw-bold mb-1">Email</h6>
                        <p class="mb-0">support@webstore.com<br>info@webstore.com</p>
                    </div>
                    
                    <div>
                        <i class="fas fa-clock"></i>
                        <h6 class="fw-bold mb-1">Working Hours</h6>
                        <p class="mb-0">Mon - Fri: 9AM - 6PM<br>Sat: 10AM - 4PM<br>Sun: Closed</p>
                    </div>
                </div>
            </div>
            
            <!-- Contact Form -->
            <div class="col-lg-8">
                <div class="contact-form">
                    <h4 class="fw-bold mb-4">Send Us a Message</h4>
                    
                    <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>
                        Thank you for your message! We'll get back to you soon.
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                            <li><?php echo e($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                    
                    <form action="<?php echo BASE_URL; ?>contact-us.php" method="POST" id="contactForm">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Your Name *</label>
                                <input type="text" name="name" class="form-control" required
                                       value="<?php echo isset($_POST['name']) ? e($_POST['name']) : ''; ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email Address *</label>
                                <input type="email" name="email" class="form-control" required
                                       value="<?php echo isset($_POST['email']) ? e($_POST['email']) : ''; ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Phone Number *</label>
                                <input type="tel" name="phone" class="form-control" required
                                       pattern="[0-9]{10}" maxlength="10" placeholder="10-digit mobile number"
                                       value="<?php echo isset($_POST['phone']) ? e($_POST['phone']) : ''; ?>">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Message *</label>
                                <textarea name="message" class="form-control" rows="5" required
                                          placeholder="How can we help you?"><?php echo isset($_POST['message']) ? e($_POST['message']) : ''; ?></textarea>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-lg mt-4">
                            <i class="fas fa-paper-plane me-2"></i>Send Message
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- FAQ Section -->
        <div class="mt-5">
            <div class="text-center mb-4">
                <h3 class="fw-bold">Frequently Asked Questions</h3>
                <p class="text-muted">Quick answers to common questions</p>
            </div>
            
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="fw-bold text-primary mb-2">How can I track my order?</h6>
                            <p class="text-muted mb-0">Once your order is shipped, you will receive a tracking number via email. You can use this number to track your package on our website or the courier's tracking portal.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="fw-bold text-primary mb-2">What is your return policy?</h6>
                            <p class="text-muted mb-0">We offer a 30-day return policy for most items. Products must be unused and in their original packaging. Simply contact our support team to initiate a return.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="fw-bold text-primary mb-2">How long does shipping take?</h6>
                            <p class="text-muted mb-0">Standard shipping typically takes 3-7 business days depending on your location. Express shipping options are available at checkout for faster delivery.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <h6 class="fw-bold text-primary mb-2">Is my payment information secure?</h6>
                            <p class="text-muted mb-0">Absolutely! We use industry-standard SSL encryption and secure payment gateways (Razorpay) to ensure your payment information is always protected.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// Phone validation
document.querySelector('input[name="phone"]').addEventListener('input', function(e) {
    this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);
});

// Form validation
document.getElementById('contactForm').addEventListener('submit', function(e) {
    var phone = document.querySelector('input[name="phone"]');
    
    if (!/^[0-9]{10}$/.test(phone.value)) {
        e.preventDefault();
        alert('Please enter a valid 10-digit phone number');
        phone.focus();
        return false;
    }
    
    return true;
});
</script>

<?php require_once 'includes/footer.php'; ?>
