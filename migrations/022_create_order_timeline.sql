-- Migration: 022_create_order_timeline
-- Description: Activity log per order

CREATE TABLE `order_timeline` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT UNSIGNED NOT NULL,
    `shop_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NULL,
    `action` VARCHAR(100) NOT NULL COMMENT 'created, status_changed, payment_received, item_added, note_added, etc.',
    `description` TEXT NULL,
    `metadata` JSON NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `order_timeline_order_id_index` (`order_id`),
    INDEX `order_timeline_shop_id_index` (`shop_id`),
    INDEX `order_timeline_action_index` (`action`),
    CONSTRAINT `order_timeline_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
    CONSTRAINT `order_timeline_shop_id_foreign` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;