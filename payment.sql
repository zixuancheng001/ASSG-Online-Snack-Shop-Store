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
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `payment_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `payment_method` varchar(20) NOT NULL,
  `payment_status` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment`
--

INSERT INTO `payment` (`payment_id`, `order_id`, `payment_method`, `payment_status`) VALUES
(0, 6, 'Online', 'Refunded'),
(0, 5, 'Online', 'Paid'),
(0, 2, 'Online', 'Paid'),
(0, 1, 'Online', 'Paid'),
(0, 8, 'Card', 'Paid'),
(0, 9, 'Online', 'Paid'),
(0, 10, 'Online', 'Paid'),
(0, 11, 'Card', 'Paid'),
(0, 12, 'Card', 'Paid'),
(0, 13, 'Online', 'Refunded'),
(0, 14, 'Card', 'Refunded'),
(0, 15, 'Card', 'Paid'),
(0, 17, 'Card', 'Paid'),
(0, 19, 'Card', 'Paid'),
(0, 20, 'Card', 'Refunded'),
(0, 21, 'Card', 'Refunded'),
(0, 22, 'Card', 'Refunded'),
(0, 23, 'Online', 'Refunded');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
