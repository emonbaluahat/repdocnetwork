<?php
define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/vendor/autoload.php';

$envFile = APP_ROOT . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;
        if (str_contains($line, '=')) {
            $pos = strpos($line, '=');
            $_ENV[trim(substr($line, 0, $pos))] = trim(substr($line, $pos + 1));
            putenv($line);
        }
    }
}
require_once APP_ROOT . '/config/app.php';
require_once APP_ROOT . '/config/database.php';

use App\Core\Database;

$db = Database::getInstance();
$pdo = $db->getPdo();

$shopId = 1;
$userId = 1;

try {
    $pdo->beginTransaction();

    // ── Categories ──
    $categories = [
        ['name' => 'ধোয়া ও প্রেস', 'description' => 'Laundry & Ironing'],
        ['name' => 'ড্রাই ক্লিন', 'description' => 'Dry Cleaning'],
        ['name' => 'সেলাই ও মেরামত', 'description' => 'Tailoring & Repairs'],
    ];
    $catIds = [];
    foreach ($categories as $c) {
        $pdo->prepare("INSERT INTO service_categories (shop_id, name, description, created_at) VALUES (?, ?, ?, NOW())")->execute([$shopId, $c['name'], $c['description']]);
        $catIds[] = (int) $pdo->lastInsertId();
    }
    echo "  Categories: " . count($categories) . "\n";

    // ── Services (20) ──
    $serviceNames = [
        'শার্ট ধোয়া', 'প্যান্ট ধোয়া', 'গেঞ্জি ধোয়া', 'বেডশীট ধোয়া', 'তোয়ালে ধোয়া',
        'স্যুট ড্রাই ক্লিন', 'কোট ড্রাই ক্লিন', 'জ্যাকেট ড্রাই ক্লিন', 'শাড়ি ড্রাই ক্লিন', 'পর্দা ড্রাই ক্লিন',
        'প্যান্ট সেলাই', 'শার্ট সেলাই', 'কুর্তা সেলাই', 'ফ্রক সেলাই', 'ব্লাউজ সেলাই',
        'জামা বোতাম লাগানো', 'প্যান্ট হেম', 'জিপার পরিবর্তন', 'কামিজ সেলাই', 'থ্রি-পিস সেলাই',
    ];
    $prices = [30, 40, 25, 80, 35, 150, 200, 120, 180, 250, 300, 250, 350, 400, 200, 50, 60, 100, 500, 600];
    $catIdx = [0, 0, 0, 0, 0, 1, 1, 1, 1, 1, 2, 2, 2, 2, 2, 2, 2, 2, 2, 2];

    for ($i = 0; $i < 20; $i++) {
        $pdo->prepare("INSERT INTO services (shop_id, category_id, name, price, status, created_at) VALUES (?, ?, ?, ?, 'active', NOW())")
            ->execute([$shopId, $catIds[$catIdx[$i]], $serviceNames[$i], $prices[$i]]);
    }
    echo "  Services: 20\n";

    // ── Customers (20) ──
    $customerNames = [
        'রহিম মিয়া', 'করিম হোসেন', 'ফাতেমা বেগম', 'জামিল আহমেদ', 'নাসরিন আক্তার',
        'সুমন খান', 'রিনা বেগম', 'আব্দুল্লাহ আল মামুন', 'তাহমিনা সুলতানা', 'মোহাম্মদ আলী',
        'সাকিব হাসান', 'মাহমুদা খাতুন', 'ইমরান হোসেন', 'সাবিনা ইয়াসমিন', 'শাহিনুর রহমান',
        'আশরাফুল আলম', 'রোকসানা বেগম', 'জাহিদ হাসান', 'সালমা আক্তার', 'নাজমুল হোসেন',
    ];
    $phones = [
        '01711110001', '01711110002', '01711110003', '01711110004', '01711110005',
        '01711110006', '01711110007', '01711110008', '01711110009', '01711110010',
        '01711110011', '01711110012', '01711110013', '01711110014', '01711110015',
        '01711110016', '01711110017', '01711110018', '01711110019', '01711110020',
    ];

    for ($i = 0; $i < 20; $i++) {
        $pdo->prepare("INSERT INTO customers (shop_id, name, phone, created_by, created_at) VALUES (?, ?, ?, ?, NOW())")
            ->execute([$shopId, $customerNames[$i], $phones[$i], $userId]);
    }
    $customerIds = range(1, 20);
    echo "  Customers: 20\n";

    // ── Orders (20) with items ──
    $statuses = ['pending', 'confirmed', 'in_progress', 'ready', 'completed', 'cancelled', 'delivered'];
    $priorities = ['normal', 'urgent', 'express'];

    for ($i = 0; $i < 20; $i++) {
        $customerId = $customerIds[$i];
        $ref = 'ORD-' . str_pad($i + 1, 5, '0', STR_PAD_LEFT);
        $status = $statuses[$i % 7];
        $priority = $priorities[$i % 3];

        // Pick 1-3 random services for this order
        $numItems = rand(1, 3);
        $total = 0;
        $items = [];
        $used = [];
        for ($j = 0; $j < $numItems; $j++) {
            $svcId = rand(1, 20);
            while (in_array($svcId, $used)) $svcId = rand(1, 20);
            $used[] = $svcId;

            $svc = $pdo->prepare("SELECT name, price FROM services WHERE id = ?");
            $svc->execute([$svcId]);
            $s = $svc->fetch(PDO::FETCH_ASSOC);
            if (!$s) continue;

            $qty = rand(1, 3);
            $lineTotal = $s['price'] * $qty;
            $total += $lineTotal;
            $items[] = ['service_id' => $svcId, 'name' => $s['name'], 'quantity' => $qty, 'unit_price' => $s['price'], 'total_price' => $lineTotal];
        }

        $due = round($total * (rand(0, 30) / 100), 2);
        $paid = $total - $due;

        $completedAt = in_array($status, ['ready', 'completed', 'delivered']) ? date('Y-m-d H:i:s', strtotime("-" . rand(1, 48) . " hours")) : null;
        $deliveredAt = $status === 'delivered' ? $completedAt : null;

        $pdo->prepare("INSERT INTO orders (shop_id, customer_id, reference, status, priority, amount, paid_amount, due_amount, discount_amount, tax_amount, completed_at, delivered_at, created_by, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, 0, ?, ?, ?, NOW(), NOW())")
            ->execute([$shopId, $customerId, $ref, $status, $priority, $total, $paid, $due, $completedAt, $deliveredAt, $userId]);

        $orderId = (int) $pdo->lastInsertId();

        foreach ($items as $item) {
            $pdo->prepare("INSERT INTO order_items (order_id, service_id, name, quantity, unit_price, total_price, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())")
                ->execute([$orderId, $item['service_id'], $item['name'], $item['quantity'], $item['unit_price'], $item['total_price']]);
        }

        // Create a transaction for completed/delivered orders
        if (in_array($status, ['completed', 'delivered']) && $paid > 0) {
            $txnRef = 'TXN-' . str_pad($i + 1, 5, '0', STR_PAD_LEFT);
            $methods = ['cash', 'bkash', 'nagad', 'card'];
            $method = $methods[$i % 4];
            $pdo->prepare("INSERT INTO transactions (shop_id, order_id, customer_id, reference, type, method, amount, status, processed_by, created_at, updated_at)
                VALUES (?, ?, ?, ?, 'payment', ?, ?, 'completed', ?, NOW(), NOW())")
                ->execute([$shopId, $orderId, $customerId, $txnRef, $method, $paid, $userId]);
        }
    }
    echo "  Orders: 20 (with items + transactions)\n";

    $pdo->commit();
    echo "\nOK — 20 dummy records inserted for shop {$shopId}\n";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
