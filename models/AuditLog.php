<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use App\Core\AuthContext;
use App\Core\Request;

class AuditLog extends Model
{
    protected static string $table = 'audit_logs';
    protected static string $primaryKey = 'id';
    protected static bool $tenantAware = false;
    protected static array $fillable = [
        'user_id',
        'shop_id',
        'action',
        'entity_type',
        'entity_id',
        'old_data',
        'new_data',
        'ip_address',
        'user_agent',
    ];

    public static function log(
        string $action,
        string $entityType,
        ?int $entityId = null,
        ?array $oldData = null,
        ?array $newData = null,
        ?int $userId = null,
        ?int $shopId = null
    ): int {
        return self::create([
            'user_id' => $userId ?? AuthContext::id(),
            'shop_id' => $shopId ?? AuthContext::shopId(),
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'old_data' => $oldData ? json_encode($oldData, JSON_UNESCAPED_UNICODE) : null,
            'new_data' => $newData ? json_encode($newData, JSON_UNESCAPED_UNICODE) : null,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    public static function getByUser(int $userId, int $limit = 50): array
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT * FROM " . self::$table . " WHERE user_id = :user_id ORDER BY created_at DESC LIMIT :limit",
            ['user_id' => $userId, 'limit' => $limit]
        );
    }

    public static function getByEntity(string $entityType, int $entityId, int $limit = 50): array
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT * FROM " . self::$table . " WHERE entity_type = :entity_type AND entity_id = :entity_id ORDER BY created_at DESC LIMIT :limit",
            ['entity_type' => $entityType, 'entity_id' => $entityId, 'limit' => $limit]
        );
    }

    public static function search(array $filters = [], int $page = 1, int $perPage = 50): array
    {
        $db = Database::getInstance();
        $sql = "SELECT al.*, u.name as user_name FROM " . self::$table . " al
                LEFT JOIN users u ON al.user_id = u.id";
        $where = [];
        $params = [];
        $hasWhere = false;

        if (!empty($filters['action'])) {
            $where[] = "al.action = :action";
            $params['action'] = $filters['action'];
        }
        if (!empty($filters['entity_type'])) {
            $where[] = "al.entity_type = :entity_type";
            $params['entity_type'] = $filters['entity_type'];
        }
        if (!empty($filters['user_id'])) {
            $where[] = "al.user_id = :user_id";
            $params['user_id'] = $filters['user_id'];
        }
        if (!empty($filters['shop_id'])) {
            $where[] = "al.shop_id = :shop_id";
            $params['shop_id'] = $filters['shop_id'];
        }
        if (!empty($filters['date_from'])) {
            $where[] = "al.created_at >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where[] = "al.created_at <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $where);
            $hasWhere = true;
        }

        $countSql = "SELECT COUNT(*) as count FROM " . self::$table . " al";
        if ($hasWhere) {
            $countSql .= " WHERE " . implode(' AND ', $where);
        }
        $totalResult = $db->fetch($countSql, $params);
        $total = (int) ($totalResult['count'] ?? 0);

        $totalPages = max(1, (int) ceil($total / $perPage));
        $page = max(1, min($page, $totalPages));
        $offset = ($page - 1) * $perPage;

        $sql .= " ORDER BY al.created_at DESC LIMIT :limit OFFSET :offset";
        $params['limit'] = $perPage;
        $params['offset'] = $offset;

        $items = $db->fetchAll($sql, $params);

        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => $totalPages,
        ];
    }
}
