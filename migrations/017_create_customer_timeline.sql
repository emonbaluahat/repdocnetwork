-- Migration: 017_create_customer_timeline
-- Description: Activity log per customer

CREATE TABLE `customer_timeline` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `customer_id` INT UNSIGNED NOT NULL,
    `shop_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NULL,
    `action` VARCHAR(100) NOT NULL COMMENT 'created, updated, document_generated, note_added, etc.',
    `description` TEXT NULL,
    `metadata` JSON NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `customer_timeline_customer_id_index` (`customer_id`),
    INDEX `customer_timeline_shop_id_index` (`shop_id`),
    INDEX `customer_timeline_action_index` (`action`),
    CONSTRAINT `customer_timeline_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
    CONSTRAINT `customer_timeline_shop_id_foreign` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;