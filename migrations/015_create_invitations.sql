-- Migration: 015_create_invitations
-- Description: Staff invitations for shop owners to invite users

CREATE TABLE `invitations` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `shop_id` INT UNSIGNED NOT NULL,
    `invited_by` INT UNSIGNED NOT NULL,
    `email` VARCHAR(191) NULL,
    `phone` VARCHAR(20) NULL,
    `role` ENUM('admin', 'operator', 'customer') NOT NULL DEFAULT 'operator',
    `token` VARCHAR(128) NOT NULL,
    `status` ENUM('pending', 'accepted', 'declined', 'expired') NOT NULL DEFAULT 'pending',
    `expires_at` DATETIME NOT NULL,
    `accepted_at` DATETIME NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `invitations_token_unique` (`token`),
    INDEX `invitations_shop_id_index` (`shop_id`),
    INDEX `invitations_email_index` (`email`),
    INDEX `invitations_phone_index` (`phone`),
    INDEX `invitations_status_index` (`status`),
    CONSTRAINT `invitations_shop_id_foreign` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`) ON DELETE CASCADE,
    CONSTRAINT `invitations_invited_by_foreign` FOREIGN KEY (`invited_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
