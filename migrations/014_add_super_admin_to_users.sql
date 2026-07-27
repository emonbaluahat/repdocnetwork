-- Migration: 014_add_super_admin_to_users
-- Description: Add is_super_admin column to users table

ALTER TABLE `users`
    ADD COLUMN `is_super_admin` TINYINT(1) NOT NULL DEFAULT 0 AFTER `status`,
    ADD INDEX `users_is_super_admin_index` (`is_super_admin`);
