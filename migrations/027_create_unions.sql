CREATE TABLE IF NOT EXISTS `unions` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT,
    `upazila_id` BIGINT UNSIGNED NOT NULL,
    `name_bn` VARCHAR(255) NOT NULL,
    `name_en` VARCHAR(255) NOT NULL,
    `union_type` VARCHAR(32) NOT NULL DEFAULT 'union',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `unions_upazila_id_idx` (`upazila_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
