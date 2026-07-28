CREATE TABLE IF NOT EXISTS `certificate_fields` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT,
    `certificate_type_id` BIGINT UNSIGNED NOT NULL,
    `field_name` VARCHAR(64) NOT NULL,
    `label_bn` VARCHAR(255) NOT NULL,
    `field_type` ENUM('text','number','date','select','textarea') NOT NULL DEFAULT 'text',
    `options` JSON NULL,
    `required` TINYINT(1) NOT NULL DEFAULT 0,
    `position` INT UNSIGNED NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `cert_fields_type_id_idx` (`certificate_type_id`),
    UNIQUE KEY `cert_fields_type_field_idx` (`certificate_type_id`, `field_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
