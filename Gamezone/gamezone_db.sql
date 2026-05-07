-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 07, 2026 at 07:40 AM
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
-- Database: `gamezone_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `admin_id` int(11) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `available_games` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `password`, `created_at`, `available_games`) VALUES
(1, '$2y$10$Hc8i7EGbHNK3MKHOOEHYrO0jWgt9R5hI1r.b2AluhLhg5lSnsXAyi', '2026-05-07 05:40:00', 'Valorent, PUBG');

-- --------------------------------------------------------

--
-- Table structure for table `banrecord`
--

CREATE TABLE `banrecord` (
  `ban_id` int(11) NOT NULL,
  `ban_type` enum('suspended','banned') NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `booking`
--

CREATE TABLE `booking` (
  `booking_id` int(11) NOT NULL,
  `status` enum('pending','confirmed','cancelled','completed') DEFAULT 'pending',
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `booking_date` date NOT NULL,
  `booking_type` enum('regular','tournament') DEFAULT 'regular',
  `room_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `game_name` varchar(100) DEFAULT NULL,
  `num_people` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `buy`
--

CREATE TABLE `buy` (
  `user_id` int(11) NOT NULL,
  `payment_id` int(11) NOT NULL,
  `plan_id` int(11) NOT NULL,
  `start_date` date DEFAULT curdate(),
  `end_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hosts`
--

CREATE TABLE `hosts` (
  `tournament_id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `implement`
--

CREATE TABLE `implement` (
  `user_id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `ban_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `makes`
--

CREATE TABLE `makes` (
  `user_id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `payment_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `membershipplan`
--

CREATE TABLE `membershipplan` (
  `plan_id` int(11) NOT NULL,
  `plan_name` varchar(50) NOT NULL,
  `duration` int(11) NOT NULL,
  `fee` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `role` enum('visitor','player','host') DEFAULT 'visitor',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `participate`
--

CREATE TABLE `participate` (
  `tournament_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `registration_date` date DEFAULT curdate(),
  `status` enum('registered','checked_in','disqualified') DEFAULT 'registered'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `payment_id` int(11) NOT NULL,
  `pay_status` enum('pending','completed','failed','refunded') DEFAULT 'pending',
  `amount` decimal(10,2) NOT NULL,
  `pay_date` date NOT NULL,
  `pay_type` enum('membership','booking','tournament_entry','refund') DEFAULT 'membership',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `prize_award`
--

CREATE TABLE `prize_award` (
  `prize_award_id` int(11) NOT NULL,
  `position` varchar(20) NOT NULL,
  `prize_amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `room`
--

CREATE TABLE `room` (
  `room_id` int(11) NOT NULL,
  `room_type` enum('regular','tournament') DEFAULT 'regular',
  `maintenance_status` enum('good','maintenance_needed','under_maintenance') DEFAULT 'good',
  `capacity` int(11) NOT NULL,
  `availability_status` enum('available','occupied','reserved') DEFAULT 'available',
  `room_name` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `available_games` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tournament`
--

CREATE TABLE `tournament` (
  `tournament_id` int(11) NOT NULL,
  `visitor_count` int(11) DEFAULT 0,
  `guest_name` varchar(100) DEFAULT NULL,
  `t_name` varchar(100) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `max_participants` int(11) DEFAULT 16,
  `entry_fee` decimal(10,2) DEFAULT 0.00,
  `status` enum('pending','registration_open','in_progress','completed','cancelled') DEFAULT 'pending',
  `room_id` int(11) DEFAULT NULL,
  `visitor_limit` int(11) DEFAULT 50,
  `player_limit` int(11) DEFAULT 16,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `prize_money` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `user_id` int(11) NOT NULL,
  `f_name` varchar(50) NOT NULL,
  `l_name` varchar(50) NOT NULL,
  `role` enum('visitor','player','host','admin') DEFAULT 'visitor',
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `status` enum('active','suspended','banned') DEFAULT 'active',
  `city` varchar(50) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `country` varchar(50) DEFAULT NULL,
  `u_type` enum('regular','premium','vip') DEFAULT 'regular',
  `zone` varchar(50) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `visit_count` int(11) DEFAULT 0,
  `shift` varchar(50) DEFAULT NULL,
  `total_score` int(11) DEFAULT 0,
  `skill_level` enum('beginner','intermediate','advanced','expert') DEFAULT 'beginner',
  `gamertag` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `f_name`, `l_name`, `role`, `email`, `password`, `status`, `city`, `postal_code`, `country`, `u_type`, `zone`, `category`, `visit_count`, `shift`, `total_score`, `skill_level`, `gamertag`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'System', 'admin', 'admin@gmail.com', '$2y$10$Hc8i7EGbHNK3MKHOOEHYrO0jWgt9R5hI1r.b2AluhLhg5lSnsXAyi', 'active', NULL, NULL, NULL, 'regular', NULL, NULL, 1, NULL, 0, 'beginner', NULL, '2026-05-07 05:40:00', '2026-05-07 05:40:06');

-- --------------------------------------------------------

--
-- Table structure for table `visitor_tournament`
--

CREATE TABLE `visitor_tournament` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `tournament_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `win`
--

CREATE TABLE `win` (
  `user_id` int(11) NOT NULL,
  `tournament_id` int(11) NOT NULL,
  `prize_award_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`);

--
-- Indexes for table `banrecord`
--
ALTER TABLE `banrecord`
  ADD PRIMARY KEY (`ban_id`);

--
-- Indexes for table `booking`
--
ALTER TABLE `booking`
  ADD PRIMARY KEY (`booking_id`),
  ADD KEY `idx_booking_date` (`booking_date`),
  ADD KEY `idx_booking_room` (`room_id`);

--
-- Indexes for table `buy`
--
ALTER TABLE `buy`
  ADD PRIMARY KEY (`user_id`,`payment_id`,`plan_id`),
  ADD KEY `payment_id` (`payment_id`),
  ADD KEY `plan_id` (`plan_id`);

--
-- Indexes for table `hosts`
--
ALTER TABLE `hosts`
  ADD PRIMARY KEY (`tournament_id`,`room_id`),
  ADD KEY `room_id` (`room_id`);

--
-- Indexes for table `implement`
--
ALTER TABLE `implement`
  ADD PRIMARY KEY (`user_id`,`admin_id`,`ban_id`),
  ADD KEY `admin_id` (`admin_id`),
  ADD KEY `ban_id` (`ban_id`);

--
-- Indexes for table `makes`
--
ALTER TABLE `makes`
  ADD PRIMARY KEY (`user_id`,`booking_id`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `payment_id` (`payment_id`);

--
-- Indexes for table `membershipplan`
--
ALTER TABLE `membershipplan`
  ADD PRIMARY KEY (`plan_id`);

--
-- Indexes for table `participate`
--
ALTER TABLE `participate`
  ADD PRIMARY KEY (`tournament_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `idx_payment_date` (`pay_date`);

--
-- Indexes for table `prize_award`
--
ALTER TABLE `prize_award`
  ADD PRIMARY KEY (`prize_award_id`);

--
-- Indexes for table `room`
--
ALTER TABLE `room`
  ADD PRIMARY KEY (`room_id`);

--
-- Indexes for table `tournament`
--
ALTER TABLE `tournament`
  ADD PRIMARY KEY (`tournament_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_tournament_date` (`start_date`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_user_email` (`email`),
  ADD KEY `idx_user_role` (`role`),
  ADD KEY `idx_user_status` (`status`);

--
-- Indexes for table `visitor_tournament`
--
ALTER TABLE `visitor_tournament`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_tournament` (`user_id`,`tournament_id`);

--
-- Indexes for table `win`
--
ALTER TABLE `win`
  ADD PRIMARY KEY (`user_id`,`tournament_id`,`prize_award_id`),
  ADD KEY `tournament_id` (`tournament_id`),
  ADD KEY `prize_award_id` (`prize_award_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `banrecord`
--
ALTER TABLE `banrecord`
  MODIFY `ban_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `booking`
--
ALTER TABLE `booking`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `membershipplan`
--
ALTER TABLE `membershipplan`
  MODIFY `plan_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `prize_award`
--
ALTER TABLE `prize_award`
  MODIFY `prize_award_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `room`
--
ALTER TABLE `room`
  MODIFY `room_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tournament`
--
ALTER TABLE `tournament`
  MODIFY `tournament_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `visitor_tournament`
--
ALTER TABLE `visitor_tournament`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `booking`
--
ALTER TABLE `booking`
  ADD CONSTRAINT `booking_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `room` (`room_id`) ON DELETE SET NULL;

--
-- Constraints for table `buy`
--
ALTER TABLE `buy`
  ADD CONSTRAINT `buy_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `buy_ibfk_2` FOREIGN KEY (`payment_id`) REFERENCES `payment` (`payment_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `buy_ibfk_3` FOREIGN KEY (`plan_id`) REFERENCES `membershipplan` (`plan_id`) ON DELETE CASCADE;

--
-- Constraints for table `hosts`
--
ALTER TABLE `hosts`
  ADD CONSTRAINT `hosts_ibfk_1` FOREIGN KEY (`tournament_id`) REFERENCES `tournament` (`tournament_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `hosts_ibfk_2` FOREIGN KEY (`room_id`) REFERENCES `room` (`room_id`) ON DELETE CASCADE;

--
-- Constraints for table `implement`
--
ALTER TABLE `implement`
  ADD CONSTRAINT `implement_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `implement_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `admin` (`admin_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `implement_ibfk_3` FOREIGN KEY (`ban_id`) REFERENCES `banrecord` (`ban_id`) ON DELETE CASCADE;

--
-- Constraints for table `makes`
--
ALTER TABLE `makes`
  ADD CONSTRAINT `makes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `makes_ibfk_2` FOREIGN KEY (`booking_id`) REFERENCES `booking` (`booking_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `makes_ibfk_3` FOREIGN KEY (`payment_id`) REFERENCES `payment` (`payment_id`) ON DELETE CASCADE;

--
-- Constraints for table `participate`
--
ALTER TABLE `participate`
  ADD CONSTRAINT `participate_ibfk_1` FOREIGN KEY (`tournament_id`) REFERENCES `tournament` (`tournament_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `participate_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `tournament`
--
ALTER TABLE `tournament`
  ADD CONSTRAINT `tournament_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `win`
--
ALTER TABLE `win`
  ADD CONSTRAINT `win_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `win_ibfk_2` FOREIGN KEY (`tournament_id`) REFERENCES `tournament` (`tournament_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `win_ibfk_3` FOREIGN KEY (`prize_award_id`) REFERENCES `prize_award` (`prize_award_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
