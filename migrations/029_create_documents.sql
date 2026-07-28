CREATE TABLE IF NOT EXISTS `documents` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT,
    `shop_id` BIGINT UNSIGNED NOT NULL,
    `customer_id` BIGINT UNSIGNED NULL,
    `template_id` BIGINT UNSIGNED NULL,
    `document_number` VARCHAR(32) NOT NULL,
    `data` JSON NULL,
    `generated_file` VARCHAR(255) NULL,
    `status` ENUM('draft','final','voided') NOT NULL DEFAULT 'draft',
    `created_by` BIGINT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `documents_shop_id_idx` (`shop_id`),
    KEY `documents_customer_id_idx` (`customer_id`),
    KEY `documents_template_id_idx` (`template_id`),
    KEY `documents_status_idx` (`status`),
    UNIQUE KEY `documents_number_idx` (`shop_id`, `document_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
