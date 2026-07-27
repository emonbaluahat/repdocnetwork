<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Service extends Model
{
    protected static string $table = 'services';
    protected static string $primaryKey = 'id';
    protected static bool $tenantAware = true;
    protected static string $tenantColumn = 'shop_id';
    protected static array $fillable = [
        'shop_id',
        'category_id',
        'name',
        'description',
        'price',
        'cost_price',
        'unit',
        'status',
        'sort_order',
    ];

    public static function search(int $shopId, string $query = '', ?int $categoryId = null, string $status = '', string $orderBy = 'sort_order ASC, name ASC', int $page = 1, int $perPage = 25): array
    {
        $db = Database::getInstance();
        $where = "s.shop_id = :shop_id";
        $params = ['shop_id' => $shopId];

        if ($query) {
            $like = "%{$query}%";
            $where .= " AND (s.name LIKE :search OR s.description LIKE :search2)";
            $params['search'] = $like;
            $params['search2'] = $like;
        }

        if ($categoryId) {
            $where .= " AND s.category_id = :category_id";
            $params['category_id'] = $categoryId;
        }

        if ($status === 'active' || $status === 'inactive') {
            $where .= " AND s.status = :status";
            $params['status'] = $status;
        }

        $countResult = $db->fetch("SELECT COUNT(*) as count FROM services s WHERE {$where}", $params);
        $total = (int) ($countResult['count'] ?? 0);
        $totalPages = max(1, (int) ceil($total / $perPage));
        $page = max(1, min($page, $totalPages));
        $offset = ($page - 1) * $perPage;

        $items = $db->fetchAll(
            "SELECT s.*, sc.name as category_name, sc.color as category_color
             FROM services s
             LEFT JOIN service_categories sc ON s.category_id = sc.id
             WHERE {$where}
             ORDER BY {$orderBy}
             LIMIT :limit OFFSET :offset",
            array_merge($params, ['limit' => $perPage, 'offset' => $offset])
        );

        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => $totalPages,
            'has_prev' => $page > 1,
            'has_next' => $page < $totalPages,
        ];
    }

    public static function getActiveByCategory(int $shopId, ?int $categoryId = null): array
    {
        $db = Database::getInstance();
        $params = ['shop_id' => $shopId];
        $where = "shop_id = :shop_id AND status = 'active'";

        if ($categoryId) {
            $where .= " AND category_id = :category_id";
            $params['category_id'] = $categoryId;
        }

        return $db->fetchAll(
            "SELECT s.*, sc.name as category_name
             FROM services s
             LEFT JOIN service_categories sc ON s.category_id = sc.id
             WHERE {$where}
             ORDER BY s.sort_order ASC, s.name ASC",
            $params
        );
    }
}