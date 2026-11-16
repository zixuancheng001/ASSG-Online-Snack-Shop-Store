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
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `image_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_images`
--

INSERT INTO `product_images` (`image_id`, `product_id`, `filename`, `sort_order`) VALUES
(2, 2, '69dc009fbd87e.jpg', 0),
(3, 3, '69dc01ad2d47a.jpg', 0),
(4, 4, '69dc002a0365b.jpg', 0),
(5, 5, '69dc00deecaee.jpg', 0),
(6, 6, 'default_image.jpg', 0),
(21, 8, '69e05234698f1.jpg', 1),
(22, 9, '69e05249a75f1.jpg', 1),
(23, 10, '69e0535f54bc4.jpg', 1),
(24, 11, '69e05579d2fe3.jpg', 1),
(25, 11, '69e05579d3d9f.jpg', 2),
(26, 11, '69e055930989e.jpg', 3),
(27, 11, '69e055cc5e148.jpg', 4),
(32, 7, '69e055fe805b8.jpg', 3),
(33, 12, '69e056f5b4774.jpg', 1),
(34, 12, '69e056fda9fae.jpg', 2),
(35, 13, '69e05773b5135.jpg', 1),
(36, 13, '69e05773b60cc.jpg', 2),
(37, 13, '69e05781640fa.jpg', 3),
(39, 14, '69e057d48245b.jpg', 2),
(40, 14, '69e057e60757d.jpg', 3),
(41, 14, '69e057eef0c62.jpg', 4),
(42, 15, '69e05860b66aa.jpg', 1),
(43, 15, '69e05860b7519.jpg', 2),
(44, 16, '69e0588f956ee.jpg', 1),
(45, 16, '69e058c643563.jpg', 2),
(46, 17, '69e05919dfc2a.jpg', 1),
(47, 17, '69e05919e0ed8.jpg', 2),
(48, 17, '69e0592761fd0.jpg', 3),
(49, 18, '69e059643addf.jpg', 1),
(50, 18, '69e059643bc11.jpg', 2),
(51, 19, '69e059cc35ba6.jpg', 1),
(52, 20, '69e05a0ed0fca.jpg', 1),
(53, 20, '69e05a5153da4.jpg', 2),
(54, 21, '69e060f409630.jpg', 0),
(55, 21, '69e061036d194.jpg', 1),
(56, 22, '69e061fd812f8.jpg', 0),
(57, 22, '69e0620a04d31.jpg', 1),
(58, 23, '69e0638158a4d.jpg', 0),
(59, 23, '69e06390c5aa8.jpg', 1),
(60, 24, '69e0644001ac6.jpg', 0),
(61, 24, '69e0644d1f668.jpg', 1),
(62, 24, '69e064589d0be.jpg', 2),
(63, 25, '69e064f4ab01d.jpg', 0),
(64, 25, '69e06504bce39.jpg', 1),
(65, 3, '69e065289a96c.jpg', 1),
(66, 4, '69e0658c7cbc5.jpg', 1),
(67, 4, '69e065b8b75af.jpg', 2),
(68, 4, '69e065b8b82be.jpg', 3),
(69, 7, '69e065ffe0ccd.jpg', 4),
(70, 1, '69e0694fd7cba.jpg', 1),
(71, 1, '69e0696ea671c.jpg', 2),
(72, 29, '69e09edfac563.jpg', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`image_id`),
  ADD KEY `product_id` (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `image_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
