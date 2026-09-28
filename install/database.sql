CREATE DATABASE IF NOT EXISTS `quewing_system` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `quewing_system`;

CREATE TABLE `users` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `username` VARCHAR(50) UNIQUE NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `first_name` VARCHAR(100),
  `last_name` VARCHAR(100),
  `role` ENUM('counter', 'admin') NOT NULL,
  `is_active` BOOLEAN DEFAULT true,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE `services` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `avg_time` INT,
  `is_active` BOOLEAN DEFAULT true,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO `services` (`name`, `avg_time`) VALUES
('Loan Withdrawal', 10),
('Insurance Claims', 15),
('Payment', 5),
('Loan Release', 10),
('For Inquiry and Concern', 5);

CREATE TABLE `counters` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `counter_number` INT UNIQUE NOT NULL,
  `status` ENUM('available', 'busy', 'paused') DEFAULT 'available',
  `staff_id` INT DEFAULT NULL,
  `assigned_by_admin` BOOLEAN NOT NULL DEFAULT false,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`staff_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE `tickets` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `ticket_number` VARCHAR(20) UNIQUE NOT NULL,
  `service_id` INT DEFAULT NULL,
  `priority` ENUM('normal', 'senior', 'pwd', 'pregnant') DEFAULT 'normal',
  `customer_name` VARCHAR(200),
  `customer_phone` VARCHAR(20),
  `notes` TEXT,
  `status` ENUM('waiting', 'called', 'serving', 'completed', 'no_show') DEFAULT 'waiting',
  `called_at` TIMESTAMP NULL DEFAULT NULL,
  `completed_at` TIMESTAMP NULL DEFAULT NULL,
  `served_by_counter` INT DEFAULT NULL,
  `service_duration` INT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `created_date` DATE DEFAULT (CURRENT_DATE),
  FOREIGN KEY (`service_id`) REFERENCES `services`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`served_by_counter`) REFERENCES `counters`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE `audit_logs` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `user_id` INT DEFAULT NULL,
  `action` VARCHAR(100),
  `details` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE `settings` (
  `key` VARCHAR(100) PRIMARY KEY,
  `value` TEXT
) ENGINE=InnoDB;

INSERT INTO `settings` (`key`, `value`) VALUES
('system_name', 'SFI QUEUING SYSTEM - SIMPLIFIED'),
('operating_hours', '8:00 AM - 5:00 PM'),
('max_tickets_per_day', '500'),
('ticket_prefix', 'SFI');
