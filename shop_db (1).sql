-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 25, 2024 at 01:11 PM
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
-- Database: `shop_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(100) NOT NULL,
  `name` varchar(20) NOT NULL,
  `password` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `name`, `password`) VALUES
(1, 'admin', '6216f8a75fd5bb3d5f22b6f9958cdede3fc086c2'),
(2, 'john', '356a192b7913b04c54574d18c28d46e6395428ab');

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(100) NOT NULL,
  `user_id` int(100) NOT NULL,
  `pid` int(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `price` int(10) NOT NULL,
  `quantity` int(10) DEFAULT NULL,
  `image` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `created_at` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`id`, `user_id`, `product_id`, `comment`, `created_at`) VALUES
(4, 4, 4, 'dasdasdasdas', '2024-09-30');

-- --------------------------------------------------------

--
-- Table structure for table `courier`
--

CREATE TABLE `courier` (
  `id` int(11) NOT NULL,
  `driver_name` varchar(255) NOT NULL,
  `driver_contact` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(15) NOT NULL,
  `status` varchar(50) DEFAULT 'Active',
  `order_id` int(11) DEFAULT NULL,
  `driver_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courier`
--

INSERT INTO `courier` (`id`, `driver_name`, `driver_contact`, `email`, `password`, `status`, `order_id`, `driver_image`) VALUES
(5, 'Johez', '09123456789', 'john@gmail.com', '1111', 'Active', NULL, '71jAqF3N2UL.jpg'),
(7, 'gdf', '54645', 'test@gmail.com', '1111', 'Active', NULL, 'couple-hiking-mountain-climbing-1296x728-header.jpg'),
(13, 'John11', '54645', 'john@gmail.com', '011c945f30ce2cb', 'active', NULL, '35cbe99c-c752-4787-a2ca-fbbec341b158.__CR0,0,600,450_PT0_SX600_V1___.jpg'),
(14, 'gdf', '54645', 'john@gmail.com', '356a192b7913b04', 'active', NULL, '71jAqF3N2UL.jpg'),
(15, 'test', '54645', 'john@gmail.com', '356a192b7913b04', 'active', NULL, 'a484b3be-64e0-486f-b509-cf5c66cc09f1.__CR0,0,970,600_PT0_SX970_V1___.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `delivery_history`
--

CREATE TABLE `delivery_history` (
  `id` int(11) NOT NULL,
  `courier_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `delivery_address` varchar(255) NOT NULL,
  `delivery_status` varchar(50) NOT NULL,
  `delivered_on` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `delivery_history`
--

INSERT INTO `delivery_history` (`id`, `courier_id`, `order_id`, `delivery_address`, `delivery_status`, `delivered_on`) VALUES
(23, 7, 12, 'flat no. dsad, fdsfsd, dsadsa, fsdfds, dasdsa - 423432', 'In Progress', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `delivery_reports`
--

CREATE TABLE `delivery_reports` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `courier_id` int(11) DEFAULT NULL,
  `delivery_status` varchar(50) DEFAULT NULL,
  `delivered_on` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `delivery_reports`
--

INSERT INTO `delivery_reports` (`id`, `order_id`, `courier_id`, `delivery_status`, `delivered_on`) VALUES
(8, 11, 7, 'Package Out for Delivery', '2024-10-07 00:17:03'),
(32, 15, 5, 'Delivered', '2024-10-13 22:35:56'),
(33, 26, 5, 'Delivered', '2024-10-13 22:36:34'),
(34, 34, 5, 'Delivered', '2024-10-23 23:19:00');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(100) NOT NULL,
  `user_id` int(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `number` varchar(12) NOT NULL,
  `message` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(100) NOT NULL,
  `user_id` int(100) NOT NULL,
  `name` varchar(20) NOT NULL,
  `number` varchar(10) NOT NULL,
  `email` varchar(50) NOT NULL,
  `method` varchar(50) NOT NULL,
  `address` varchar(500) NOT NULL,
  `total_products` varchar(1000) NOT NULL,
  `total_price` int(100) NOT NULL,
  `placed_on` date NOT NULL DEFAULT current_timestamp(),
  `payment_status` varchar(20) NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `name`, `number`, `email`, `method`, `address`, `total_products`, `total_price`, `placed_on`, `payment_status`) VALUES
(11, 4, 'das', '23423', 'john@gmail.com', 'cash on delivery', 'flat no. dsad, dsffsd, asdasdsa, fdsfsd, asddas - 2342', 'admin (23424 x 1) - ', 23424, '2024-09-30', 'canceled'),
(12, 4, 'admin', '234342', 'john@gmail.com', 'cash on delivery', 'flat no. dsad, fdsfsd, dsadsa, fsdfds, dasdsa - 423432', 'sdaas (423 x 1) - ', 423, '2024-09-30', 'In Progress'),
(13, 4, 'das', '3424', 'john@gmail.com', 'cash on delivery', 'flat no. dsadas, dasdsa, dasdas, dasdas, dsadas - 423432', 'admin (23424 x 2) - ', 46848, '2024-09-30', 'completed'),
(15, 4, 'dasdas', '23424', 'john@gmail.com', 'cash on delivery', 'flat no. dsadas, asfaf, dsadas, asdfsdf, dsaasd - 432423', 'admin (23424 x 1) - ', 23424, '2024-09-30', 'paid'),
(26, 3, 'john', '342534', 'john@gmail.com', 'cash on delivery', 'flat no. dasdas, dasdda, dsadsa, das, 1sdfdf - 3453', 'admin (23424 x 1) - ', 23424, '2024-10-13', 'paid'),
(27, 3, 'admin', '2342', 'test@gmail.com', 'cash on delivery', 'flat no. dsadas, dasdda, dasda, fdsfsd, 111 - 324', 'admin (23424 x 1) - ', 23424, '2024-10-13', 'pending'),
(28, 3, 'dasdas', '32423', 'test@gmail.com', 'cash on delivery', 'flat no. dasdsa, rswes, dsadas, rfsdfsd, dsadas - 1111', 'admin (23424 x 2) - hello (324 x 2) - ', 47496, '2024-10-23', 'pending'),
(29, 3, 'dasdas', '3242', 'johnMateo@gmail.com', 'cash on delivery', 'flat no. dasdas, 423423, dasdas, 423342, dsadas - 423', 'admin (23424 x 1) - ', 23424, '2024-10-23', 'pending'),
(30, 3, 'admin', '321', 'test@gmail.com', 'cash on delivery', 'flat no. dsadas, 312, dsadas, dasdas, dsadas - 2131', 'admin (23424 x 1) - ', 23424, '2024-10-23', 'pending'),
(31, 3, 'john', '2342342', 'test@gmail.com', 'cash on delivery', 'flat no. 2342, 4324, 4242, 23424, 3424 - 23423', 'admin (23424 x 1) - ', 23424, '2024-10-23', 'pending'),
(32, 3, '', '', '', '', '', '1', 423, '2024-10-23', 'Pending'),
(33, 3, '', '', '', '', '', '1', 23424, '2024-10-23', 'Pending'),
(34, 3, 'admin', '423', 'john@gmail.com', 'cash on delivery', 'flat no. dasdas, 423, dasdasdsa, 432, 234sada - 423', 'hello (324 x 1) - ', 324, '2024-10-23', 'In Progress'),
(35, 3, 'das', '867867876', 'john@gmail.com', 'cash on delivery', 'flat no. dsad, 867867, dasdasdsa, 867867, dasdas - 6786', 'admin444 (345 x 3) - ', 1035, '2024-10-23', 'pending'),
(36, 3, 'admin', '1231', 'johnMateo@gmail.com', 'cash on delivery', 'flat no. dsadas, dasdda, sdasdas, asda, 1sdfdf - 23432', 'Laptop (423 x 3) - ', 1269, '2024-10-23', 'pending'),
(37, 3, 'john', '2342', 'john@gmail.com', 'cash on delivery', 'flat no. dasdsa, 43242, dasdasdsa, 43242, 43242 - 342423', 'Laptop (423 x 11) - admin (23424 x 9) - ', 215469, '2024-10-23', 'pending'),
(38, 3, 'john', '423423', 'john@gmail.com', 'cash on delivery', 'flat no. 432423, 432423, 432432, 432423, 432432 - 432432', 'hello (324 x 1) - admin444 (345 x 1) - ', 669, '2024-10-23', 'pending'),
(39, 3, 'admin', '342234', 'test@gmail.com', 'cash on delivery', 'flat no. dasdas, asdasda, dasda, dasd, dasdas - 23424', 'admin (23424 x 1) - ', 23424, '2024-10-23', 'pending'),
(40, 3, 'dasdas', '234324', 'john@gmail.com', 'credit card', 'flat no. dsadas, 432423, 2234, 32423, 242 - 23423', 'hello (324 x 1) - ', 324, '2024-10-23', 'pending'),
(41, 3, 'admin', '42423', 'john@gmail.com', 'credit card', 'flat no. dsadas, 42423, dsadas, 42342, 111 - 2342', 'admin (23424 x 1) - ', 23424, '2024-10-23', 'pending'),
(42, 3, 'admin', '234234', 'john@gmail.com', 'cash on delivery', 'flat no. dasdas, 312321, dsadas, dasdas, 111 - 121231', 'hello (324 x 1) - ', 324, '2024-10-24', 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `details` varchar(500) NOT NULL,
  `price` int(10) NOT NULL,
  `image_01` varchar(100) NOT NULL,
  `image_02` varchar(100) NOT NULL,
  `image_03` varchar(100) NOT NULL,
  `category` enum('Laptop','Console','Lifestyle','Headphones','Charger','Accessories') NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `details`, `price`, `image_01`, `image_02`, `image_03`, `category`, `stock`, `created_at`) VALUES
(3, 'Laptop', 'dadsa', 423, '360_F_91078252_i7cx2uJzDzgoJGDdUAHtVAcpjugVauX9.jpg', '360_F_91078252_i7cx2uJzDzgoJGDdUAHtVAcpjugVauX9.jpg', '360_F_91078252_i7cx2uJzDzgoJGDdUAHtVAcpjugVauX9.jpg', 'Console', 29, '2024-10-21 11:34:21'),
(4, 'admin', 'sadas', 23424, '360_F_91078252_i7cx2uJzDzgoJGDdUAHtVAcpjugVauX9.jpg', '360_F_91078252_i7cx2uJzDzgoJGDdUAHtVAcpjugVauX9.jpg', '360_F_91078252_i7cx2uJzDzgoJGDdUAHtVAcpjugVauX9.jpg', 'Headphones', 68, '2024-10-21 11:34:21'),
(5, 'hello', '234', 324, '35cbe99c-c752-4787-a2ca-fbbec341b158.__CR0,0,600,450_PT0_SX600_V1___.jpg', 'a484b3be-64e0-486f-b509-cf5c66cc09f1.__CR0,0,970,600_PT0_SX970_V1___.jpg', '71jAqF3N2UL.jpg', 'Charger', 458, '2024-10-21 11:34:21'),
(6, 'john', '32432', 2342, '71jAqF3N2UL.jpg', '71jAqF3N2UL.jpg', '71jAqF3N2UL.jpg', 'Laptop', 432423, '2024-10-21 11:34:21'),
(7, 'admin444', 'dfgdfgfd', 345, '71jAqF3N2UL.jpg', '71jAqF3N2UL.jpg', '71jAqF3N2UL.jpg', 'Console', 4349, '2024-10-21 12:08:17');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(100) NOT NULL,
  `name` varchar(20) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`) VALUES
(3, 'john', 'john@gmail.com', '6216f8a75fd5bb3d5f22b6f9958cdede3fc086c2'),
(4, 'john', 'test@gmail.com', '6216f8a75fd5bb3d5f22b6f9958cdede3fc086c2');

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `id` int(100) NOT NULL,
  `user_id` int(100) NOT NULL,
  `pid` int(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `price` int(100) NOT NULL,
  `image` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `wishlist`
--

INSERT INTO `wishlist` (`id`, `user_id`, `pid`, `name`, `price`, `image`) VALUES
(13, 4, 4, 'admin', 23424, '360_F_91078252_i7cx2uJzDzgoJGDdUAHtVAcpjugVauX9.jpg');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id_fk` (`user_id`),
  ADD KEY `product_id_fk` (`product_id`);

--
-- Indexes for table `courier`
--
ALTER TABLE `courier`
  ADD PRIMARY KEY (`id`),
  ADD KEY `courier_ibfk_1` (`order_id`);

--
-- Indexes for table `delivery_history`
--
ALTER TABLE `delivery_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `delivery_history_ibfk_1` (`courier_id`),
  ADD KEY `delivery_history_ibfk_2` (`order_id`);

--
-- Indexes for table `delivery_reports`
--
ALTER TABLE `delivery_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `delivery_reports_ibfk_1` (`order_id`),
  ADD KEY `delivery_reports_ibfk_2` (`courier_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `courier`
--
ALTER TABLE `courier`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `delivery_history`
--
ALTER TABLE `delivery_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `delivery_reports`
--
ALTER TABLE `delivery_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `courier`
--
ALTER TABLE `courier`
  ADD CONSTRAINT `courier_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `delivery_history`
--
ALTER TABLE `delivery_history`
  ADD CONSTRAINT `delivery_history_ibfk_1` FOREIGN KEY (`courier_id`) REFERENCES `courier` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `delivery_history_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `delivery_reports`
--
ALTER TABLE `delivery_reports`
  ADD CONSTRAINT `delivery_reports_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `delivery_reports_ibfk_2` FOREIGN KEY (`courier_id`) REFERENCES `courier` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
