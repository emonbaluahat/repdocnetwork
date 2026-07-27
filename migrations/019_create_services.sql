-- Migration: 019_create_services
-- Description: Shop-scoped service catalog

CREATE TABLE `services` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `shop_id` INT UNSIGNED NOT NULL,
    `category_id` INT UNSIGNED NULL,
    `name` VARCHAR(100) NOT NULL,
    `description` TEXT NULL,
    `price` DECIMAL(10,2) NOT NULL DEFAULT 0,
    `cost_price` DECIMAL(10,2) NULL,
    `unit` VARCHAR(20) DEFAULT 'pcs',
    `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
    `sort_order` INT UNSIGNED DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `services_shop_id_index` (`shop_id`),
    INDEX `services_category_id_index` (`category_id`),
    INDEX `services_status_index` (`status`),
    INDEX `services_category_id_status_index` (`category_id`, `status`),
    CONSTRAINT `services_shop_id_foreign` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`) ON DELETE CASCADE,
    CONSTRAINT `services_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `service_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
