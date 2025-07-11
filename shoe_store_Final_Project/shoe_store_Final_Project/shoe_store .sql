-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 12, 2025 at 04:34 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `shoe_store`
--

-- --------------------------------------------------------

--
-- Table structure for table `brands`
--

CREATE TABLE `brands` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `brands`
--

INSERT INTO `brands` (`id`, `name`) VALUES
(2, 'Adidas'),
(1, 'Nike');

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `used_shoe_id` int(11) DEFAULT NULL,
  `product_type` enum('new','used') NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `price` decimal(20,0) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `type` enum('men','women','kids') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `type`) VALUES
(1, 'Boots', ''),
(2, 'Snikers', ''),
(7, 'sniker', 'men');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_number` varchar(20) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `shipping_name` varchar(100) NOT NULL,
  `shipping_address` text NOT NULL,
  `shipping_city` varchar(50) NOT NULL,
  `shipping_state` varchar(50) NOT NULL,
  `shipping_zip` varchar(20) NOT NULL,
  `shipping_phone` varchar(20) NOT NULL,
  `billing_name` varchar(100) NOT NULL,
  `billing_address` text NOT NULL,
  `billing_city` varchar(50) NOT NULL,
  `billing_state` varchar(50) NOT NULL,
  `billing_zip` varchar(20) NOT NULL,
  `payment_method` enum('card','paypal','cod') NOT NULL,
  `card_last4` varchar(4) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `order_status` enum('pending','accepted','cancelled') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `order_number`, `total_amount`, `shipping_name`, `shipping_address`, `shipping_city`, `shipping_state`, `shipping_zip`, `shipping_phone`, `billing_name`, `billing_address`, `billing_city`, `billing_state`, `billing_zip`, `payment_method`, `card_last4`, `created_at`, `order_status`) VALUES
(11, 1, 'ORD-684AE3A3AEECA', 4665.60, 'Customer', 'No:195/A Suduwella, Madampe, 61230', 'Madampe', 'madampe', '556', '0773187196', 'Customer', 'No:195/A Suduwella, Madampe, 61230', 'Madampe', 'madampe', '556', 'cod', NULL, '2025-06-12 14:26:43', 'pending'),
(12, 1, 'ORD-684AE41F2E7DB', 2008.80, 'Customer', 'No:195/A Suduwella, Madampe, 61230', 'Madampe', 'madampe', '556', '0773187196', 'Customer', 'No:195/A Suduwella, Madampe, 61230', 'Madampe', 'madampe', '556', 'cod', NULL, '2025-06-12 14:28:47', 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_type` enum('new','used') NOT NULL DEFAULT 'new',
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_type`, `quantity`, `price`) VALUES
(12, 11, 30, 'new', 3, 1440.00),
(13, 12, 33, 'new', 3, 620.00);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `brand_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `discount_price` decimal(10,2) DEFAULT NULL,
  `gender` enum('men','women','kids') NOT NULL,
  `size` varchar(50) NOT NULL,
  `color` varchar(50) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `image` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `brand_id`, `category_id`, `name`, `description`, `price`, `discount_price`, `gender`, `size`, `color`, `quantity`, `image`, `created_at`, `updated_at`) VALUES
(20, 2, 7, 'Men Red Shoe', 'From the boardroom to the streets—walk with purpose. Your shoes tell your story. Make it legendary.', 1350.00, NULL, 'men', 'M', 'Red', 20, 'product_1748328243_68355f33c7235.jpg', '2025-05-27 06:44:03', '2025-06-12 13:58:28'),
(26, 2, 7, 'Men Blue Shoe', 'Step into style with shoes that speak louder than words. Fashion, comfort, and confidence—right beneath your feet.', 1500.00, 1450.00, 'men', 'L', 'Blue', 20, 'product_1748328170_68355eea120ab.jpg', '2025-05-27 06:42:50', '2025-06-12 13:24:41'),
(28, 1, 7, 'Men ash Shoe', 'Every step counts. Choose shoes that keep up with your hustle and highlight your style every day.', 1600.00, 1550.00, 'men', 'L', 'Ash', 20, 'product_1748328339_68355f93c0148.jpg', '2025-05-27 06:45:39', '2025-06-12 11:58:17'),
(29, 1, 7, 'Men ash Shoe', 'Every step counts. Choose shoes that keep up with your hustle and highlight your style every day.', 1600.00, 1550.00, 'men', 'L', 'Ash', 20, 'product_1748328339_68355f93c8d03.jpg', '2025-05-27 06:45:39', '2025-06-12 13:37:25'),
(30, 1, 7, 'Men Blue Shoe', 'Every step counts. Choose shoes that keep up with your hustle and highlight your style every day.', 1500.00, 1440.00, 'men', 'L', 'Blue', 17, 'product_1748328378_68355fba0b962.jpg', '2025-05-27 06:46:18', '2025-06-12 14:26:43'),
(31, 2, 7, 'Women Ash Shoe', 'Every step counts. Choose shoes that keep up with your hustle and highlight your style every day.', 1200.00, 1120.00, 'women', 'L', 'Ash', 20, 'product_1748328402_68355fd28aa05.jpg', '2025-05-27 06:46:42', '2025-06-12 11:58:14'),
(32, 2, 7, 'Women White Shoe', 'Make your first impression unforgettable. Slip into designs that define who you are with every confident stride.', 1480.00, 1450.00, 'women', 'L', 'white', 20, 'product_1748328551_68356067cf326.jpg', '2025-05-27 06:49:11', '2025-06-12 11:58:22'),
(33, 1, 7, 'Kids blue Shoe', 'Make your first impression unforgettable. Slip into designs that define who you are with every confident stride.', 650.00, 620.00, 'kids', 'S', 'Blue', 17, 'product_1748328602_6835609a1dae1.jpg', '2025-05-27 06:50:02', '2025-06-12 14:28:47');

-- --------------------------------------------------------

--
-- Table structure for table `used_shoes`
--

CREATE TABLE `used_shoes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `brand` varchar(50) NOT NULL,
  `size` varchar(20) NOT NULL,
  `color` varchar(30) NOT NULL,
  `gender` enum('men','women','kids') NOT NULL,
  `category` varchar(50) NOT NULL,
  `shoe_condition` varchar(20) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `description` text DEFAULT NULL,
  `image` varchar(255) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `used_shoes`
--

INSERT INTO `used_shoes` (`id`, `user_id`, `name`, `brand`, `size`, `color`, `gender`, `category`, `shoe_condition`, `price`, `quantity`, `description`, `image`, `status`, `created_at`) VALUES
(3, 1, 'Kids Red Shoe', 'nike', 'S', 'Red', 'kids', 'Sniker', 'good', 350.00, 1, 'Your journey deserves the right pair. Walk farther, faster, and fiercer in shoes that never hold you back.', 'used_6835610da12ad.jpg', 'approved', '2025-05-27 06:51:57'),
(4, 6, 'Women Red Shoe', 'nike', 'L', 'white', 'women', 'Sniker', 'fair', 250.00, 5, 'From the boardroom to the streets—walk with purpose. Your shoes tell your story. Make it legendary.', 'used_6835f2881313b.jpg', 'approved', '2025-05-27 17:12:40');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `role` enum('admin','customer') DEFAULT 'customer',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `phone`, `address`, `role`, `created_at`) VALUES
(1, 'Customer', 'customer@gmail.com', '$2y$10$lwdSyy26MXK0RKxIDnj.TuHESQ8k2d94aBOThibFYitIVhb/qQFIS', '0773187196', 'No:195/A Suduwella, Madampe, 61230', 'customer', '2025-05-21 13:02:53'),
(6, 'admin', 'admin@gmail.com', '$2y$10$GWSUBzwUFcrOw6IShrRSfeeq17jWLvk3P7PI45IoLxqlFRJpizOqm', '', '', 'admin', '2025-05-27 14:42:35');

-- --------------------------------------------------------

--
-- Table structure for table `uused_shoes`
--

CREATE TABLE `uused_shoes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `brand` varchar(50) NOT NULL,
  `category` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `size` varchar(20) NOT NULL,
  `color` varchar(30) NOT NULL,
  `gender` enum('men','women','kids') NOT NULL,
  `conditions` enum('new','like_new','good','fair') NOT NULL,
  `image` varchar(255) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `brands`
--
ALTER TABLE `brands`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `used_shoe_id` (`used_shoe_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `brand_id` (`brand_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `used_shoes`
--
ALTER TABLE `used_shoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `uused_shoes`
--
ALTER TABLE `uused_shoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `brands`
--
ALTER TABLE `brands`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `used_shoes`
--
ALTER TABLE `used_shoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `uused_shoes`
--
ALTER TABLE `uused_shoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `cart_ibfk_3` FOREIGN KEY (`used_shoe_id`) REFERENCES `uused_shoes` (`id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`),
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

--
-- Constraints for table `used_shoes`
--
ALTER TABLE `used_shoes`
  ADD CONSTRAINT `used_shoes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `uused_shoes`
--
ALTER TABLE `uused_shoes`
  ADD CONSTRAINT `uused_shoes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
