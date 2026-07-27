-- Migration: 013_create_otp_attempts
-- Description: OTP attempt tracking for rate limiting

CREATE TABLE `otp_attempts` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `phone` VARCHAR(20) NULL,
    `email` VARCHAR(191) NULL,
    `ip_address` VARCHAR(45) NOT NULL,
    `attempts` INT UNSIGNED NOT NULL DEFAULT 1,
    `locked_until` DATETIME NULL,
    `last_attempt_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `otp_attempts_phone_index` (`phone`),
    INDEX `otp_attempts_email_index` (`email`),
    INDEX `otp_attempts_ip_index` (`ip_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
