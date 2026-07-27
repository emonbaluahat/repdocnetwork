-- Migration: 011_create_failed_logins
-- Description: Track failed login attempts for rate limiting and account lockout

CREATE TABLE `failed_logins` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NULL,
    `ip_address` VARCHAR(45) NOT NULL,
    `key_identifier` VARCHAR(255) NULL COMMENT 'Rate limiting key (login:user:ip or otp:phone)',
    `attempted_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `failed_logins_user_id_index` (`user_id`),
    INDEX `failed_logins_ip_index` (`ip_address`),
    INDEX `failed_logins_key_index` (`key_identifier`),
    INDEX `failed_logins_attempted_at_index` (`attempted_at`),
    CONSTRAINT `failed_logins_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
