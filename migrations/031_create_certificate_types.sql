CREATE TABLE IF NOT EXISTS `certificate_types` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT,
    `slug` VARCHAR(64) NOT NULL UNIQUE,
    `name_bn` VARCHAR(255) NOT NULL,
    `name_en` VARCHAR(255) NOT NULL,
    `category` VARCHAR(64) NOT NULL DEFAULT 'general',
    `fee` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `cert_types_category_idx` (`category`),
    KEY `cert_types_status_idx` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
