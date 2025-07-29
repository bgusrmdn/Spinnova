-- SlotMania Database Schema
-- Created: <?= date('Y-m-d H:i:s') ?>

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- Database: `slotmania_db`
CREATE DATABASE IF NOT EXISTS `slotmania_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `slotmania_db`;

-- Table structure for `admins`
CREATE TABLE `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL UNIQUE,
  `email` varchar(100) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('super_admin','admin','moderator') DEFAULT 'admin',
  `avatar` varchar(255) DEFAULT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `login_attempts` int(3) DEFAULT 0,
  `locked_until` timestamp NULL DEFAULT NULL,
  `two_factor_enabled` tinyint(1) DEFAULT 0,
  `two_factor_secret` varchar(32) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_username` (`username`),
  KEY `idx_email` (`email`),
  KEY `idx_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `users`
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL UNIQUE,
  `email` varchar(100) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `balance` decimal(10,2) DEFAULT 0.00,
  `avatar` varchar(255) DEFAULT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `login_attempts` int(3) DEFAULT 0,
  `locked_until` timestamp NULL DEFAULT NULL,
  `email_verified` tinyint(1) DEFAULT 0,
  `verification_token` varchar(64) DEFAULT NULL,
  `reset_token` varchar(64) DEFAULT NULL,
  `reset_expires` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_username` (`username`),
  KEY `idx_email` (`email`),
  KEY `idx_balance` (`balance`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `games`
CREATE TABLE `games` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL UNIQUE,
  `description` text,
  `thumbnail` varchar(255) DEFAULT NULL,
  `category` varchar(50) DEFAULT 'classic',
  `rtp` decimal(5,2) DEFAULT 95.00,
  `min_bet` decimal(8,2) DEFAULT 1.00,
  `max_bet` decimal(8,2) DEFAULT 1000.00,
  `reels` int(2) DEFAULT 3,
  `rows` int(2) DEFAULT 3,
  `paylines` int(3) DEFAULT 1,
  `symbols` json DEFAULT NULL,
  `paytable` json DEFAULT NULL,
  `bonus_features` json DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `is_featured` tinyint(1) DEFAULT 0,
  `play_count` int(11) DEFAULT 0,
  `total_wagered` decimal(15,2) DEFAULT 0.00,
  `total_won` decimal(15,2) DEFAULT 0.00,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_slug` (`slug`),
  KEY `idx_category` (`category`),
  KEY `idx_active` (`is_active`),
  KEY `idx_featured` (`is_featured`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `game_sessions`
CREATE TABLE `game_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `game_id` int(11) NOT NULL,
  `session_token` varchar(64) NOT NULL UNIQUE,
  `initial_balance` decimal(10,2) NOT NULL,
  `current_balance` decimal(10,2) NOT NULL,
  `total_bet` decimal(10,2) DEFAULT 0.00,
  `total_win` decimal(10,2) DEFAULT 0.00,
  `spins_count` int(11) DEFAULT 0,
  `status` enum('active','completed','abandoned') DEFAULT 'active',
  `started_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `ended_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_game_id` (`game_id`),
  KEY `idx_session_token` (`session_token`),
  KEY `idx_status` (`status`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`game_id`) REFERENCES `games` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `game_spins`
CREATE TABLE `game_spins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` int(11) NOT NULL,
  `bet_amount` decimal(8,2) NOT NULL,
  `win_amount` decimal(8,2) DEFAULT 0.00,
  `result` json NOT NULL,
  `is_bonus` tinyint(1) DEFAULT 0,
  `multiplier` decimal(5,2) DEFAULT 1.00,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_session_id` (`session_id`),
  KEY `idx_bet_amount` (`bet_amount`),
  KEY `idx_win_amount` (`win_amount`),
  FOREIGN KEY (`session_id`) REFERENCES `game_sessions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `transactions`
CREATE TABLE `transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `type` enum('deposit','withdrawal','game_win','game_loss','bonus','refund') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `balance_before` decimal(10,2) NOT NULL,
  `balance_after` decimal(10,2) NOT NULL,
  `reference_id` varchar(100) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `status` enum('pending','completed','failed','cancelled') DEFAULT 'completed',
  `processed_by` int(11) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_type` (`type`),
  KEY `idx_status` (`status`),
  KEY `idx_reference_id` (`reference_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`processed_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `settings`
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(100) NOT NULL UNIQUE,
  `value` text,
  `type` enum('string','integer','boolean','json') DEFAULT 'string',
  `description` varchar(255) DEFAULT NULL,
  `is_public` tinyint(1) DEFAULT 0,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_key` (`key`),
  KEY `idx_public` (`is_public`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table structure for `admin_logs`
CREATE TABLE `admin_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `target_type` varchar(50) DEFAULT NULL,
  `target_id` int(11) DEFAULT NULL,
  `old_values` json DEFAULT NULL,
  `new_values` json DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_admin_id` (`admin_id`),
  KEY `idx_action` (`action`),
  KEY `idx_target` (`target_type`, `target_id`),
  FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin user
INSERT INTO `admins` (`username`, `email`, `password`, `full_name`, `role`) VALUES
('admin', 'admin@slotmania.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', 'super_admin');

-- Insert default settings
INSERT INTO `settings` (`key`, `value`, `type`, `description`, `is_public`) VALUES
('site_name', 'SlotMania', 'string', 'Site name', 1),
('site_description', 'The best online slot gaming experience', 'string', 'Site description', 1),
('maintenance_mode', 'false', 'boolean', 'Maintenance mode status', 0),
('registration_enabled', 'true', 'boolean', 'User registration enabled', 0),
('min_deposit', '10', 'integer', 'Minimum deposit amount', 1),
('max_withdrawal', '10000', 'integer', 'Maximum withdrawal amount', 1),
('default_currency', 'IDR', 'string', 'Default currency', 1),
('house_edge', '5.0', 'string', 'House edge percentage', 0);

-- Insert sample games
INSERT INTO `games` (`name`, `slug`, `description`, `category`, `rtp`, `min_bet`, `max_bet`, `symbols`, `paytable`) VALUES
('Cherry Blast', 'cherry-blast', 'Classic fruit slot with modern twist', 'classic', 96.50, 1.00, 100.00, 
'["🍒", "🍋", "🔔", "💎", "👑"]', 
'{"🍒🍒🍒": 5, "🍋🍋🍋": 10, "🔔🔔🔔": 15, "💎💎💎": 100, "👑👑👑": 50}'),

('Diamond Strike', 'diamond-strike', 'Luxurious diamond-themed slot', 'premium', 97.20, 5.00, 500.00,
'["💎", "👑", "⭐", "💰", "🍀"]',
'{"💎💎💎": 100, "👑👑👑": 50, "⭐⭐⭐": 30, "💰💰💰": 25, "🍀🍀🍀": 20}'),

('Royal Fortune', 'royal-fortune', 'Regal themed slot with bonus features', 'premium', 96.80, 2.00, 200.00,
'["👑", "💎", "🏰", "⚔️", "🛡️"]',
'{"👑👑👑": 50, "💎💎💎": 100, "🏰🏰🏰": 75, "⚔️⚔️⚔️": 40, "🛡️🛡️🛡️": 30}');

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;