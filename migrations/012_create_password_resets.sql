-- Migration: 012_create_password_resets
-- Description: Password reset tokens with expiry

CREATE TABLE `password_resets` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `token` VARCHAR(128) NOT NULL,
    `type` ENUM('email', 'phone') NOT NULL DEFAULT 'email',
    `expires_at` DATETIME NOT NULL,
    `used_at` DATETIME NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `password_resets_user_id_index` (`user_id`),
    INDEX `password_resets_token_index` (`token`),
    INDEX `password_resets_expires_at_index` (`expires_at`),
    CONSTRAINT `password_resets_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
