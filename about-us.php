<?php
/**
 * About Us Page
 */

$pageTitle = 'About Us';
require_once 'includes/header.php';
?>

<!-- About Hero -->
<section class="bg-gradient-to-r from-pink-100 to-purple-100 py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">About WebStore</h1>
        <p class="text-xl text-gray-600 max-w-2xl mx-auto">Your trusted destination for quality products and exceptional shopping experience</p>
    </div>
</section>

<!-- About Content -->
<section class="py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center mb-20">
            <div>
                <img src="https://img.freepik.com/free-vector/ecommerce-checkout-laptop-concept-illustration_114360-8233.jpg" 
                     alt="About WebStore" class="w-full rounded-3xl shadow-xl">
            </div>
            <div>
                <h2 class="text-3xl font-bold text-gray-900 mb-6">Who We Are</h2>
                <p class="text-gray-600 mb-4 leading-relaxed">WebStore is a leading e-commerce platform dedicated to providing customers with a seamless and enjoyable shopping experience. Founded with a vision to make quality products accessible to everyone, we have grown into a trusted marketplace serving thousands of satisfied customers.</p>
                <p class="text-gray-600 mb-6 leading-relaxed">Our team is passionate about curating the best products, ensuring competitive prices, and delivering exceptional customer service. We believe in building lasting relationships with our customers based on trust, transparency, and mutual respect.</p>
                <a href="<?php echo BASE_URL; ?>shop.php" class="inline-flex items-center bg-primary-500 hover:bg-primary-600 text-white font-semibold py-3 px-6 rounded-full transition shadow-lg hover:shadow-xl">
                    <i class="fas fa-shopping-bag mr-2"></i>Start Shopping
                </a>
            </div>
        </div>
        
        <!-- Mission & Vision -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-20">
            <div class="bg-white rounded-2xl shadow-md p-8">
                <div class="flex items-center mb-4">
                    <div class="w-14 h-14 bg-primary-100 rounded-full flex items-center justify-center mr-4">
                        <i class="fas fa-bullseye text-2xl text-primary-500"></i>
                    </div>
                    <h4 class="text-xl font-bold text-gray-900">Our Mission</h4>
                </div>
                <p class="text-gray-600 leading-relaxed">To provide customers with a convenient, secure, and enjoyable online shopping experience while offering quality products at competitive prices. We strive to exceed customer expectations through exceptional service and continuous innovation.</p>
            </div>
            <div class="bg-white rounded-2xl shadow-md p-8">
                <div class="flex items-center mb-4">
                    <div class="w-14 h-14 bg-primary-100 rounded-full flex items-center justify-center mr-4">
                        <i class="fas fa-eye text-2xl text-primary-500"></i>
                    </div>
                    <h4 class="text-xl font-bold text-gray-900">Our Vision</h4>
                </div>
                <p class="text-gray-600 leading-relaxed">To become the most trusted and preferred online shopping destination, known for our commitment to quality, customer satisfaction, and sustainable business practices. We envision a future where shopping is effortless and enjoyable for everyone.</p>
            </div>
        </div>
        
        <!-- Why Choose Us -->
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Why Choose Us</h2>
            <p class="text-gray-600">Discover what makes WebStore the preferred choice for online shoppers</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-20">
            <div class="bg-white rounded-2xl shadow-md p-6 text-center">
                <div class="w-16 h-16 bg-primary-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-shield-alt text-3xl text-primary-500"></i>
                </div>
                <h5 class="text-lg font-bold text-gray-900 mb-2">Secure Shopping</h5>
                <p class="text-gray-600 text-sm">Your security is our priority. We use advanced encryption and secure payment gateways to protect your data.</p>
            </div>
            <div class="bg-white rounded-2xl shadow-md p-6 text-center">
                <div class="w-16 h-16 bg-primary-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-shipping-fast text-3xl text-primary-500"></i>
                </div>
                <h5 class="text-lg font-bold text-gray-900 mb-2">Fast Delivery</h5>
                <p class="text-gray-600 text-sm">We partner with reliable logistics providers to ensure your orders reach you quickly and safely.</p>
            </div>
            <div class="bg-white rounded-2xl shadow-md p-6 text-center">
                <div class="w-16 h-16 bg-primary-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-undo text-3xl text-primary-500"></i>
                </div>
                <h5 class="text-lg font-bold text-gray-900 mb-2">Easy Returns</h5>
                <p class="text-gray-600 text-sm">Not satisfied? Our hassle-free 30-day return policy ensures you can shop with confidence.</p>
            </div>
            <div class="bg-white rounded-2xl shadow-md p-6 text-center">
                <div class="w-16 h-16 bg-primary-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-headset text-3xl text-primary-500"></i>
                </div>
                <h5 class="text-lg font-bold text-gray-900 mb-2">24/7 Support</h5>
                <p class="text-gray-600 text-sm">Our dedicated customer support team is available round the clock to assist you with any queries.</p>
            </div>
            <div class="bg-white rounded-2xl shadow-md p-6 text-center">
                <div class="w-16 h-16 bg-primary-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-tags text-3xl text-primary-500"></i>
                </div>
                <h5 class="text-lg font-bold text-gray-900 mb-2">Best Prices</h5>
                <p class="text-gray-600 text-sm">We offer competitive prices and regular promotions to help you get the best value for your money.</p>
            </div>
            <div class="bg-white rounded-2xl shadow-md p-6 text-center">
                <div class="w-16 h-16 bg-primary-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-award text-3xl text-primary-500"></i>
                </div>
                <h5 class="text-lg font-bold text-gray-900 mb-2">Quality Guaranteed</h5>
                <p class="text-gray-600 text-sm">Every product in our store is carefully selected and verified to meet our high quality standards.</p>
            </div>
        </div>
        
        <!-- Team Section -->
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Meet Our Team</h2>
            <p class="text-gray-600">The passionate people behind WebStore</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="bg-white rounded-2xl shadow-md p-6 text-center">
                <img src="https://ui-avatars.com/api/?name=John+Doe&background=f84183&color=fff&size=128" 
                     class="w-24 h-24 rounded-full mx-auto mb-4" alt="Team Member">
                <h5 class="font-bold text-gray-900 mb-1">John Doe</h5>
                <p class="text-primary-500 mb-2">Founder & CEO</p>
                <p class="text-gray-600 text-sm">Visionary leader with 15+ years of experience in e-commerce and retail.</p>
            </div>
            <div class="bg-white rounded-2xl shadow-md p-6 text-center">
                <img src="https://ui-avatars.com/api/?name=Jane+Smith&background=f84183&color=fff&size=128" 
                     class="w-24 h-24 rounded-full mx-auto mb-4" alt="Team Member">
                <h5 class="font-bold text-gray-900 mb-1">Jane Smith</h5>
                <p class="text-primary-500 mb-2">Operations Manager</p>
                <p class="text-gray-600 text-sm">Expert in supply chain management and customer experience optimization.</p>
            </div>
            <div class="bg-white rounded-2xl shadow-md p-6 text-center">
                <img src="https://ui-avatars.com/api/?name=Mike+Johnson&background=f84183&color=fff&size=128" 
                     class="w-24 h-24 rounded-full mx-auto mb-4" alt="Team Member">
                <h5 class="font-bold text-gray-900 mb-1">Mike Johnson</h5>
                <p class="text-primary-500 mb-2">Tech Lead</p>
                <p class="text-gray-600 text-sm">Technology enthusiast driving innovation and platform development.</p>
            </div>
        </div>
    </div>
</section>

<!-- Every Mood -->
<section class="my-20 relative bg-accent">

    <!-- SCALLOP TOP FULL WIDTH -->
    <div class="absolute -top-0.5 left-0 w-full leading-none">
        <svg
            class="w-full h-[40px] md:h-[80px]"
            viewBox="0 0 1440 80"
            preserveAspectRatio="none">
            <defs>
                <pattern
                    id="scallop"
                    width="80"
                    height="80"
                    patternUnits="userSpaceOnUse">
                    <circle cx="40" cy="0" r="40" fill="white" />
                </pattern>
            </defs>

            <!-- FULL HEIGHT RECT -->
            <rect width="100%" height="100%" fill="url(#scallop)" />
        </svg>
    </div>

    <!-- MAIN CONTENT -->
    <div class="max-w-7xl mx-auto px-6 pt-24 pb-16 grid lg:grid-cols-2 gap-12 items-center">

        <!-- LEFT IMAGE -->
        <div class="flex justify-center lg:justify-start">
            <div class="relative">

                <!-- Circle -->
                <div class="w-64 h-64 sm:w-80 sm:h-80 lg:w-[420px] lg:h-[420px] flex items-center justify-center animate-float ">
                    <img
                        src="<?php echo IMAGES_URL; ?>/makhana_bowl.png"
                        class="w-full h-full object-contain" />
                </div>
            </div>
        </div>

        <!-- RIGHT CONTENT -->
        <div class="text-center lg:text-left space-y-6">

            <h2 class="scroll-animate-top text-3xl md:text-5xl font-luckiest text-primary-600 mb-3">From Classic to Bold — Discover a Flavor for Every <span class="text-white" style="-webkit-text-stroke: 1px black;">Mood.</span></h2>

            <!-- FEATURES -->
            <ul class="space-y-4 text-xs sm:text-base text-gray-800 max-w-lg mx-auto lg:mx-0">

                <li class="scroll-animate-left flex items-center gap-3 justify-start">
                    <img src="<?php echo IMAGES_URL; ?>/makhana_icon.png" class="w-8 opacity-80 mt-1" />
                    Pure crunch with perfectly balanced salt.
                </li>

                <li class="scroll-animate-left flex items-center gap-3 justify-start">
                    <img src="<?php echo IMAGES_URL; ?>/makhana_icon.png" class="w-8 opacity-80 mt-1" />
                    A bold, fiery blend that excites your taste buds.
                </li>

                <li class="scroll-animate-left flex items-center gap-3 justify-start">
                    <img src="<?php echo IMAGES_URL; ?>/makhana_icon.png" class="w-8 opacity-80 mt-1" />
                    Creamy, rich cheese in every crispy bite.
                </li>

                <li class="scroll-animate-left flex items-center gap-3 justify-start">
                    <img src="<?php echo IMAGES_URL; ?>/makhana_icon.png" class="w-8 opacity-80 mt-1" />
                    Smooth, tangy flavor with a savory twist.
                </li>

            </ul>

            <!-- BUTTON -->
            <div class="flex justify-center lg:justify-start">
                <a href="<?php echo BASE_URL; ?>about-us.php"
                    class="bg-primary-600 text-white font-semibold 
            py-3 px-8 rounded-full 
            shadow-[3px_3px_0_#000] hover:shadow-[4px_3px_0_#000] 
            transition duration-150 scroll-animate-top">
                    Explore Flavors
                </a>
            </div>

        </div>
    </div>

    <!-- SCALLOP (OPPOSITE / FLIPPED) -->
    <div class="absolute -bottom-0.5 left-0 w-full leading-none">
        <svg
            class="w-full h-[50px] sm:h-[60px] md:h-[80px]"
            viewBox="0 0 1440 80"
            preserveAspectRatio="none">
            <defs>
                <pattern
                    id="scallop-bottom"
                    width="80"
                    height="80"
                    patternUnits="userSpaceOnUse">
                    <!-- MOVE CIRCLE TO BOTTOM -->
                    <circle cx="40" cy="80" r="40" fill="white" />
                </pattern>
            </defs>

            <rect width="100%" height="100%" fill="url(#scallop-bottom)" />
        </svg>
    </div>

    <!-- RIGHT FLOATING PRODUCT -->
    <div class="hidden lg:block absolute right-10 bottom-10 rotate-12 animate-float">
        <img
            src="https://cdn-icons-png.flaticon.com/512/2553/2553691.png"
            class="w-20 drop-shadow-xl" />
    </div>

</section>

<!-- Features Section -->
<section class="my-20">
    <svg
        viewBox="0 0 1440 200"
        class="w-full h-8 md:h-10"
        preserveAspectRatio="none">

        <path
            fill="#43a047"
            d="
        M0,100
        C180,20 360,200 540,100
        S900,20 1080,100
        S1260,200 1440,100
        L1440,200 L0,200 Z
        " />
    </svg>

    <div class="py-8 md:py-20 bg-primary-600 text-white">
        <div class="max-w-6xl mx-auto px-4 text-center">

            <!-- TITLE -->
            <h2 class="text-3xl md:text-5xl font-luckiest text-white mb-12 md:mb-20 scroll-animate-top">Why Choose <span class="text-accent" style="-webkit-text-stroke: 1px black;">Earthance?</span></h2>

            <!-- FEATURES -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-8">

                <!-- ITEM 1 -->
                <div class="flex items-center gap-4 text-left justify-center sm:justify-start scroll-animate-left">
                    <span class="flex-shrink-0 w-14 h-14 sm:w-28 sm:h-28 flex items-center justify-center bg-accent-500 rounded-full shadow-md">
                        <i class="fas fa-shopping-bag text-2xl md:text-5xl text-primary-500"></i>
                    </span>
                    <div>
                        <h3 class="font-semibold">Premium Ingredients</h3>
                        <p class="text-sm text-white/80">
                            Only high-quality potatoes & natural seasonings.
                        </p>
                    </div>
                </div>

                <!-- ITEM 2 -->
                <div class="flex items-center gap-4 text-left justify-center sm:justify-start scroll-animate-left">
                    <span class="flex-shrink-0 w-14 h-14 sm:w-28 sm:h-28 flex items-center justify-center bg-accent-500 rounded-full shadow-md">
                        <i class="fas fa-shipping-fast text-2xl md:text-5xl text-primary-500"></i>
                    </span>
                    <div>
                        <h3 class="font-semibold">Fast Delivery</h3>
                        <p class="text-sm text-white/80">
                            Fresh, crunchy snacks delivered to your doorstep.
                        </p>
                    </div>
                </div>

                <!-- ITEM 3 -->
                <div class="flex items-center gap-4 text-left justify-center sm:justify-start scroll-animate-left">
                    <span class="flex-shrink-0 w-14 h-14 sm:w-28 sm:h-28 flex items-center justify-center bg-accent-500 rounded-full shadow-md">
                        <i class="fas fa-heart text-2xl md:text-5xl text-primary-500"></i>
                    </span>
                    <div>
                        <h3 class="font-semibold">Loved Nationwide</h3>
                        <p class="text-sm text-white/80">
                            Trusted by thousands of snack lovers.
                        </p>
                    </div>
                </div>

            </div>

        </div>
    </div>

    <svg
        viewBox="0 0 1440 200"
        class="w-full h-8 md:h-10"
        preserveAspectRatio="none">

        <path
            fill="#43a047"
            d="
        M0,100
        C240,160 480,20 720,100
        S1200,160 1440,100
        L1440,0 L0,0 Z
        " />
    </svg>

</section>

<!-- Authentic Spices -->
<section class="mb-20">
    <!-- CONTENT -->
    <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 md:grid-cols-3 items-center gap-10">

        <!-- LEFT FEATURES -->
        <div class="space-y-10 text-center md:text-right scroll-animate-left">

            <div class="flex flex-col items-center md:items-end">
                <img src="<?php echo IMAGES_URL; ?>/chili.png"
                    class="w-20 h-20 object-contain animate-float">
                <h3 class="font-semibold text-gray-800">Authentic Spices</h3>
                <p class="text-sm text-gray-600">
                    Rich aroma and traditional blends to enhance every dish.
                </p>
            </div>

            <div class="flex flex-col items-center md:items-end">
                <img src="<?php echo IMAGES_URL; ?>/snacks.png"
                    class="w-20 h-20 object-contain animate-float">
                <h3 class="font-semibold text-gray-800">Crispy Snacks</h3>
                <p class="text-sm text-gray-600">
                    Freshly prepared snacks with perfect crunch and taste.
                </p>
            </div>

            <div class="flex flex-col items-center md:items-end">
                <img src="<?php echo IMAGES_URL; ?>/spices.png"
                    class="w-20 h-20 object-contain animate-float">
                <h3 class="font-semibold text-gray-800">Premium Quality</h3>
                <p class="text-sm text-gray-600">
                    Handpicked ingredients ensuring purity and freshness.
                </p>
            </div>

        </div>

        <!-- CENTER IMAGE -->
        <div class="flex justify-center relative">
            <img
                src="<?php echo IMAGES_URL; ?>/makhana_bowl.png"
                alt="Snacks and Spices"
                class="w-60 md:w-80 drop-shadow-xl rounded-2xl animate-float " />
        </div>

        <!-- RIGHT FEATURES -->
        <div class="space-y-10 text-center md:text-left scroll-animate-right">

             <div class="flex flex-col items-center md:items-start">
                <img src="<?php echo IMAGES_URL; ?>/variety_snacks.png"
                    class="w-20 h-20 object-contain animate-float">
                <h3 class="font-semibold text-gray-800">Variety of Snacks</h3>
                <p class="text-sm text-gray-600">
                    From namkeen to traditional treats, something for everyone.
                </p>
            </div>

            <div class="flex flex-col items-center md:items-start">
                <img src="<?php echo IMAGES_URL; ?>/natural_ingredients.png"
                    class="w-20 h-20 object-contain animate-float">
                <h3 class="font-semibold text-gray-800">Natural Ingredients</h3>
                <p class="text-sm text-gray-600">
                    No artificial flavors, only real and natural goodness.
                </p>
            </div>

            <div class="flex flex-col items-center md:items-start">
                <img src="<?php echo IMAGES_URL; ?>/packaging.png"
                    class="w-20 h-20 object-contain animate-float">
                <h3 class="font-semibold text-gray-800">Fresh Packaging</h3>
                <p class="text-sm text-gray-600">
                    Hygienically packed to preserve taste and quality.
                </p>
            </div>

        </div>

    </div>
</section>


<!-- Call to Action -->
<section class="py-16 bg-gradient-to-r from-pink-100 to-purple-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl font-bold text-gray-900 mb-4">Ready to Start Shopping?</h2>
        <p class="text-gray-600 mb-6">Explore our wide range of products and experience the WebStore difference today.</p>
        <a href="<?php echo BASE_URL; ?>shop.php" class="inline-flex items-center bg-primary-500 hover:bg-primary-600 text-white font-semibold py-4 px-8 rounded-full transition shadow-lg hover:shadow-xl">
            <i class="fas fa-shopping-bag mr-2"></i>Browse Products
        </a>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
