-- Migration: 009_create_settings
-- Description: Global and tenant-specific settings (key-value store)

CREATE TABLE `settings` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `shop_id` INT UNSIGNED NULL COMMENT 'NULL = global setting, non-NULL = shop-specific',
    `key` VARCHAR(100) NOT NULL,
    `value` JSON NOT NULL COMMENT 'Setting value (supports complex types via JSON)',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `settings_shop_key_unique` (`shop_id`, `key`),
    INDEX `settings_key_index` (`key`),
    CONSTRAINT `settings_shop_id_foreign` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed default global settings
INSERT INTO `settings` (`shop_id`, `key`, `value`) VALUES
(NULL, 'app.name', '"RepDoc Network"'),
(NULL, 'app.locale', '"bn"'),
(NULL, 'app.timezone', '"Asia/Dhaka"'),
(NULL, 'app.currency', '"BDT"'),
(NULL, 'app.date_format', '"d M Y"'),
(NULL, 'auth.otp_expiry_minutes', '5'),
(NULL, 'auth.max_login_attempts', '5'),
(NULL, 'auth.lockout_minutes', '15'),
(NULL, 'storage.max_file_size_mb', '10'),
(NULL, 'storage.allowed_file_types', '["pdf","docx","jpg","png"]'),
(NULL, 'document.reference_prefix', '"DOC"'),
(NULL, 'theme.default', '"light"'),
(NULL, 'theme.allow_dark_mode', 'true');
