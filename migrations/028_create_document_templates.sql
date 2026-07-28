CREATE TABLE IF NOT EXISTS `document_templates` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT,
    `shop_id` BIGINT UNSIGNED NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `category` ENUM('general','certificate','form','report') NOT NULL DEFAULT 'general',
    `template_type` VARCHAR(64) NOT NULL DEFAULT 'html',
    `content` LONGTEXT NOT NULL,
    `variables` JSON NULL,
    `paper_size` ENUM('A4_PORTRAIT','A4_LANDSCAPE','LETTER') NOT NULL DEFAULT 'A4_PORTRAIT',
    `status` ENUM('active','inactive','draft') NOT NULL DEFAULT 'draft',
    `created_by` BIGINT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `templates_shop_id_idx` (`shop_id`),
    KEY `templates_category_idx` (`category`),
    KEY `templates_status_idx` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
