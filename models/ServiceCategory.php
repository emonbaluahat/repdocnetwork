<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class ServiceCategory extends Model
{
    protected static string $table = 'service_categories';
    protected static string $primaryKey = 'id';
    protected static bool $tenantAware = true;
    protected static string $tenantColumn = 'shop_id';
    protected static array $fillable = [
        'shop_id',
        'name',
        'description',
        'icon',
        'color',
        'sort_order',
    ];

    public static function getByShop(int $shopId): array
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT sc.*,
                    (SELECT COUNT(*) FROM services s WHERE s.category_id = sc.id AND s.status = 'active') as service_count
             FROM service_categories sc
             WHERE sc.shop_id = :shop_id
             ORDER BY sc.sort_order ASC, sc.name ASC",
            ['shop_id' => $shopId]
        );
    }

    public static function getAsOptions(int $shopId): array
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT id, name FROM service_categories WHERE shop_id = :shop_id ORDER BY name ASC",
            ['shop_id' => $shopId]
        );
    }
}