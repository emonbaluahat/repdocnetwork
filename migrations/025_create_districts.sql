CREATE TABLE IF NOT EXISTS `districts` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT,
    `division_id` BIGINT UNSIGNED NOT NULL,
    `name_bn` VARCHAR(255) NOT NULL,
    `name_en` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `districts_division_id_idx` (`division_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
