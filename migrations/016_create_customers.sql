-- Migration: 016_create_customers
-- Description: Shop-scoped customer records

CREATE TABLE `customers` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `shop_id` INT UNSIGNED NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(20) NOT NULL,
    `email` VARCHAR(191) NULL,
    `nid` VARCHAR(20) NULL,
    `address` TEXT NULL,
    `photo` VARCHAR(255) NULL,
    `tags` JSON NULL COMMENT 'Array of tags like Premium, Regular',
    `metadata` JSON NULL COMMENT 'Custom fields, extra data',
    `notes` TEXT NULL,
    `created_by` INT UNSIGNED NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `customers_shop_id_index` (`shop_id`),
    INDEX `customers_name_index` (`name`),
    INDEX `customers_phone_index` (`phone`),
    INDEX `customers_email_index` (`email`),
    INDEX `customers_nid_index` (`nid`),
    CONSTRAINT `customers_shop_id_foreign` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;