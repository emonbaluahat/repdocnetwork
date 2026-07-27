<?php
/**
 * Development Seed Script
 * Creates a test user, shop, and shop-user association.
 * Run: php seeds/seed_dev.php
 */

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
            $key = trim(substr($line, 0, $pos));
            $value = trim(substr($line, $pos + 1));
            $_ENV[$key] = $value;
            putenv("{$key}={$value}");
        }
    }
}

require_once APP_ROOT . '/config/app.php';
require_once APP_ROOT . '/config/database.php';

use App\Core\Database;

$db = Database::getInstance();
$pdo = $db->getPdo();

$hash = password_hash('password123', PASSWORD_BCRYPT, ['cost' => 12]);

try {
    $pdo->beginTransaction();

    $pdo->exec("
        INSERT INTO users (name, username, email, phone, password, status, created_at, updated_at)
        VALUES ('Admin User', 'admin', 'admin@repdoc.test', '01711111111', {$pdo->quote($hash)}, 'active', NOW(), NOW())
    ");
    $userId = (int) $pdo->lastInsertId();

    $settings = json_encode([
        'business_hours' => [
            'saturday' => ['open' => '09:00', 'close' => '21:00', 'closed' => false],
            'sunday' => ['open' => '09:00', 'close' => '21:00', 'closed' => false],
            'monday' => ['open' => '09:00', 'close' => '21:00', 'closed' => false],
            'tuesday' => ['open' => '09:00', 'close' => '21:00', 'closed' => false],
            'wednesday' => ['open' => '09:00', 'close' => '21:00', 'closed' => false],
            'thursday' => ['open' => '09:00', 'close' => '21:00', 'closed' => false],
            'friday' => ['open' => '14:00', 'close' => '21:00', 'closed' => false],
        ],
        'invoice' => [
            'prefix' => 'INV',
            'show_logo' => true,
            'show_business_hours' => true,
            'footer_text' => 'Thank you for your business!',
            'terms_conditions' => 'Goods once sold cannot be returned or exchanged.',
            'tax_rate' => 0,
            'tax_label' => 'VAT',
        ],
    ], JSON_UNESCAPED_UNICODE);

    $pdo->exec("
        INSERT INTO shops (name, slug, status, settings, owner_id, created_by, created_at, updated_at)
        VALUES ('RepDoc Demo Shop', 'demo', 'active', {$pdo->quote($settings)}, {$userId}, {$userId}, NOW(), NOW())
    ");
    $shopId = (int) $pdo->lastInsertId();

    $pdo->exec("
        INSERT INTO shop_user (shop_id, user_id, role, joined_at, invited_by, is_active)
        VALUES ({$shopId}, {$userId}, 'owner', NOW(), {$userId}, 1)
    ");

    $pdo->commit();
    echo "OK: user_id={$userId}, shop_id={$shopId}\n";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
