CREATE TABLE `documents` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `shop_id` INT UNSIGNED NOT NULL,
    `customer_id` INT UNSIGNED NULL,
    `template_id` INT UNSIGNED NULL,
    `document_number` VARCHAR(50) NOT NULL,
    `data` JSON NULL,
    `generated_file` VARCHAR(255) NULL,
    `status` ENUM('draft','generated','voided') NOT NULL DEFAULT 'draft',
    `created_by` INT UNSIGNED NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_documents_shop` (`shop_id`),
    INDEX `idx_documents_customer` (`customer_id`),
    INDEX `idx_documents_template` (`template_id`),
    CONSTRAINT `fk_documents_shop` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_documents_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_documents_template` FOREIGN KEY (`template_id`) REFERENCES `document_templates` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;