<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use App\Core\AuthContext;

class CustomerTimeline extends Model
{
    protected static string $table = 'customer_timeline';
    protected static string $primaryKey = 'id';
    protected static bool $tenantAware = true;
    protected static string $tenantColumn = 'shop_id';
    protected static array $fillable = [
        'customer_id',
        'shop_id',
        'user_id',
        'action',
        'description',
        'metadata',
    ];

    public static function log(int $customerId, int $shopId, string $action, ?string $description = null, ?array $metadata = null): int
    {
        return self::create([
            'customer_id' => $customerId,
            'shop_id' => $shopId,
            'user_id' => AuthContext::id(),
            'action' => $action,
            'description' => $description,
            'metadata' => $metadata ? json_encode($metadata, JSON_UNESCAPED_UNICODE) : null,
        ]);
    }

    public static function getByCustomer(int $customerId, int $shopId, int $limit = 50): array
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT ct.*, u.name as user_name
             FROM customer_timeline ct
             LEFT JOIN users u ON ct.user_id = u.id
             WHERE ct.customer_id = :customer_id AND ct.shop_id = :shop_id
             ORDER BY ct.created_at DESC
             LIMIT :limit",
            ['customer_id' => $customerId, 'shop_id' => $shopId, 'limit' => $limit]
        );
    }

    public static function getByShop(int $shopId, int $limit = 30): array
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT ct.*, u.name as user_name, c.name as customer_name
             FROM customer_timeline ct
             LEFT JOIN users u ON ct.user_id = u.id
             LEFT JOIN customers c ON ct.customer_id = c.id
             WHERE ct.shop_id = :shop_id
             ORDER BY ct.created_at DESC
             LIMIT :limit",
            ['shop_id' => $shopId, 'limit' => $limit]
        );
    }
}