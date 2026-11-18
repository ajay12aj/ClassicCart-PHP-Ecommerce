-- =====================================================================
-- ClassicCart - PHP E-Commerce Website
-- Database: classiccart
-- This SQL file creates the complete database structure with sample data.
-- Import this file in phpMyAdmin after creating the database `classiccart`.
-- =====================================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- --------------------------------------------------------
-- Table: admins
-- --------------------------------------------------------
CREATE TABLE `admins` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_admin_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: users
-- --------------------------------------------------------
CREATE TABLE `users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `password` VARCHAR(255) NOT NULL,
  `address` TEXT DEFAULT NULL,
  `city` VARCHAR(100) DEFAULT NULL,
  `state` VARCHAR(100) DEFAULT NULL,
  `pincode` VARCHAR(10) DEFAULT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1 = active, 0 = inactive',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_user_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: categories
-- --------------------------------------------------------
CREATE TABLE `categories` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `image` VARCHAR(255) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1 = active, 0 = inactive',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_category_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: products
-- --------------------------------------------------------
CREATE TABLE `products` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `category_id` INT(11) NOT NULL,
  `name` VARCHAR(200) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `discount_price` DECIMAL(10,2) DEFAULT NULL,
  `stock` INT(11) NOT NULL DEFAULT 0,
  `image` VARCHAR(255) NOT NULL,
  `rating` DECIMAL(2,1) NOT NULL DEFAULT 0.0,
  `status` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1 = active, 0 = inactive',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_product_category` (`category_id`),
  CONSTRAINT `fk_product_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: wishlist
-- --------------------------------------------------------
CREATE TABLE `wishlist` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `product_id` INT(11) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_wishlist_user_product` (`user_id`,`product_id`),
  KEY `idx_wishlist_product` (`product_id`),
  CONSTRAINT `fk_wishlist_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_wishlist_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: cart
-- --------------------------------------------------------
CREATE TABLE `cart` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `product_id` INT(11) NOT NULL,
  `quantity` INT(11) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_cart_user_product` (`user_id`,`product_id`),
  KEY `idx_cart_product` (`product_id`),
  CONSTRAINT `fk_cart_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_cart_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: orders
-- --------------------------------------------------------
CREATE TABLE `orders` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `order_number` VARCHAR(50) NOT NULL,
  `user_id` INT(11) DEFAULT NULL,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `address` TEXT NOT NULL,
  `city` VARCHAR(100) NOT NULL,
  `state` VARCHAR(100) NOT NULL,
  `pincode` VARCHAR(10) NOT NULL,
  `subtotal` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `shipping` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `total` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `payment_method` VARCHAR(50) NOT NULL DEFAULT 'Cash on Delivery',
  `status` ENUM('Pending','Confirmed','Processing','Shipped','Delivered','Cancelled') NOT NULL DEFAULT 'Pending',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_order_number` (`order_number`),
  KEY `idx_order_user` (`user_id`),
  CONSTRAINT `fk_order_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: order_items
-- --------------------------------------------------------
CREATE TABLE `order_items` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `order_id` INT(11) NOT NULL,
  `product_id` INT(11) DEFAULT NULL,
  `product_name` VARCHAR(200) NOT NULL,
  `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `quantity` INT(11) NOT NULL DEFAULT 1,
  `subtotal` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `idx_order_item_order` (`order_id`),
  KEY `idx_order_item_product` (`product_id`),
  CONSTRAINT `fk_order_item_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_order_item_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
-- SAMPLE DATA
-- =====================================================================

-- Default Admin (email: admin@classiccart.com / password: Admin@123)
INSERT INTO `admins` (`id`,`name`,`email`,`password`,`status`) VALUES
(1, 'Administrator', 'admin@classiccart.com', '$2y$10$2.MKthKtorLPcyiQt/8kOu8QniZazdPMRv74hMVCP58N6Mr7i1OWq', 1);

-- Sample Users (user@demo.com / password: User@123)
INSERT INTO `users` (`id`,`name`,`email`,`phone`,`password`,`address`,`city`,`state`,`pincode`,`status`) VALUES
(1, 'Riya Sharma', 'user@demo.com', '8401942581', '$2y$10$iE2nPAqnCwxUezlhi107mOORZqp/uDwesb8Sgw6eILULcX0o3Br6W', '12, Green Park Colony, MG Road', 'New Delhi', 'Delhi', '110001', 1),
(2, 'Aman Verma', 'aman@demo.com', '9123456780', '$2y$10$iE2nPAqnCwxUezlhi107mOORZqp/uDwesb8Sgw6eILULcX0o3Br6W', '45, Lake View Road, Near City Mall', 'Mumbai', 'Maharashtra', '400001', 1);

-- Sample Categories
INSERT INTO `categories` (`id`,`name`,`image`,`description`,`status`) VALUES
(1, 'Men', 'c1.jpg', 'Fashion and clothing for men.', 1),
(2, 'Women', 'c2.jpg', 'Fashion and clothing for women.', 1),
(3, 'Electronics', 'c3.jpg', 'Televisions, phones, chargers and more.', 1),
(4, 'Shoes', 'c4.jpg', 'Footwear for every occasion.', 1),
(5, 'Accessories', 'c5.jpg', 'Watches, sunglasses, belts, bags and more.', 1),
(6, 'Home & Living', 'c6.jpg', 'Essentials to make your home comfortable.', 1);

-- Sample Products
INSERT INTO `products` (`id`,`category_id`,`name`,`description`,`price`,`discount_price`,`stock`,`image`,`rating`,`status`) VALUES
(1, 1, 'Classic Cotton T-Shirt', 'Comfortable 100% cotton t-shirt with a classic fit. Perfect for everyday casual wear. Available in multiple colours.', 599.00, 449.00, 50, 'p1.jpg', 4.5, 1),
(2, 1, 'Slim Fit Denim Jeans', 'Premium quality denim jeans with a modern slim fit. Durable stitching and fade-resistant fabric.', 1499.00, 1199.00, 40, 'p2.jpg', 4.3, 1),
(3, 1, 'Casual Formal Shirt', 'Smart casual shirt that works for both office and outings. Breathable cotton blend fabric.', 899.00, NULL, 25, 'p3.jpg', 4.0, 1),
(4, 4, 'Men Sports Running Shoes', 'Lightweight running shoes with cushioned sole and breathable mesh upper. Ideal for daily workouts.', 1999.00, 1599.00, 30, 'p4.jpg', 4.6, 1),
(5, 4, 'Leather Formal Shoes', 'Classic leather formal shoes with an anti-skid sole. Perfect for work and formal events.', 2499.00, 1999.00, 18, 'p5.jpg', 4.4, 1),
(6, 4, 'Canvas Casual Shoes', 'Trendy canvas sneakers that pair well with any casual outfit. Flexible and easy to clean.', 999.00, 799.00, 35, 'p6.jpg', 4.2, 1),
(7, 2, 'Floral Print Dress', 'Elegant floral print dress with a comfortable fit. Great for parties and casual outings.', 1299.00, 999.00, 22, 'p7.jpg', 4.5, 1),
(8, 2, 'Elegant Kurta Set', 'Traditional kurta set with soft, breathable fabric and stylish embroidery.', 1799.00, 1399.00, 15, 'p8.jpg', 4.7, 1),
(9, 5, 'Designer Handbag', 'Stylish designer handbag with multiple compartments and a secure zip closure.', 2199.00, 1799.00, 20, 'p9.jpg', 4.3, 1),
(10, 3, 'Smart LED TV 43 Inch', 'Full HD smart LED television with built-in streaming apps and Dolby audio support.', 28999.00, 24999.00, 10, 'p10.jpg', 4.8, 1),
(11, 3, 'Wireless Bluetooth Headphones', 'Over-ear wireless headphones with active noise cancellation and 30 hour battery life.', 2499.00, 1999.00, 45, 'p11.jpg', 4.6, 1),
(12, 3, 'Smartphone 128GB', '6.5 inch smartphone with 128GB storage, dual camera and long-lasting battery.', 15999.00, 13999.00, 12, 'p12.jpg', 4.5, 1),
(13, 3, 'USB-C Fast Charger', '25W fast charger compatible with most smartphones and tablets. Safety certified.', 499.00, 399.00, 60, 'p13.jpg', 4.2, 1),
(14, 5, 'Classic Analog Wrist Watch', 'Elegant analog watch with a stainless steel strap and water resistant body.', 1499.00, 1199.00, 28, 'p14.jpg', 4.4, 1),
(15, 5, 'Sunglasses UV Protection', 'Stylish unisex sunglasses with 100% UV protection and durable frames.', 799.00, 599.00, 40, 'p15.jpg', 4.1, 1),
(16, 6, 'Premium Cotton Bed Sheet', 'Soft, comfortable cotton bed sheet set that keeps you cool through the night.', 1099.00, 899.00, 26, 'p16.jpg', 4.3, 1),
(17, 6, 'Ceramic Dinner Set', 'Complete 12 piece ceramic dinner set for families. Microwave and dishwasher safe.', 1899.00, 1499.00, 14, 'p17.jpg', 4.5, 1),
(18, 6, 'Table Reading Lamp', 'Adjustable LED reading lamp with three brightness levels and a warm glow.', 699.00, 549.00, 30, 'p18.jpg', 4.0, 1),
(19, 5, 'Leather Casual Belt', 'Genuine leather casual belt with an anti-buckle design. One size fits all.', 499.00, 349.00, 55, 'p19.jpg', 4.2, 1),
(20, 6, 'Coffee Maker 1.2L', 'Automatic coffee maker with a 1.2 litre capacity and reusable filter.', 2999.00, 2499.00, 0, 'p20.jpg', 3.9, 1);

-- Sample Wishlist entries
INSERT INTO `wishlist` (`user_id`,`product_id`) VALUES
(1, 2),
(1, 11);

-- Sample Cart entries
INSERT INTO `cart` (`user_id`,`product_id`,`quantity`) VALUES
(1, 1, 2),
(1, 13, 1);

-- Sample Orders
INSERT INTO `orders` (`id`,`order_number`,`user_id`,`name`,`email`,`phone`,`address`,`city`,`state`,`pincode`,`subtotal`,`shipping`,`total`,`payment_method`,`status`,`created_at`) VALUES
(1, 'CC-20260905-1001', 1, 'Riya Sharma', 'user@demo.com', '9876543210', '12, Green Park Colony, MG Road', 'New Delhi', 'Delhi', '110001', 898.00, 50.00, 948.00, 'Cash on Delivery', 'Delivered', '2026-09-05 10:30:00'),
(2, 'CC-20260906-1002', 1, 'Riya Sharma', 'user@demo.com', '9876543210', '12, Green Park Colony, MG Road', 'New Delhi', 'Delhi', '110001', 2298.00, 50.00, 2348.00, 'Cash on Delivery', 'Pending', '2026-09-06 14:15:00');

INSERT INTO `order_items` (`order_id`,`product_id`,`product_name`,`price`,`quantity`,`subtotal`) VALUES
(1, 1, 'Classic Cotton T-Shirt', 449.00, 2, 898.00),
(2, 11, 'Wireless Bluetooth Headphones', 1999.00, 1, 1999.00),
(2, 13, 'USB-C Fast Charger', 399.00, 1, 399.00);