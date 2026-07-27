-- Migration: 001_create_users
-- Description: All platform users

CREATE TABLE `users` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `username` VARCHAR(50) NULL UNIQUE,
    `email` VARCHAR(191) NOT NULL UNIQUE,
    `phone` VARCHAR(20) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `avatar` VARCHAR(255) NULL,
    `preferences` JSON NULL,
    `language` VARCHAR(10) NOT NULL DEFAULT 'bn',
    `theme` VARCHAR(10) NOT NULL DEFAULT 'light',
    `email_verified_at` DATETIME NULL,
    `phone_verified_at` DATETIME NULL,
    `last_login_at` DATETIME NULL,
    `status` ENUM('active', 'inactive', 'blocked') NOT NULL DEFAULT 'active',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `users_email_index` (`email`),
    INDEX `users_phone_index` (`phone`),
    INDEX `users_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
