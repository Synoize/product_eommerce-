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
<section class="bg-gradient-to-r from-pink-50 to-purple-50 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-3">Contact Us</h1>
        <p class="text-gray-600">We'd love to hear from you. Get in touch with us.</p>
    </div>
</section>

<!-- Contact Content -->
<section class="py-12 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Contact Info -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl shadow-md p-6 h-full">
                    <h4 class="text-xl font-bold text-gray-900 mb-6">Get In Touch</h4>
                    
                    <div class="mb-6 flex items-start">
                        <div class="w-10 h-10 bg-primary-100 rounded-full flex items-center justify-center mr-4 flex-shrink-0">
                            <i class="fas fa-map-marker-alt text-primary-500"></i>
                        </div>
                        <div>
                            <h6 class="font-bold text-gray-900 mb-1">Address</h6>
                            <p class="text-gray-600 text-sm">123 Commerce Street<br>Business District, City 12345<br>Country</p>
                        </div>
                    </div>
                    
                    <div class="mb-6 flex items-start">
                        <div class="w-10 h-10 bg-primary-100 rounded-full flex items-center justify-center mr-4 flex-shrink-0">
                            <i class="fas fa-phone text-primary-500"></i>
                        </div>
                        <div>
                            <h6 class="font-bold text-gray-900 mb-1">Phone</h6>
                            <p class="text-gray-600 text-sm">+1 234 567 8900<br>+1 234 567 8901</p>
                        </div>
                    </div>
                    
                    <div class="mb-6 flex items-start">
                        <div class="w-10 h-10 bg-primary-100 rounded-full flex items-center justify-center mr-4 flex-shrink-0">
                            <i class="fas fa-envelope text-primary-500"></i>
                        </div>
                        <div>
                            <h6 class="font-bold text-gray-900 mb-1">Email</h6>
                            <p class="text-gray-600 text-sm">support@webstore.com<br>info@webstore.com</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="w-10 h-10 bg-primary-100 rounded-full flex items-center justify-center mr-4 flex-shrink-0">
                            <i class="fas fa-clock text-primary-500"></i>
                        </div>
                        <div>
                            <h6 class="font-bold text-gray-900 mb-1">Working Hours</h6>
                            <p class="text-gray-600 text-sm">Mon - Fri: 9AM - 6PM<br>Sat: 10AM - 4PM<br>Sun: Closed</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Contact Form -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-md p-6 md:p-8">
                    <h4 class="text-xl font-bold text-gray-900 mb-6">Send Us a Message</h4>
                    
                    <?php if ($success): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6 flex items-center">
                        <i class="fas fa-check-circle mr-2"></i>
                        Thank you for your message! We'll get back to you soon.
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($errors)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
                        <ul class="mb-0 list-disc list-inside">
                            <?php foreach ($errors as $error): ?>
                            <li><?php echo e($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                    
                    <form action="<?php echo BASE_URL; ?>contact-us.php" method="POST" id="contactForm" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Your Name *</label>
                                <input type="text" name="name" required
                                       value="<?php echo isset($_POST['name']) ? e($_POST['name']) : ''; ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email Address *</label>
                                <input type="email" name="email" required
                                       value="<?php echo isset($_POST['email']) ? e($_POST['email']) : ''; ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number *</label>
                                <input type="tel" name="phone" required
                                       pattern="[0-9]{10}" maxlength="10" placeholder="10-digit mobile number"
                                       value="<?php echo isset($_POST['phone']) ? e($_POST['phone']) : ''; ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Message *</label>
                            <textarea name="message" rows="5" required
                                      placeholder="How can we help you?"
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition"><?php echo isset($_POST['message']) ? e($_POST['message']) : ''; ?></textarea>
                        </div>
                        
                        <button type="submit" class="inline-flex items-center bg-primary-500 hover:bg-primary-600 text-white font-semibold py-4 px-8 rounded-full transition shadow-lg hover:shadow-xl">
                            <i class="fas fa-paper-plane mr-2"></i>Send Message
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- FAQ Section -->
        <div class="mt-16">
            <div class="text-center mb-8">
                <h3 class="text-2xl font-bold text-gray-900 mb-2">Frequently Asked Questions</h3>
                <p class="text-gray-600">Quick answers to common questions</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white rounded-2xl shadow-md p-6">
                    <h6 class="font-bold text-primary-500 mb-2">How can I track my order?</h6>
                    <p class="text-gray-600 text-sm">Once your order is shipped, you will receive a tracking number via email. You can use this number to track your package on our website or the courier's tracking portal.</p>
                </div>
                <div class="bg-white rounded-2xl shadow-md p-6">
                    <h6 class="font-bold text-primary-500 mb-2">What is your return policy?</h6>
                    <p class="text-gray-600 text-sm">We offer a 30-day return policy for most items. Products must be unused and in their original packaging. Simply contact our support team to initiate a return.</p>
                </div>
                <div class="bg-white rounded-2xl shadow-md p-6">
                    <h6 class="font-bold text-primary-500 mb-2">How long does shipping take?</h6>
                    <p class="text-gray-600 text-sm">Standard shipping typically takes 3-7 business days depending on your location. Express shipping options are available at checkout for faster delivery.</p>
                </div>
                <div class="bg-white rounded-2xl shadow-md p-6">
                    <h6 class="font-bold text-primary-500 mb-2">Is my payment information secure?</h6>
                    <p class="text-gray-600 text-sm">Absolutely! We use industry-standard SSL encryption and secure payment gateways (Razorpay) to ensure your payment information is always protected.</p>
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
