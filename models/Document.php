<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use App\Core\AuthContext;

class Document extends Model
{
    protected static string $table = 'documents';
    protected static string $primaryKey = 'id';
    protected static bool $tenantAware = true;
    protected static string $tenantColumn = 'shop_id';
    protected static array $fillable = [
        'shop_id', 'customer_id', 'template_id', 'document_number',
        'data', 'generated_file', 'status', 'created_by',
    ];

    public static function getByShop(int $shopId, string $search = '', string $status = '', int $page = 1, int $perPage = 25): array
    {
        $db = Database::getInstance();
        $where = 'd.shop_id = :shop_id';
        $params = ['shop_id' => $shopId];

        if ($search) {
            $like = "%{$search}%";
            $where .= " AND (d.document_number LIKE :search OR c.name LIKE :search2)";
            $params['search'] = $like;
            $params['search2'] = $like;
        }
        if ($status) {
            $where .= " AND d.status = :status";
            $params['status'] = $status;
        }

        $countResult = $db->fetch(
            "SELECT COUNT(*) as count FROM documents d LEFT JOIN customers c ON d.customer_id = c.id WHERE {$where}",
            $params
        );
        $total = (int) ($countResult['count'] ?? 0);
        $totalPages = max(1, (int) ceil($total / $perPage));
        $page = max(1, min($page, $totalPages));
        $offset = ($page - 1) * $perPage;

        $items = $db->fetchAll(
            "SELECT d.*, c.name as customer_name, t.name as template_name, t.template_type
             FROM documents d
             LEFT JOIN customers c ON d.customer_id = c.id
             LEFT JOIN document_templates t ON d.template_id = t.id
             WHERE {$where}
             ORDER BY d.created_at DESC LIMIT :limit OFFSET :offset",
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

    public static function generateNumber(): string
    {
        $prefix = 'DOC';
        $date = date('ymd');
        $rand = strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
        return "{$prefix}-{$date}-{$rand}";
    }

    public static function void(int $id): bool
    {
        $doc = self::find($id);
        if (!$doc || $doc['status'] === 'voided') return false;
        return (bool) self::update($id, ['status' => 'voided']);
    }

    public static function getStatuses(): array
    {
        return [
            'draft' => 'খসড়া',
            'generated' => 'জেনারেটেড',
            'voided' => 'বাতিল',
        ];
    }

    public static function getRecent(int $shopId, int $limit = 10): array
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT d.*, c.name as customer_name, t.name as template_name, t.template_type
             FROM documents d
             LEFT JOIN customers c ON d.customer_id = c.id
             LEFT JOIN document_templates t ON d.template_id = t.id
             WHERE d.shop_id = :shop_id
             ORDER BY d.created_at DESC LIMIT :limit",
            ['shop_id' => $shopId, 'limit' => $limit]
        );
    }
}
