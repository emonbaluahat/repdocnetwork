-- Migration: 020_create_orders
-- Description: Shop-scoped service order workflow

CREATE TABLE `orders` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `shop_id` INT UNSIGNED NOT NULL,
    `customer_id` INT UNSIGNED NOT NULL,
    `reference` VARCHAR(50) NOT NULL UNIQUE,
    `status` ENUM('pending','confirmed','in_progress','ready','completed','cancelled','delivered') NOT NULL DEFAULT 'pending',
    `priority` ENUM('normal','urgent','express') NOT NULL DEFAULT 'normal',
    `amount` DECIMAL(10,2) NOT NULL DEFAULT 0,
    `paid_amount` DECIMAL(10,2) NOT NULL DEFAULT 0,
    `due_amount` DECIMAL(10,2) NOT NULL DEFAULT 0,
    `discount_amount` DECIMAL(10,2) NOT NULL DEFAULT 0,
    `discount_type` ENUM('fixed','percentage') NULL,
    `tax_amount` DECIMAL(10,2) NOT NULL DEFAULT 0,
    `notes` TEXT NULL,
    `internal_notes` TEXT NULL,
    `estimated_ready_at` DATETIME NULL,
    `completed_at` DATETIME NULL,
    `delivered_at` DATETIME NULL,
    `created_by` INT UNSIGNED NULL,
    `updated_by` INT UNSIGNED NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `orders_shop_id_index` (`shop_id`),
    INDEX `orders_customer_id_index` (`customer_id`),
    INDEX `orders_reference_index` (`reference`),
    INDEX `orders_status_index` (`status`),
    INDEX `orders_created_at_index` (`created_at`),
    CONSTRAINT `orders_shop_id_foreign` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`) ON DELETE CASCADE,
    CONSTRAINT `orders_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;