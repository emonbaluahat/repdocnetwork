CREATE TABLE IF NOT EXISTS `verifications` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT,
    `document_id` BIGINT UNSIGNED NOT NULL,
    `verification_code` VARCHAR(64) NOT NULL,
    `qr_data` TEXT NULL,
    `status` ENUM('active','used','expired') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `verifications_code_idx` (`verification_code`),
    KEY `verifications_document_id_idx` (`document_id`),
    KEY `verifications_status_idx` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
