--
-- Database Seed Data
-- WebStore eCommerce Platform
-- Run this after database.sql to populate sample data
--

USE ecommerce_db;

-- --------------------------------------------------------

--
-- Dumping data for table `users`
--

-- Default admin user (password: admin123)
INSERT INTO `users` (`name`, `email`, `mobile`, `password`, `role`, `status`, `address`, `city`, `state`, `pincode`, `created_at`) VALUES
('Administrator', 'admin@gmail.com', '9876543210', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1, '123 Admin Street', 'Mumbai', 'Maharashtra', '400001', NOW());

-- --------------------------------------------------------

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`name`, `image`) VALUES
('Snacks & Namkeen', 'snacks.jpg'),
('Masala & Spices', 'spices.jpg');

-- --------------------------------------------------------

--
-- Dumping data for table `products`
--

-- Snacks & Namkeen (Category 1)
INSERT INTO `products` (`name`, `description`, `category_id`, `price`, `original_price`, `stock`, `image`, `gallery`, `status`, `created_at`) VALUES
('Roasted Makhana Premium', 'Crispy roasted fox nuts (makhana) seasoned with Himalayan pink salt. High in protein, gluten-free, perfect healthy snack. 100g pack.', 1, 149.00, 249.00, 100, 'https://images.unsplash.com/photo-1599490659213-e2b9527bd087?w=400', NULL, 1, NOW()),
('Masala Makhana', 'Spicy and flavorful roasted makhana with authentic Indian masala. Crunchy low-calorie snack. 100g pack.', 1, 159.00, 259.00, 80, 'https://images.unsplash.com/photo-1604467707321-70c1b85d7cd6?w=400', NULL, 1, NOW()),
('Aloo Bhujia', 'Classic crispy potato noodles (bhujia) with traditional spices. Haldiram style taste. 400g pack.', 1, 129.00, 199.00, 150, 'https://images.unsplash.com/photo-1604467707321-70c1b85d7cd6?w=400', NULL, 1, NOW()),
('Moong Dal Namkeen', 'Crunchy fried moong dal with mild spices. Popular Indian tea-time snack. 400g pack.', 1, 139.00, 219.00, 120, 'https://images.unsplash.com/photo-1604467707321-70c1b85d7cd6?w=400', NULL, 1, NOW()),
('Chana Choor Garam', 'Spicy and tangy roasted chickpea flakes. Street style chana chor with masala. 500g pack.', 1, 119.00, 189.00, 90, 'https://images.unsplash.com/photo-1604467707321-70c1b85d7cd6?w=400', NULL, 1, NOW()),
('Kurkure Masala', 'Crunchy corn curls with desi masala flavor. Perfect party snack. 100g pack.', 1, 20.00, 35.00, 200, 'https://images.unsplash.com/photo-1604467707321-70c1b85d7cd6?w=400', NULL, 1, NOW()),
('Soya Sticks Crunchy', 'Crispy soya sticks with spicy seasoning. High protein crunchy snack. 200g pack.', 1, 89.00, 149.00, 75, 'https://images.unsplash.com/photo-1604467707321-70c1b85d7cd6?w=400', NULL, 1, NOW()),
('Peanut Chikki', 'Traditional gajak made with roasted peanuts and jaggery. Healthy winter sweet snack. 250g pack.', 1, 99.00, 159.00, 60, 'https://images.unsplash.com/photo-1604467707321-70c1b85d7cd6?w=400', NULL, 1, NOW()),
('Khatta Meetha Mixture', 'Sweet and sour mixture with sev, peanuts, and raisins. Popular namkeen mix. 400g pack.', 1, 149.00, 229.00, 85, 'https://images.unsplash.com/photo-1604467707321-70c1b85d7cd6?w=400', NULL, 1, NOW()),
('Methi Mathri', 'Crispy fenugreek flavored savory crackers. Traditional Rajasthani snack. 500g pack.', 1, 179.00, 279.00, 70, 'https://images.unsplash.com/photo-1604467707321-70c1b85d7cd6?w=400', NULL, 1, NOW()),

-- Masala & Spices (Category 2)
('Turmeric Powder Premium', 'High-curcumin Lakadong turmeric powder. Organic, pure, and aromatic. 250g pack.', 2, 89.00, 149.00, 100, 'https://images.unsplash.com/photo-1596040033229-a9821ebd058d?w=400', NULL, 1, NOW()),
('Red Chili Powder', 'Premium Kashmiri red chili powder. Rich color, mild heat. Perfect for curries. 250g pack.', 2, 129.00, 199.00, 80, 'https://images.unsplash.com/photo-1596040033229-a9821ebd058d?w=400', NULL, 1, NOW()),
('Garam Masala', 'Authentic North Indian garam masala blend. Freshly ground whole spices. 100g pack.', 2, 149.00, 229.00, 90, 'https://images.unsplash.com/photo-1596040033229-a9821ebd058d?w=400', NULL, 1, NOW()),
('Cumin Seeds Whole', 'Premium quality whole jeera. Aromatic and flavorful. Essential for tadka. 100g pack.', 2, 79.00, 129.00, 110, 'https://images.unsplash.com/photo-1596040033229-a9821ebd058d?w=400', NULL, 1, NOW()),
('Coriander Powder', 'Freshly ground dhaniya powder. Pure and aromatic. No additives. 250g pack.', 2, 69.00, 119.00, 95, 'https://images.unsplash.com/photo-1596040033229-a9821ebd058d?w=400', NULL, 1, NOW()),
('Pav Bhaji Masala', 'Authentic Mumbai-style pav bhaji masala. Perfect blend of spices. 100g pack.', 2, 59.00, 99.00, 120, 'https://images.unsplash.com/photo-1596040033229-a9821ebd058d?w=400', NULL, 1, NOW()),
('Chole Masala', 'Punjabi style chole masala for rich and spicy chickpea curry. 100g pack.', 2, 69.00, 109.00, 85, 'https://images.unsplash.com/photo-1596040033229-a9821ebd058d?w=400', NULL, 1, NOW()),
('Kitchen King Masala', 'All-purpose curry masala. Perfect for everyday Indian cooking. 100g pack.', 2, 89.00, 139.00, 100, 'https://images.unsplash.com/photo-1596040033229-a9821ebd058d?w=400', NULL, 1, NOW()),
('Kasuri Methi', 'Dried fenugreek leaves. Authentic aroma for Indian curries and parathas. 50g pack.', 2, 49.00, 79.00, 75, 'https://images.unsplash.com/photo-1596040033229-a9821ebd058d?w=400', NULL, 1, NOW()),
('Mustard Seeds', 'Yellow mustard seeds (rai) for tempering and pickles. Fresh and potent. 100g pack.', 2, 39.00, 69.00, 130, 'https://images.unsplash.com/photo-1596040033229-a9821ebd058d?w=400', NULL, 1, NOW()),
('Cardamom Green Whole', 'Premium green elaichi whole. Aromatic and flavorful. 50g pack.', 2, 199.00, 299.00, 50, 'https://images.unsplash.com/photo-1596040033229-a9821ebd058d?w=400', NULL, 1, NOW()),
('Black Pepper Powder', 'Freshly ground kali mirch. Strong aroma and pungent flavor. 100g pack.', 2, 119.00, 179.00, 80, 'https://images.unsplash.com/photo-1596040033229-a9821ebd058d?w=400', NULL, 1, NOW());

-- --------------------------------------------------------

--
-- Dumping data for table `coupons`
--

INSERT INTO `coupons` (`code`, `type`, `value`, `min_order`, `max_discount`, `expiry_date`, `usage_limit`, `used_count`, `status`, `created_at`) VALUES
('WELCOME10', 'percent', 10.00, 500.00, 500.00, DATE_ADD(CURDATE(), INTERVAL 30 DAY), 100, 0, 1, NOW()),
('FLAT200', 'fixed', 200.00, 1000.00, NULL, DATE_ADD(CURDATE(), INTERVAL 30 DAY), 50, 0, 1, NOW()),
('SUMMER25', 'percent', 25.00, 2000.00, 1000.00, DATE_ADD(CURDATE(), INTERVAL 60 DAY), 200, 0, 1, NOW()),
('NEWUSER15', 'percent', 15.00, 0.00, 750.00, DATE_ADD(CURDATE(), INTERVAL 15 DAY), 1, 0, 1, NOW()),
('SNACKS20', 'percent', 20.00, 300.00, 100.00, DATE_ADD(CURDATE(), INTERVAL 30 DAY), 100, 0, 1, NOW()),
('SPICES10', 'percent', 10.00, 200.00, 50.00, DATE_ADD(CURDATE(), INTERVAL 30 DAY), 100, 0, 1, NOW());

-- --------------------------------------------------------

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`name`, `email`, `phone`, `message`, `created_at`) VALUES
('Rahul Sharma', 'rahul@example.com', '9876543210', 'I am interested in becoming a seller on your platform. Please provide more information about the vendor registration process.', DATE_SUB(NOW(), INTERVAL 5 DAY)),
('Priya Patel', 'priya@example.com', '9876543211', 'My order #123 has not been delivered yet. It has been 10 days since I placed the order. Please check the status.', DATE_SUB(NOW(), INTERVAL 3 DAY)),
('Amit Kumar', 'amit@example.com', '9876543212', 'Great website! I love the product variety. Do you have any upcoming sales on electronics?', DATE_SUB(NOW(), INTERVAL 2 DAY)),
('Sneha Gupta', 'sneha@example.com', '9876543213', 'I want to return a product I purchased last week. The size is not fitting. How can I initiate the return?', DATE_SUB(NOW(), INTERVAL 1 DAY)),
('Vikram Singh', 'vikram@example.com', '9876543214', 'Do you offer cash on delivery option? I prefer paying after receiving the product.', NOW());

-- --------------------------------------------------------

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`product_id`, `user_id`, `rating`, `comment`, `created_at`) VALUES
(1, 1, 5, 'Best makhana I have tasted! Crispy and perfectly salted. Great healthy snack option.', DATE_SUB(NOW(), INTERVAL 5 DAY)),
(2, 1, 5, 'Masala makhana is so addictive! Spicy but not overwhelming. Ordering more soon.', DATE_SUB(NOW(), INTERVAL 4 DAY)),
(4, 1, 4, 'Classic moong dal, very fresh and crispy. Perfect with evening tea.', DATE_SUB(NOW(), INTERVAL 3 DAY)),
(11, 1, 5, 'Authentic garam masala! Perfect blend, makes my curries taste like restaurant style.', DATE_SUB(NOW(), INTERVAL 6 DAY)),
(12, 1, 5, 'Turmeric powder has beautiful color and aroma. Very pure and authentic.', DATE_SUB(NOW(), INTERVAL 4 DAY)),
(16, 1, 4, 'Pav bhaji masala is excellent! Tastes just like Mumbai street food.', DATE_SUB(NOW(), INTERVAL 3 DAY));
