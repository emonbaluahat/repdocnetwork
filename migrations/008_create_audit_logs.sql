-- Migration: 008_create_audit_logs
-- Description: Immutable audit trail for all sensitive operations

CREATE TABLE `audit_logs` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NULL,
    `shop_id` INT UNSIGNED NULL,
    `action` VARCHAR(100) NOT NULL COMMENT 'Action performed (e.g., customer.created, document.generated)',
    `entity_type` VARCHAR(50) NOT NULL COMMENT 'Type of entity affected',
    `entity_id` INT UNSIGNED NULL COMMENT 'ID of the affected entity',
    `old_data` JSON NULL COMMENT 'Previous state (for updates/deletes)',
    `new_data` JSON NULL COMMENT 'New state (for creates/updates)',
    `ip_address` VARCHAR(45) NULL,
    `user_agent` TEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `audit_logs_user_id_index` (`user_id`),
    INDEX `audit_logs_shop_id_index` (`shop_id`),
    INDEX `audit_logs_action_index` (`action`),
    INDEX `audit_logs_entity_index` (`entity_type`, `entity_id`),
    INDEX `audit_logs_created_at_index` (`created_at`),
    CONSTRAINT `audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    CONSTRAINT `audit_logs_shop_id_foreign` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
