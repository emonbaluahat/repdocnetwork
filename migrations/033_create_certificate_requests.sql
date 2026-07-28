CREATE TABLE IF NOT EXISTS `certificate_requests` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT,
    `shop_id` BIGINT UNSIGNED NOT NULL,
    `certificate_type_id` BIGINT UNSIGNED NOT NULL,
    `customer_id` BIGINT UNSIGNED NULL,
    `data` JSON NULL,
    `status` ENUM('draft','submitted','completed','cancelled') NOT NULL DEFAULT 'draft',
    `created_by` BIGINT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `cert_req_shop_id_idx` (`shop_id`),
    KEY `cert_req_type_id_idx` (`certificate_type_id`),
    KEY `cert_req_customer_id_idx` (`customer_id`),
    KEY `cert_req_status_idx` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
