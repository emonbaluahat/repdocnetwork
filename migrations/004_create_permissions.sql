-- Migration: 004_create_permissions
-- Description: Permission registry — defines all available permissions

CREATE TABLE `permissions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(100) NOT NULL UNIQUE,
    `module` VARCHAR(50) NOT NULL COMMENT 'Module this permission belongs to',
    `description` TEXT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `permissions_module_index` (`module`),
    INDEX `permissions_slug_index` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed default permissions
INSERT INTO `permissions` (`name`, `slug`, `module`, `description`) VALUES
('Manage Shop', 'manage_shop', 'shop', 'Manage shop settings and profile'),
('Manage Operators', 'manage_operators', 'shop', 'Add, remove, and manage operators'),
('View Customers', 'view_customers', 'crm', 'View customer list and details'),
('Create Customers', 'create_customers', 'crm', 'Add new customers'),
('Edit Customers', 'edit_customers', 'crm', 'Edit existing customer information'),
('Delete Customers', 'delete_customers', 'crm', 'Remove customers from the system'),
('View Documents', 'view_documents', 'documents', 'View document list and details'),
('Create Documents', 'create_documents', 'documents', 'Generate new documents'),
('Edit Documents', 'edit_documents', 'documents', 'Modify existing documents'),
('Void Documents', 'void_documents', 'documents', 'Void/cancel generated documents'),
('Manage Templates', 'manage_templates', 'templates', 'Create and edit document templates'),
('View Reports', 'view_reports', 'reports', 'Access reports and analytics'),
('Manage Settings', 'manage_settings', 'settings', 'Configure system and shop settings'),
('Manage Billing', 'manage_billing', 'billing', 'Access billing and subscription management');
