-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 06, 2026 at 08:58 PM
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
  `available_games` text DEFAULT NULL COMMENT 'Comma-separated available games'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `password`, `created_at`, `available_games`) VALUES
(1, '$2y$12$fr0JY7DMI2YeFGCRmo5bKuHdRHETeRwWSBZUMe5nberVh0pmkt.gy', '2026-04-22 04:31:39', 'Valorent, PUBG');

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

--
-- Dumping data for table `buy`
--

INSERT INTO `buy` (`user_id`, `payment_id`, `plan_id`, `start_date`, `end_date`) VALUES
(1, 126, 9, '2026-05-07', '2026-05-06'),
(3, 128, 7, '2026-05-07', '2026-05-06');

-- --------------------------------------------------------

--
-- Table structure for table `gamesession`
--

CREATE TABLE `gamesession` (
  `session_id` int(11) NOT NULL,
  `game_name` varchar(100) NOT NULL,
  `player_id` int(11) DEFAULT NULL,
  `room_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `entry_time` datetime DEFAULT NULL,
  `exit_time` datetime DEFAULT NULL,
  `duration_minutes` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hosts`
--

CREATE TABLE `hosts` (
  `tournament_id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `session_id` int(11) DEFAULT NULL
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

--
-- Dumping data for table `membershipplan`
--

INSERT INTO `membershipplan` (`plan_id`, `plan_name`, `duration`, `fee`, `description`, `role`, `created_at`) VALUES
(6, 'p1', 1654651, 99999999.99, 'yfyujh', 'visitor', '2026-04-30 04:51:55'),
(7, 'Test Plan', 12, 12.00, 'td', 'player', '2026-04-30 06:31:22'),
(8, 'Test', 223, 233.00, '234423', 'host', '2026-04-30 06:43:45'),
(9, 'ewwi', 435, 335.00, '121', 'visitor', '2026-04-30 10:01:27'),
(10, 'New1', 1, 1.00, 'N/A', 'visitor', '2026-05-05 10:20:16');

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

--
-- Dumping data for table `participate`
--

INSERT INTO `participate` (`tournament_id`, `user_id`, `registration_date`, `status`) VALUES
(22, 10, '2026-05-07', 'registered'),
(22, 11, '2026-05-07', 'registered');

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

--
-- Dumping data for table `payment`
--

INSERT INTO `payment` (`payment_id`, `pay_status`, `amount`, `pay_date`, `pay_type`, `created_at`) VALUES
(123, 'completed', 200.00, '2026-05-06', 'tournament_entry', '2026-05-06 18:33:44'),
(124, 'completed', 200.00, '2026-05-07', 'tournament_entry', '2026-05-06 18:34:23'),
(125, 'completed', 200.00, '2026-05-07', 'tournament_entry', '2026-05-06 18:34:41'),
(126, 'completed', 335.00, '2026-05-06', 'membership', '2026-05-06 18:37:14'),
(127, 'completed', 335.00, '2026-05-07', 'refund', '2026-05-06 18:37:33'),
(128, 'completed', 12.00, '2026-05-07', 'membership', '2026-05-06 18:38:11'),
(129, 'completed', 4.80, '2026-05-07', 'refund', '2026-05-06 18:38:21');

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

--
-- Dumping data for table `tournament`
--

INSERT INTO `tournament` (`tournament_id`, `visitor_count`, `guest_name`, `t_name`, `start_date`, `end_date`, `user_id`, `max_participants`, `entry_fee`, `status`, `room_id`, `visitor_limit`, `player_limit`, `created_at`, `prize_money`) VALUES
(22, 0, NULL, 'Lan', '2026-05-07', '2026-05-08', 4, 2, 200.00, 'registration_open', 7, 2, 2, '2026-05-06 18:16:20', 0.00);

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
(1, 'ANIRUDDHA', 'SAHA', 'visitor', 'aniruddha.saha@g.bracu.ac.bd', '$2y$10$MKsOowWcE/oh56J77KRxqe0DriXlbTXSvl4vw1Uzs449pKvTGByse', 'active', NULL, NULL, NULL, 'regular', NULL, NULL, 76, NULL, 0, 'beginner', NULL, '2026-04-22 04:29:44', '2026-05-06 18:37:30'),
(2, 'Admin', 'System', 'admin', 'admin@gmail.com', '$2y$12$fr0JY7DMI2YeFGCRmo5bKuHdRHETeRwWSBZUMe5nberVh0pmkt.gy', 'active', NULL, NULL, NULL, 'regular', NULL, NULL, 94, NULL, 0, 'beginner', NULL, '2026-04-22 04:31:39', '2026-05-06 18:50:13'),
(3, 'ANIRUDDHA', 'SAHA', 'player', 'sahaaniruddha2004@gmail.com', '$2y$10$qxRZALxF.znHU5t6uyUozOA6sApJd0Z/xfUl3gHkx8bptKOSeZB5i', 'active', NULL, NULL, NULL, 'regular', NULL, NULL, 38, NULL, 0, 'beginner', NULL, '2026-04-29 04:54:48', '2026-05-06 18:38:02'),
(4, 'ANIRUDDHA', 'SAHA', 'host', 'rahik@gmail.com', '$2y$10$7Pp0D6GdI6NNwKsWn98HZuSX9jcJ0iJrIFgDTi6RZO.dj0phFnLoW', 'active', NULL, NULL, NULL, 'regular', NULL, NULL, 85, NULL, 0, 'beginner', NULL, '2026-04-29 05:11:02', '2026-05-06 18:33:03'),
(5, 'visitor2', 'a', 'visitor', 'vi@gmail.com', '$2y$10$JvQDAi9QVVjwWc980XVLyeXTDs6AiwOO8vVm6SqBcxvGDXcXBXm5O', 'active', NULL, NULL, NULL, 'regular', NULL, NULL, 7, NULL, 0, 'beginner', NULL, '2026-04-29 16:31:51', '2026-04-30 03:00:55'),
(6, 'v', '1', 'visitor', 'visitor@gmail.com', '$2y$10$yrVgQXIZXvXmUznLWyGpUOXZUse0L3L3TV3penciCycAj1QqdVKVa', 'active', NULL, NULL, NULL, 'regular', NULL, NULL, 9, NULL, 0, 'beginner', NULL, '2026-04-30 04:51:00', '2026-05-03 15:42:00'),
(7, 'h', 't', 'host', 'host@gmail.com', '$2y$10$h2r.FeZlu/RUO7h2qAuMjuAcfFNkW70792RH7ELC0Gnegjzv1bQCO', 'active', NULL, NULL, NULL, 'regular', NULL, NULL, 17, NULL, 0, 'beginner', NULL, '2026-04-30 06:32:15', '2026-05-03 15:39:50'),
(8, 'p', '1', 'player', 'player@gmail.com', '$2y$10$ITaSLOX83ImcAXnpjNXp2ObVFw36zv75i7OiiIF7QCmaI9f.a309G', 'active', NULL, NULL, NULL, 'regular', NULL, NULL, 8, NULL, 0, 'beginner', NULL, '2026-04-30 06:35:53', '2026-05-03 15:40:28'),
(10, 'ANIRUDDHA', 'SAHA', 'player', 'playerr2@gmail.com', '$2y$10$yMkyS2OM7Vu7nqJ/Yn8lvOhtygniBDyenAJmnfwbBDMDrdil7EmFC', 'active', NULL, NULL, NULL, 'regular', NULL, NULL, 1, NULL, 0, 'beginner', NULL, '2026-05-06 18:34:01', '2026-05-06 18:34:36'),
(11, 'ANIRUDDHA', 'SAHA', 'player', 'playerr3@gmail.com', '$2y$10$l5Z0V4ti6a59EDho5tbg.OC13DKNObKaBT2Z/Uaphg98x6oojSCgS', 'active', NULL, NULL, NULL, 'regular', NULL, NULL, 1, NULL, 0, 'beginner', NULL, '2026-05-06 18:34:10', '2026-05-06 18:34:16');

-- --------------------------------------------------------

--
-- Table structure for table `visitor_tournament`
--

CREATE TABLE `visitor_tournament` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `tournament_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `visitor_tournament`
--

INSERT INTO `visitor_tournament` (`id`, `user_id`, `tournament_id`) VALUES
(20, -1, 19),
(17, -1, 20),
(19, -1, 21);

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
-- Indexes for table `gamesession`
--
ALTER TABLE `gamesession`
  ADD PRIMARY KEY (`session_id`),
  ADD KEY `idx_gamesession_user` (`user_id`),
  ADD KEY `idx_gamesession_room` (`room_id`);

--
-- Indexes for table `hosts`
--
ALTER TABLE `hosts`
  ADD PRIMARY KEY (`tournament_id`,`room_id`),
  ADD KEY `room_id` (`room_id`),
  ADD KEY `session_id` (`session_id`);

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
  MODIFY `ban_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `booking`
--
ALTER TABLE `booking`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `gamesession`
--
ALTER TABLE `gamesession`
  MODIFY `session_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `membershipplan`
--
ALTER TABLE `membershipplan`
  MODIFY `plan_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=130;

--
-- AUTO_INCREMENT for table `prize_award`
--
ALTER TABLE `prize_award`
  MODIFY `prize_award_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `room`
--
ALTER TABLE `room`
  MODIFY `room_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `tournament`
--
ALTER TABLE `tournament`
  MODIFY `tournament_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `visitor_tournament`
--
ALTER TABLE `visitor_tournament`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

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
-- Constraints for table `gamesession`
--
ALTER TABLE `gamesession`
  ADD CONSTRAINT `gamesession_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `room` (`room_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `gamesession_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `hosts`
--
ALTER TABLE `hosts`
  ADD CONSTRAINT `hosts_ibfk_1` FOREIGN KEY (`tournament_id`) REFERENCES `tournament` (`tournament_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `hosts_ibfk_2` FOREIGN KEY (`room_id`) REFERENCES `room` (`room_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `hosts_ibfk_3` FOREIGN KEY (`session_id`) REFERENCES `gamesession` (`session_id`) ON DELETE CASCADE;

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
