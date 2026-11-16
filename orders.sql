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
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `orders_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `address_id` int(11) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `cancel_reason` varchar(500) NOT NULL DEFAULT '',
  `status` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`orders_id`, `user_id`, `address_id`, `total`, `cancel_reason`, `status`) VALUES
(1, 2, 1, 6.30, '', 'Pending'),
(2, 2, 1, 10.20, '', 'Pending'),
(3, 22, 2, 4.20, '', 'Cancelled'),
(4, 22, 2, 8.00, '', 'Cancelled'),
(5, 2, 1, 2.00, '', 'Pending'),
(6, 2, 1, 50.20, '', 'Completed'),
(8, 2, 1, 52.80, '', 'Completed'),
(9, 5, 3, 29.00, '', 'Completed'),
(10, 5, 4, 9.00, '', 'Completed'),
(11, 5, 3, 6.00, '', 'Completed'),
(12, 5, 3, 4.20, '', 'Processing'),
(13, 5, 3, 1.80, '', 'Cancelled'),
(14, 5, 3, 1.80, 'Changed mind', 'Completed'),
(15, 5, 4, 24.00, '', 'Completed'),
(16, 5, 3, 6.00, '', NULL),
(17, 5, 3, 6.00, '', 'Completed'),
(18, 5, 4, 4.20, 'Changed mind', 'Cancelled'),
(19, 5, 3, 2.50, '', 'Completed'),
(20, 5, 3, 8.40, 'Changed mind', 'Cancelled'),
(21, 5, 3, 9.00, '', 'Completed'),
(22, 5, 3, 1.80, '', 'Cancelled'),
(23, 5, 3, 6.00, 'Changed mind', 'Cancelled');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`orders_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `orders_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
