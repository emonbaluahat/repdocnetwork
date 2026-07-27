-- Migration: 003_create_shop_user
-- Description: User-shop membership pivot with role assignment

CREATE TABLE `shop_user` (
    `shop_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `role` ENUM('owner', 'admin', 'operator', 'customer') NOT NULL DEFAULT 'operator',
    `joined_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `invited_by` INT UNSIGNED NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (`shop_id`, `user_id`),
    INDEX `shop_user_role_index` (`role`),
    INDEX `shop_user_user_id_index` (`user_id`),
    CONSTRAINT `shop_user_shop_id_foreign` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`) ON DELETE CASCADE,
    CONSTRAINT `shop_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `shop_user_invited_by_foreign` FOREIGN KEY (`invited_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
