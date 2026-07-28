CREATE TABLE `document_templates` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `shop_id` INT UNSIGNED NOT NULL,
    `name` VARCHAR(200) NOT NULL,
    `category` VARCHAR(100) NOT NULL DEFAULT '',
    `template_type` ENUM('certificate','application','cv','money_receipt','invoice','job_sheet','warranty_card','other') NOT NULL DEFAULT 'other',
    `content` TEXT NOT NULL,
    `variables` JSON NULL,
    `paper_size` VARCHAR(20) NOT NULL DEFAULT 'A4',
    `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
    `created_by` INT UNSIGNED NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_templates_shop` (`shop_id`),
    INDEX `idx_templates_category` (`category`),
    INDEX `idx_templates_type` (`template_type`),
    CONSTRAINT `fk_templates_shop` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `document_templates` (`shop_id`, `name`, `category`, `template_type`, `content`, `variables`, `created_at`) VALUES
(1, 'সার্টিফিকেট', 'সার্টিফিকেট', 'certificate', '<div style=\"text-align:center;padding:40px;font-family:Noto Sans Bengali,sans-serif;\"><h1>সনদপত্র</h1><p>ইতি পূর্বে {{customer_name}} পিতা/স্বামী {{father_name}} এর নিকট থেকে আবেদনক্রমে তাঁর {{nid_number}} নম্বর জাতীয় পরিচয়পত্র যাচাই করা হয়েছে।</p><p>সনদ নং: {{document_number}}</p><p>তারিখ: {{date:created_at}}</p></div>', '[\"customer_name\",\"father_name\",\"mother_name\",\"nid_number\",\"phone\",\"address\"]', NOW()),
(1, 'ইনভয়েস', 'আর্থিক', 'invoice', '<div style=\"padding:30px;font-family:Noto Sans Bengali,sans-serif;\"><h2>ইনভয়েস</h2><p>ইনভয়েস নং: {{document_number}}</p><p>গ্রাহক: {{customer_name}}</p><p>ফোন: {{phone}}</p><hr><p>সেবা: {{service_name}}</p><p>পরিমাণ: {{quantity}}</p><p>মূল্য: ৳{{number:amount}}</p><hr><p><strong>মোট: ৳{{number:amount}}</strong></p></div>', '[\"customer_name\",\"phone\",\"service_name\",\"quantity\",\"amount\"]', NOW()),
(1, 'মানি রিসিট', 'আর্থিক', 'money_receipt', '<div style=\"padding:30px;font-family:Noto Sans Bengali,sans-serif;\"><h2>মণি রিসিট</h2><p>রিসিট নং: {{document_number}}</p><p>প্রাপ্তির তারিখ: {{date:created_at}}</p><hr><p>নিকট হতে প্রাপ্ত: {{customer_name}}</p><p>টাকার পরিমাণ: ৳{{number:amount}}</p><p>উদ্দেশ্য: {{purpose}}</p><hr><p style=\"text-align:right;\">স্বাক্ষর</p></div>', '[\"customer_name\",\"amount\",\"purpose\",\"phone\"]', NOW()),
(1, 'জব শিট', 'সেবা', 'job_sheet', '<div style=\"padding:30px;font-family:Noto Sans Bengali,sans-serif;\"><h2>জব শিট</h2><p>জব নং: {{document_number}}</p><p>গ্রাহক: {{customer_name}}</p><p>ফোন: {{phone}}</p><p>ডিভাইস: {{device}}</p><p>সমস্যা: {{issue}}</p><p>প্রাপ্তির তারিখ: {{date:created_at}}</p></div>', '[\"customer_name\",\"phone\",\"device\",\"issue\"]', NOW());