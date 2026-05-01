<?php
/**
 * About Us Page
 */

$pageTitle = 'About Us';
require_once 'includes/header.php';
?>

<!-- About Hero -->
<section class="about-hero">
    <div class="container text-center">
        <h1 class="display-4 fw-bold mb-3">About WebStore</h1>
        <p class="lead mb-0">Your trusted destination for quality products and exceptional shopping experience</p>
    </div>
</section>

<!-- About Content -->
<section class="py-5">
    <div class="container">
        <div class="row align-items-center mb-5">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <img src="https://img.freepik.com/free-vector/ecommerce-checkout-laptop-concept-illustration_114360-8233.jpg" 
                     alt="About WebStore" class="img-fluid rounded-4 shadow">
            </div>
            <div class="col-lg-6">
                <h2 class="fw-bold mb-4">Who We Are</h2>
                <p class="text-muted mb-4">WebStore is a leading e-commerce platform dedicated to providing customers with a seamless and enjoyable shopping experience. Founded with a vision to make quality products accessible to everyone, we have grown into a trusted marketplace serving thousands of satisfied customers.</p>
                <p class="text-muted mb-4">Our team is passionate about curating the best products, ensuring competitive prices, and delivering exceptional customer service. We believe in building lasting relationships with our customers based on trust, transparency, and mutual respect.</p>
                <a href="<?php echo BASE_URL; ?>shop.php" class="btn btn-primary">
                    <i class="fas fa-shopping-bag me-2"></i>Start Shopping
                </a>
            </div>
        </div>
        
        <!-- Mission & Vision -->
        <div class="row g-4 mb-5">
            <div class="col-md-6">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="icon-box bg-primary-light rounded-circle p-3 me-3">
                                <i class="fas fa-bullseye fa-2x text-primary"></i>
                            </div>
                            <h4 class="fw-bold mb-0">Our Mission</h4>
                        </div>
                        <p class="text-muted mb-0">To provide customers with a convenient, secure, and enjoyable online shopping experience while offering quality products at competitive prices. We strive to exceed customer expectations through exceptional service and continuous innovation.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="icon-box bg-primary-light rounded-circle p-3 me-3">
                                <i class="fas fa-eye fa-2x text-primary"></i>
                            </div>
                            <h4 class="fw-bold mb-0">Our Vision</h4>
                        </div>
                        <p class="text-muted mb-0">To become the most trusted and preferred online shopping destination, known for our commitment to quality, customer satisfaction, and sustainable business practices. We envision a future where shopping is effortless and enjoyable for everyone.</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Why Choose Us -->
        <div class="text-center mb-5">
            <h2 class="fw-bold">Why Choose Us</h2>
            <p class="text-muted">Discover what makes WebStore the preferred choice for online shoppers</p>
        </div>
        
        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="about-feature">
                    <i class="fas fa-shield-alt"></i>
                    <h5 class="fw-bold">Secure Shopping</h5>
                    <p class="text-muted">Your security is our priority. We use advanced encryption and secure payment gateways to protect your data.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="about-feature">
                    <i class="fas fa-shipping-fast"></i>
                    <h5 class="fw-bold">Fast Delivery</h5>
                    <p class="text-muted">We partner with reliable logistics providers to ensure your orders reach you quickly and safely.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="about-feature">
                    <i class="fas fa-undo"></i>
                    <h5 class="fw-bold">Easy Returns</h5>
                    <p class="text-muted">Not satisfied? Our hassle-free 30-day return policy ensures you can shop with confidence.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="about-feature">
                    <i class="fas fa-headset"></i>
                    <h5 class="fw-bold">24/7 Support</h5>
                    <p class="text-muted">Our dedicated customer support team is available round the clock to assist you with any queries.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="about-feature">
                    <i class="fas fa-tags"></i>
                    <h5 class="fw-bold">Best Prices</h5>
                    <p class="text-muted">We offer competitive prices and regular promotions to help you get the best value for your money.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="about-feature">
                    <i class="fas fa-award"></i>
                    <h5 class="fw-bold">Quality Guaranteed</h5>
                    <p class="text-muted">Every product in our store is carefully selected and verified to meet our high quality standards.</p>
                </div>
            </div>
        </div>
        
        <!-- Team Section -->
        <div class="text-center mb-5">
            <h2 class="fw-bold">Meet Our Team</h2>
            <p class="text-muted">The passionate people behind WebStore</p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card text-center border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <img src="https://ui-avatars.com/api/?name=John+Doe&background=f84183&color=fff&size=128" 
                             class="rounded-circle mb-3" width="100" alt="Team Member">
                        <h5 class="fw-bold mb-1">John Doe</h5>
                        <p class="text-primary mb-2">Founder & CEO</p>
                        <p class="text-muted small mb-0">Visionary leader with 15+ years of experience in e-commerce and retail.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <img src="https://ui-avatars.com/api/?name=Jane+Smith&background=f84183&color=fff&size=128" 
                             class="rounded-circle mb-3" width="100" alt="Team Member">
                        <h5 class="fw-bold mb-1">Jane Smith</h5>
                        <p class="text-primary mb-2">Operations Manager</p>
                        <p class="text-muted small mb-0">Expert in supply chain management and customer experience optimization.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <img src="https://ui-avatars.com/api/?name=Mike+Johnson&background=f84183&color=fff&size=128" 
                             class="rounded-circle mb-3" width="100" alt="Team Member">
                        <h5 class="fw-bold mb-1">Mike Johnson</h5>
                        <p class="text-primary mb-2">Tech Lead</p>
                        <p class="text-muted small mb-0">Technology enthusiast driving innovation and platform development.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="py-5 bg-primary-light">
    <div class="container text-center">
        <h2 class="fw-bold mb-3">Ready to Start Shopping?</h2>
        <p class="text-muted mb-4">Explore our wide range of products and experience the WebStore difference today.</p>
        <a href="<?php echo BASE_URL; ?>shop.php" class="btn btn-primary btn-lg">
            <i class="fas fa-shopping-bag me-2"></i>Browse Products
        </a>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
