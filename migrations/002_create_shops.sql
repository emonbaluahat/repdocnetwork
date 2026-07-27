-- Migration: 002_create_shops
-- Description: Tenant/shop containers — each shop is a tenant

CREATE TABLE `shops` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(200) NOT NULL,
    `slug` VARCHAR(100) NOT NULL UNIQUE,
    `domain` VARCHAR(191) NULL UNIQUE,
    `status` ENUM('pending', 'active', 'suspended', 'closed') NOT NULL DEFAULT 'pending',
    `settings` JSON NULL COMMENT 'Theme, locale, timezone, features',
    `subscription` VARCHAR(50) NULL COMMENT 'Plan name: free, starter, professional, enterprise',
    `expires_at` DATETIME NULL COMMENT 'Subscription expiry',
    `logo` VARCHAR(255) NULL,
    `address` TEXT NULL,
    `phone` VARCHAR(20) NULL,
    `email` VARCHAR(191) NULL,
    `business_hours` JSON NULL COMMENT 'Operating hours per day',
    `owner_id` INT UNSIGNED NOT NULL,
    `created_by` INT UNSIGNED NULL,
    `verified_at` DATETIME NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `shops_status_index` (`status`),
    INDEX `shops_owner_id_index` (`owner_id`),
    CONSTRAINT `shops_owner_id_foreign` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
