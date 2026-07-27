-- Migration: 010_create_notifications
-- Description: System notifications for users

CREATE TABLE `notifications` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `shop_id` INT UNSIGNED NULL,
    `type` VARCHAR(50) NOT NULL COMMENT 'Notification type (e.g., document.ready, order.placed)',
    `title` VARCHAR(200) NOT NULL,
    `message` TEXT NULL,
    `data` JSON NULL COMMENT 'Additional payload for deep linking',
    `read_at` DATETIME NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `notifications_user_id_index` (`user_id`),
    INDEX `notifications_shop_id_index` (`shop_id`),
    INDEX `notifications_read_at_index` (`read_at`),
    INDEX `notifications_created_at_index` (`created_at`),
    CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `notifications_shop_id_foreign` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
