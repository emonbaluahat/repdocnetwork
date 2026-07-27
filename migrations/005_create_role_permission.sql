-- Migration: 005_create_role_permission
-- Description: Role-permission mapping — assigns permissions to roles

CREATE TABLE `role_permission` (
    `role` ENUM('owner', 'admin', 'operator', 'customer') NOT NULL,
    `permission_id` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`role`, `permission_id`),
    CONSTRAINT `role_permission_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed role-permission mappings
-- Owner: all permissions
INSERT INTO `role_permission` (`role`, `permission_id`)
SELECT 'owner', `id` FROM `permissions`;

-- Admin: all except manage_billing
INSERT INTO `role_permission` (`role`, `permission_id`)
SELECT 'admin', `id` FROM `permissions` WHERE `slug` != 'manage_billing';

-- Operator: view/create customers, view/create documents
INSERT INTO `role_permission` (`role`, `permission_id`)
SELECT 'operator', `id` FROM `permissions` WHERE `slug` IN (
    'view_customers', 'create_customers', 'edit_customers',
    'view_documents', 'create_documents'
);

-- Customer: view own documents
INSERT INTO `role_permission` (`role`, `permission_id`)
SELECT 'customer', `id` FROM `permissions` WHERE `slug` = 'view_documents';
