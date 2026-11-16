-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 16, 2026 at 11:23 AM
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
-- Database: `snackshop`
--

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(50) NOT NULL DEFAULT 'Others',
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `name`, `description`, `category`, `price`, `image`, `created_at`) VALUES
(1, 'Chocolate Bar', 'Delicious milk chocolate', 'Chocolate', 4.50, '69e0696ea671c.jpg', '2026-04-12 20:33:30'),
(2, 'Potato Chips', 'Crispy salted chips', 'Chips', 3.00, '69e026901f399.jpg', '2026-04-12 20:33:30'),
(3, 'Ice Cream Sandwich', 'Creamy ice cream between soft cookies', 'Ice Cream', 6.00, '69e065289a96c.jpg', '2026-04-12 20:33:30'),
(4, 'Cookies', 'Crunchy chocolate cookies', 'Cookies', 4.20, '69e065b8b75af.jpg', '2026-04-12 20:33:30'),
(5, 'Soft Drink', 'Refreshing cold drink', 'Drinks', 6.00, '69e0273c8dec4.jpg', '2026-04-12 20:33:30'),
(7, 'White Chocolate', 'Smooth and creamy white chocolate', 'Chocolate', 5.00, '69e065ffe0ccd.jpg', '2026-04-13 10:05:00'),
(8, 'BBQ Chips', 'Smoky BBQ flavoured crispy chips', 'Chips', 3.50, '69e05234698f1.jpg', '2026-04-13 10:10:00'),
(9, 'Sour Cream Chips', 'Tangy sour cream and onion chips', 'Chips', 3.00, '69e05249a75f1.jpg', '2026-04-13 10:15:00'),
(10, 'Vanilla Ice Cream', 'Classic smooth vanilla ice cream cup', 'Ice Cream', 4.50, '69e0535f54bc4.jpg', '2026-04-13 10:20:00'),
(11, 'Strawberry Ice Cream', 'Sweet strawberry flavoured ice cream', 'Ice Cream', 4.50, '69e055cc5e148.jpg', '2026-04-13 10:25:00'),
(12, 'Butter Cookies', 'Melt-in-your-mouth buttery cookies', 'Cookies', 3.80, '69e056fda9fae.jpg', '2026-04-13 10:30:00'),
(13, 'Oreo Cookies', 'Classic cream-filled chocolate sandwich cookies', 'Cookies', 4.00, '69e05781640fa.jpg', '2026-04-13 10:35:00'),
(14, 'Orange Juice', 'Fresh squeezed orange juice 250ml', 'Drinks', 3.50, '69e057eef0c62.jpg', '2026-04-13 10:40:00'),
(15, 'Green Tea', 'Refreshing cold green tea with honey', 'Drinks', 0.80, '69e05860b66aa.jpg', '2026-04-13 10:45:00'),
(16, 'Gummy Bears', 'Colourful fruity gummy bear candies', 'Others', 2.50, '69e058c643563.jpg', '2026-04-13 10:50:00'),
(17, 'Popcorn', 'Lightly salted butter popcorn', 'Others', 15.00, '69e0592761fd0.jpg', '2026-04-13 10:55:00'),
(18, 'Peanut Butter Cup', 'Chocolate cup filled with creamy peanut butter', 'Chocolate', 3.20, '69e059643addf.jpg', '2026-04-13 11:00:00'),
(19, 'Mineral Water', 'Pure natural mineral water 500ml', 'Drinks', 0.50, '69e059cc35ba6.jpg', '2026-04-13 11:05:00'),
(20, 'Wafer Sticks', 'Crispy wafer sticks with chocolate filling', 'Others', 2.80, '69e05a5153da4.jpg', '2026-04-13 11:10:00'),
(21, 'Macaron (per piece)', 'Macarons are a delicate French dessert made from almond flour, egg whites, and sugar.', 'Cookies', 5.00, '69e061036d194.jpg', '2026-04-16 04:09:24'),
(22, 'Chocolate Cake (per slice)', 'Chocolate cake is rich and moist, made with premium cocoa for a deep chocolate flavor.', 'Cake', 11.50, '69e0620a04d31.jpg', '2026-04-16 04:13:49'),
(23, 'Pudding', 'Pudding is a soft, creamy dessert made from milk and sugar, with a smooth texture and sweet flavor.', 'Others', 5.00, '69e06390c5aa8.jpg', '2026-04-16 04:20:17'),
(24, 'Souffle', 'A soufflé is a light and fluffy dish made from eggs that rises during baking and has a soft texture.', 'Cake', 8.00, '69e064589d0be.jpg', '2026-04-16 04:23:28'),
(25, 'Cup Cake', 'A cupcake is a small, single-serving cake that is soft, sweet, and usually topped with frosting.', 'Cake', 5.00, '69e06504bce39.jpg', '2026-04-16 04:26:28');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
