-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Dec 10, 2025 at 01:59 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ecoswap`
--

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(255) DEFAULT NULL,
  `condition` varchar(50) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `status` enum('available','pending','swapped') DEFAULT 'available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`id`, `name`, `description`, `category`, `condition`, `image_url`, `user_id`, `status`, `created_at`) VALUES
(1, 'pinkcup', 'it\'s a pinterest vibe cup so you may love to use it ', 'cup', 'new', 'uploads/68e00409e8b41-_ (3).jpeg', 1, 'swapped', '2025-10-03 17:12:41'),
(2, 'Harry potter books', 'it\'s a collection of all the harry potter books ', 'Book', 'new', 'uploads/68fdebeec0fb7-harrypotterbook.jpg', 8, 'swapped', '2025-10-26 09:37:50'),
(3, 'Harry potter books', 'it\'s a collection of all the harry potter books ', 'Book', 'new', 'uploads/68fdebf2951a1-harrypotterbook.jpg', 8, 'available', '2025-10-26 09:37:54');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `swap_id` int(11) DEFAULT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `message_text` text NOT NULL,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `swap_id`, `sender_id`, `receiver_id`, `message_text`, `sent_at`) VALUES
(1, 7, 1, 8, 'hi', '2025-12-09 13:06:21');

-- --------------------------------------------------------

--
-- Table structure for table `swaps`
--

CREATE TABLE `swaps` (
  `id` int(11) NOT NULL,
  `item1_id` int(11) NOT NULL,
  `item2_id` int(11) NOT NULL,
  `item1_owner_id` int(11) NOT NULL,
  `item2_owner_id` int(11) NOT NULL,
  `status` enum('pending','accepted','completed','declined') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `swaps`
--

INSERT INTO `swaps` (`id`, `item1_id`, `item2_id`, `item1_owner_id`, `item2_owner_id`, `status`, `created_at`) VALUES
(1, 1, 2, 1, 8, 'accepted', '2025-10-26 09:45:08'),
(2, 2, 1, 8, 1, 'accepted', '2025-10-26 10:14:42'),
(3, 1, 2, 1, 8, 'accepted', '2025-10-26 10:24:13'),
(4, 2, 1, 8, 1, 'pending', '2025-10-26 10:25:36'),
(5, 3, 1, 8, 1, 'pending', '2025-10-26 10:44:25'),
(6, 3, 1, 8, 1, 'pending', '2025-12-09 13:05:13'),
(7, 3, 1, 8, 1, 'pending', '2025-12-09 13:06:01');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `created_at`) VALUES
(1, 'raven', 'dharvityagi@gmail.com', '$2y$10$oKOxVudEY.ml.rdGgsQ4gu0ZAsAYNCamEyNxJANPpdBY0UIJEg7Mq', '2025-10-03 16:27:28'),
(5, 'honey', '123@gmail.com', '$2y$10$H6lyIXGYifSJek9iKPV.GeNU3tkmgtynrfobCw/VXW72/d1DGAhC6', '2025-10-03 16:29:35'),
(8, 'dracula', 'honey123@gmail.com', '$2y$10$6umEFLkLvdwUOZNEiQVMlO1Oit0GLfbiks4/bFlMsm0hrQjPXVyk6', '2025-10-26 06:40:17'),
(9, 'Shalvi', 'shalvi123@gmail.com', '$2y$10$VEVpJyjpHpGj7rIlDxCkCeRWKB8tVPZ58nd6uHehhzMlBkLBv8Xmm', '2025-12-09 12:50:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `swap_id` (`swap_id`),
  ADD KEY `sender_id` (`sender_id`);

--
-- Indexes for table `swaps`
--
ALTER TABLE `swaps`
  ADD PRIMARY KEY (`id`),
  ADD KEY `item1_id` (`item1_id`),
  ADD KEY `item2_id` (`item2_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `swaps`
--
ALTER TABLE `swaps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `items`
--
ALTER TABLE `items`
  ADD CONSTRAINT `items_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`swap_id`) REFERENCES `swaps` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `swaps`
--
ALTER TABLE `swaps`
  ADD CONSTRAINT `swaps_ibfk_1` FOREIGN KEY (`item1_id`) REFERENCES `items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `swaps_ibfk_2` FOREIGN KEY (`item2_id`) REFERENCES `items` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
