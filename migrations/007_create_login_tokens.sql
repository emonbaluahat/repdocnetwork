-- Migration: 007_create_login_tokens
-- Description: One-time tokens for OTP login, email verification, password reset

CREATE TABLE `login_tokens` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `token` VARCHAR(128) NOT NULL,
    `type` ENUM('email', 'phone', 'password_reset') NOT NULL DEFAULT 'phone',
    `expires_at` DATETIME NOT NULL,
    `used_at` DATETIME NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `login_tokens_user_id_index` (`user_id`),
    INDEX `login_tokens_token_index` (`token`),
    INDEX `login_tokens_expires_at_index` (`expires_at`),
    CONSTRAINT `login_tokens_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
