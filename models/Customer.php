<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Customer extends Model
{
    protected static string $table = 'customers';
    protected static string $primaryKey = 'id';
    protected static bool $tenantAware = true;
    protected static string $tenantColumn = 'shop_id';
    protected static array $fillable = [
        'shop_id',
        'name',
        'phone',
        'email',
        'nid',
        'address',
        'photo',
        'tags',
        'metadata',
        'notes',
        'created_by',
    ];

    public static function search(int $shopId, string $query, int $limit = 20): array
    {
        $db = Database::getInstance();
        $like = "%{$query}%";
        return $db->fetchAll(
            "SELECT * FROM customers
             WHERE shop_id = :shop_id
             AND (name LIKE :q1 OR phone LIKE :q2 OR email LIKE :q3 OR nid LIKE :q4)
             ORDER BY name ASC
             LIMIT :limit",
            [
                'shop_id' => $shopId,
                'q1' => $like,
                'q2' => $like,
                'q3' => $like,
                'q4' => $like,
                'limit' => $limit,
            ]
        );
    }

    public static function findByPhone(int $shopId, string $phone): ?array
    {
        $db = Database::getInstance();
        return $db->fetch(
            "SELECT * FROM customers WHERE shop_id = :shop_id AND phone = :phone LIMIT 1",
            ['shop_id' => $shopId, 'phone' => $phone]
        );
    }

    public static function findByNid(int $shopId, string $nid): ?array
    {
        $db = Database::getInstance();
        return $db->fetch(
            "SELECT * FROM customers WHERE shop_id = :shop_id AND nid = :nid LIMIT 1",
            ['shop_id' => $shopId, 'nid' => $nid]
        );
    }

    public static function findByEmail(int $shopId, string $email): ?array
    {
        $db = Database::getInstance();
        return $db->fetch(
            "SELECT * FROM customers WHERE shop_id = :shop_id AND email = :email LIMIT 1",
            ['shop_id' => $shopId, 'email' => $email]
        );
    }

    public static function getByShop(int $shopId, string $search = '', string $tag = '', string $orderBy = 'created_at DESC', int $page = 1, int $perPage = 25): array
    {
        $db = Database::getInstance();
        $where = "shop_id = :shop_id";
        $params = ['shop_id' => $shopId];

        if ($search) {
            $like = "%{$search}%";
            $where .= " AND (name LIKE :search OR phone LIKE :search2 OR email LIKE :search3 OR nid LIKE :search4)";
            $params['search'] = $like;
            $params['search2'] = $like;
            $params['search3'] = $like;
            $params['search4'] = $like;
        }

        if ($tag) {
            $where .= " AND JSON_CONTAINS(tags, :tag)";
            $params['tag'] = json_encode($tag);
        }

        $countResult = $db->fetch("SELECT COUNT(*) as count FROM customers WHERE {$where}", $params);
        $total = (int) ($countResult['count'] ?? 0);
        $totalPages = max(1, (int) ceil($total / $perPage));
        $page = max(1, min($page, $totalPages));
        $offset = ($page - 1) * $perPage;

        $items = $db->fetchAll(
            "SELECT * FROM customers WHERE {$where} ORDER BY {$orderBy} LIMIT :limit OFFSET :offset",
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

    public static function getTimeline(int $customerId, int $shopId, int $limit = 50): array
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

    public static function getTagCounts(int $shopId): array
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT DISTINCT JSON_EXTRACT(tags, '$[*]') as tag_list FROM customers WHERE shop_id = :shop_id AND tags IS NOT NULL",
            ['shop_id' => $shopId]
        );
    }
}