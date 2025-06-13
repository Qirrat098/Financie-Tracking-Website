-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 13, 2025 at 08:32 PM
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
-- Database: `adviso_db`
--
CREATE DATABASE IF NOT EXISTS `adviso_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `adviso_db`;

-- --------------------------------------------------------

--
-- Table structure for table `achievements`
--

DROP TABLE IF EXISTS `achievements`;
CREATE TABLE `achievements` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `criteria` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `achievements`
--

INSERT INTO `achievements` (`id`, `title`, `description`, `icon`, `criteria`, `created_at`) VALUES
(1, 'First Goal', 'Created your first financial goal', 'fa-bullseye', 'Create first financial goal', '2025-05-31 21:32:39'),
(2, 'Goal Master', 'Completed 5 financial goals', 'fa-trophy', 'Complete 5 goals', '2025-05-31 21:32:39'),
(3, 'Savings Champion', 'Saved over ₹50,000', 'fa-medal', 'Total savings exceed ₹50,000', '2025-05-31 21:32:39');

-- --------------------------------------------------------

--
-- Table structure for table `articles`
--

DROP TABLE IF EXISTS `articles`;
CREATE TABLE `articles` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `author_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `status` enum('draft','pending','published','rejected','revision_needed') NOT NULL DEFAULT 'draft',
  `moderator_feedback` text DEFAULT NULL,
  `moderated_by` int(11) DEFAULT NULL,
  `moderated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `articles`
--

INSERT INTO `articles` (`id`, `title`, `slug`, `content`, `author_id`, `category_id`, `status`, `moderator_feedback`, `moderated_by`, `moderated_at`, `created_at`, `updated_at`) VALUES
(1, 'Umer', 'umer', '<p>Hello Umer</p>', 1, 4, 'published', '<br />\r\n<b>Deprecated</b>:  htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated in <b>D:\\XAMPP\\htdocs\\Adviso\\moderate-article.php</b> on line <b>164</b><br />\r\n', 1, '2025-05-31 21:50:39', '2025-05-31 20:26:45', '2025-05-31 21:50:39'),
(13, 'kjashdkjashlkasfsadfsdgljbnasgkbasdkfkasdlfnaskjdfsdfb', 'kjashdkjashlk', '<p>kjbkvjlfhksdafhlnasdngkasdglasdjglkasj;sadfasdfasdfasdfajknfdkjsa&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;Umer</p>', 3, 8, 'pending', '<br />\r\n<b>Deprecated</b>:  htmlspecialchars(): Passing null to parameter #1 ($string) of type string is deprecated in <b>D:\\XAMPP\\htdocs\\Adviso\\moderate-article.php</b> on line <b>164</b><br />\r\n', 1, '2025-06-04 11:41:33', '2025-06-04 11:09:52', '2025-06-13 18:20:55');

-- --------------------------------------------------------

--
-- Table structure for table `article_likes`
--

DROP TABLE IF EXISTS `article_likes`;
CREATE TABLE `article_likes` (
  `id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `liked_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `article_views`
--

DROP TABLE IF EXISTS `article_views`;
CREATE TABLE `article_views` (
  `id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `viewed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Personal Finance', 'personal-finance', 'Articles about managing personal finances, budgeting, and saving money', '2025-05-31 20:11:16', '2025-05-31 20:11:16'),
(2, 'Investment', 'investment', 'Articles about investment strategies, stocks, bonds, and other investment vehicles', '2025-05-31 20:11:16', '2025-05-31 20:11:16'),
(3, 'Tax Planning', 'tax-planning', 'Articles about tax strategies, deductions, and tax-efficient investing', '2025-05-31 20:11:16', '2025-05-31 20:11:16'),
(4, 'Retirement', 'retirement', 'Articles about retirement planning, pensions, and retirement accounts', '2025-05-31 20:11:16', '2025-05-31 20:11:16'),
(5, 'Insurance', 'insurance', 'Articles about different types of insurance and risk management', '2025-05-31 20:11:16', '2025-05-31 20:11:16'),
(6, 'Estate Planning', 'estate-planning', 'Articles about wills, trusts, and estate management', '2025-05-31 20:11:16', '2025-05-31 20:11:16'),
(7, 'Financial Education', 'financial-education', 'Articles about financial literacy and education', '2025-05-31 20:11:16', '2025-05-31 20:11:16'),
(8, 'Market Analysis', 'market-analysis', 'Articles about market trends and economic analysis', '2025-05-31 20:11:16', '2025-05-31 20:11:16');

-- --------------------------------------------------------

--
-- Table structure for table `financial_goals`
--

DROP TABLE IF EXISTS `financial_goals`;
CREATE TABLE `financial_goals` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `target_amount` decimal(15,2) NOT NULL,
  `saved_amount` decimal(15,2) DEFAULT 0.00,
  `target_date` date DEFAULT NULL,
  `status` enum('active','completed','cancelled') DEFAULT 'active',
  `category` varchar(50) DEFAULT NULL,
  `priority` enum('low','medium','high') DEFAULT 'medium',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `financial_goals`
--

INSERT INTO `financial_goals` (`id`, `user_id`, `title`, `description`, `target_amount`, `saved_amount`, `target_date`, `status`, `category`, `priority`, `created_at`, `updated_at`) VALUES
(1, 1, 'Emergency Fund', 'Building emergency fund for unexpected expenses', 100000.00, 50000.00, '2024-12-31', 'active', 'Savings', 'high', '2025-05-31 21:32:39', '2025-05-31 21:32:39'),
(2, 1, 'New Car', 'Saving for a new car purchase', 250000.00, 150000.00, '2024-06-30', 'active', 'Vehicle', 'medium', '2025-05-31 21:32:39', '2025-05-31 21:32:39'),
(4, 3, 'Hockey', 'Get a Hockey!', 25000.00, 25000.00, '2025-06-30', 'completed', 'savings', 'medium', '2025-06-04 09:50:29', '2025-06-13 18:17:27'),
(5, 3, 'Car!', '', 2500000.00, 2500000.00, '2025-06-28', 'completed', 'purchase', 'medium', '2025-06-04 10:29:02', '2025-06-13 18:19:12'),
(7, 3, 'helicopter', '', 2500000.00, 2500000.00, '2025-06-17', 'completed', 'purchase', 'medium', '2025-06-13 18:20:13', '2025-06-13 18:26:05');

-- --------------------------------------------------------

--
-- Table structure for table `tax_records`
--

DROP TABLE IF EXISTS `tax_records`;
CREATE TABLE `tax_records` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `tax_year` varchar(9) NOT NULL,
  `total_income` decimal(15,2) NOT NULL,
  `total_deductions` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_tax` decimal(15,2) NOT NULL,
  `status` enum('pending','processing','completed') NOT NULL DEFAULT 'pending',
  `filing_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tax_records`
--

INSERT INTO `tax_records` (`id`, `user_id`, `tax_year`, `total_income`, `total_deductions`, `total_tax`, `status`, `filing_date`, `created_at`, `updated_at`) VALUES
(1, 1, '2023-24', 1200000.00, 150000.00, 150000.00, 'completed', '2024-03-15', '2025-05-31 20:24:09', '2025-05-31 20:24:09'),
(2, 1, '2022-23', 1000000.00, 120000.00, 120000.00, 'completed', '2023-03-15', '2025-05-31 20:24:09', '2025-05-31 20:24:09'),
(3, 1, '2024-25', 1500000.00, 200000.00, 200000.00, 'pending', NULL, '2025-05-31 20:24:09', '2025-05-31 20:24:09');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

DROP TABLE IF EXISTS `transactions`;
CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('income','expense') NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `transaction_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `user_id`, `type`, `amount`, `description`, `category`, `transaction_date`, `created_at`) VALUES
(1, 1, 'income', 50000.00, 'Monthly Salary', 'Salary', '2025-05-31', '2025-05-31 21:32:39'),
(2, 1, 'expense', 15000.00, 'Monthly Rent', 'Housing', '2025-05-31', '2025-05-31 21:32:39'),
(3, 3, 'income', 150000.00, '', 'Salary', '2025-06-04', '2025-06-04 10:12:03'),
(4, 3, 'expense', 50240.00, '', 'Shopping', '2025-06-04', '2025-06-04 10:12:41'),
(5, 3, 'income', 60000000.00, 'Salary', 'Salary', '2025-06-13', '2025-06-13 18:26:55');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`, `updated_at`) VALUES
(1, 'Admin User', 'admin@adviso.com', '$2y$10$cjbIKFDp9hyw2PFXloD7huNWg8FAOxIMCktgaaV15LHYL7Z/4QUfW', 'admin', '2025-05-31 21:32:37', '2025-05-31 21:41:35'),
(2, 'Test User', 'test@adviso.com', '$2y$10$56GHk3OFBhvrn9IgCksaOejuBd/XgZDRJB5Vz4h/oS5BnHWUKJofy', 'user', '2025-05-31 21:32:37', '2025-05-31 21:32:37'),
(3, 'Junaid Anser', 'junaidanser65@gmail.com', '$2y$10$cjbIKFDp9hyw2PFXloD7huNWg8FAOxIMCktgaaV15LHYL7Z/4QUfW', 'user', '2025-05-31 21:41:14', '2025-05-31 21:51:25');

-- --------------------------------------------------------

--
-- Table structure for table `user_achievements`
--

DROP TABLE IF EXISTS `user_achievements`;
CREATE TABLE `user_achievements` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `achievement_id` int(11) NOT NULL,
  `earned_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_preferences`
--

DROP TABLE IF EXISTS `user_preferences`;
CREATE TABLE `user_preferences` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `theme` varchar(10) DEFAULT 'light',
  `email_notifications` tinyint(1) DEFAULT 1,
  `push_notifications` tinyint(1) DEFAULT 1,
  `share_progress` tinyint(1) DEFAULT 0,
  `share_achievements` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_preferences`
--

INSERT INTO `user_preferences` (`id`, `user_id`, `theme`, `email_notifications`, `push_notifications`, `share_progress`, `share_achievements`, `created_at`, `updated_at`) VALUES
(1, 1, 'on', 1, 1, 0, 0, '2025-05-31 21:14:55', '2025-05-31 21:15:02'),
(2, 2, 'on', 1, 1, 0, 0, '2025-05-31 21:18:32', '2025-05-31 21:18:32');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `achievements`
--
ALTER TABLE `achievements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `articles`
--
ALTER TABLE `articles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `author_id` (`author_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `moderated_by` (`moderated_by`);

--
-- Indexes for table `article_likes`
--
ALTER TABLE `article_likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_article_like` (`article_id`,`user_id`),
  ADD KEY `idx_article_likes_article` (`article_id`),
  ADD KEY `idx_article_likes_user` (`user_id`);

--
-- Indexes for table `article_views`
--
ALTER TABLE `article_views`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_article_views_article` (`article_id`),
  ADD KEY `idx_article_views_user` (`user_id`),
  ADD KEY `idx_article_views_ip` (`ip_address`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `financial_goals`
--
ALTER TABLE `financial_goals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_goals_user` (`user_id`),
  ADD KEY `idx_goals_status` (`status`);

--
-- Indexes for table `tax_records`
--
ALTER TABLE `tax_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_transactions_user` (`user_id`),
  ADD KEY `idx_transactions_date` (`transaction_date`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_users_email` (`email`),
  ADD KEY `idx_users_role` (`role`);

--
-- Indexes for table `user_achievements`
--
ALTER TABLE `user_achievements`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_achievement` (`user_id`,`achievement_id`),
  ADD KEY `achievement_id` (`achievement_id`),
  ADD KEY `idx_user_achievements_user` (`user_id`);

--
-- Indexes for table `user_preferences`
--
ALTER TABLE `user_preferences`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `achievements`
--
ALTER TABLE `achievements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `articles`
--
ALTER TABLE `articles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `article_likes`
--
ALTER TABLE `article_likes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `article_views`
--
ALTER TABLE `article_views`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `financial_goals`
--
ALTER TABLE `financial_goals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `tax_records`
--
ALTER TABLE `tax_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `user_achievements`
--
ALTER TABLE `user_achievements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_preferences`
--
ALTER TABLE `user_preferences`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `articles`
--
ALTER TABLE `articles`
  ADD CONSTRAINT `articles_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `articles_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `articles_ibfk_3` FOREIGN KEY (`moderated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `article_likes`
--
ALTER TABLE `article_likes`
  ADD CONSTRAINT `article_likes_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `article_likes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `article_views`
--
ALTER TABLE `article_views`
  ADD CONSTRAINT `article_views_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `article_views_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `financial_goals`
--
ALTER TABLE `financial_goals`
  ADD CONSTRAINT `financial_goals_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tax_records`
--
ALTER TABLE `tax_records`
  ADD CONSTRAINT `tax_records_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_achievements`
--
ALTER TABLE `user_achievements`
  ADD CONSTRAINT `user_achievements_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_achievements_ibfk_2` FOREIGN KEY (`achievement_id`) REFERENCES `achievements` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_preferences`
--
ALTER TABLE `user_preferences`
  ADD CONSTRAINT `user_preferences_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
