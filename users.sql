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
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` varchar(20) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'active',
  `login_attempts` int(11) DEFAULT 0,
  `attempt_time` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `password`, `role`, `photo`, `status`, `login_attempts`, `attempt_time`) VALUES
(1, 'JianHao', 'jianhao67@gmail.com', '$2y$10$sFeUeVG9KFNsJKKFfMu5SeqZgGS5aw1qCob8MaL1Mt.6NRG4z6QOu', 'Admin', '69dbf5e4d4f29.jpg', 'active', 0, NULL),
(2, 'ZiXiang', 'zixiang12@gmail.com', '$2y$10$nlAi0XzSZoUoWlMgurKYXO6KksitbyEFNH.5SdEMqyukztcs/t8tq', 'Member', '69dbe82680e80.jpg', 'active', 0, NULL),
(4, 'JunXian', 'junxian111@gmail.com', '$2y$10$xdy4PGRHcSJVZ.Wc8.2YY.crgqW7m2O1lg03GPQ.3uepoUT88RZ5K', 'Admin', '69dc05113ef4a.jpg', 'active', 0, '2026-04-15 21:41:19'),
(5, 'JianDe', 'jiandeIsSb@gmail.com', '$2y$10$928VuxCT1xFFk4i8uYAAauJvmZyC6HroQi1GCmA4BJkDPxu4.cl7m', 'Member', '69e0a4a0da447.jpg', 'active', 0, NULL),
(19, 'Zi Xuan', 'zixuan19@gmail.com', '$2y$10$kMmj6q7FNi9H/ek/je/2PeJBNHt2rwFo/JHPPfktWxw9A3pDNDevm', 'Admin', '69e053960a59e.jpg', 'active', 0, NULL),
(20, 'Joshua Goh', 'joshua21@gmail.com', '$2y$10$Lln2k0T0vCF4lR2IuJz8yeG7Av6wR.0tuASpyJAQjYw2eJ7D7wAXS', 'Admin', '69db8149708f1.jpg', 'active', 0, NULL),
(22, 'Kai Qin', 'kaiqin45@gmail.com', '$2y$10$lie.g42b9cQ3QbsFB1/SN.sGMEoVV05tKHOyV2ZnozPdi9LhD9H/y', 'Member', 'default_image.jpg', 'active', 0, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
