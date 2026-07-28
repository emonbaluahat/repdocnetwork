<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use App\Core\AuthContext;

class DocumentTemplate extends Model
{
    protected static string $table = 'document_templates';
    protected static string $primaryKey = 'id';
    protected static bool $tenantAware = true;
    protected static string $tenantColumn = 'shop_id';
    protected static array $fillable = [
        'shop_id', 'name', 'category', 'template_type',
        'content', 'variables', 'paper_size', 'status', 'created_by',
    ];

    public static function getByShop(int $shopId, string $search = '', string $category = '', string $type = '', string $status = '', int $page = 1, int $perPage = 25): array
    {
        $db = Database::getInstance();
        $where = 'shop_id = :shop_id';
        $params = ['shop_id' => $shopId];

        if ($search) {
            $like = "%{$search}%";
            $where .= " AND (name LIKE :search OR category LIKE :search2)";
            $params['search'] = $like;
            $params['search2'] = $like;
        }
        if ($category) {
            $where .= " AND category = :category";
            $params['category'] = $category;
        }
        if ($type) {
            $where .= " AND template_type = :type";
            $params['type'] = $type;
        }
        if ($status) {
            $where .= " AND status = :status";
            $params['status'] = $status;
        }

        $countResult = $db->fetch("SELECT COUNT(*) as count FROM document_templates WHERE {$where}", $params);
        $total = (int) ($countResult['count'] ?? 0);
        $totalPages = max(1, (int) ceil($total / $perPage));
        $page = max(1, min($page, $totalPages));
        $offset = ($page - 1) * $perPage;

        $items = $db->fetchAll(
            "SELECT * FROM document_templates WHERE {$where} ORDER BY created_at DESC LIMIT :limit OFFSET :offset",
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

    public static function getActiveByCategory(int $shopId): array
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT * FROM document_templates WHERE shop_id = :shop_id AND status = 'active' ORDER BY category, name",
            ['shop_id' => $shopId]
        );
    }

    public static function getCategories(int $shopId): array
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT DISTINCT category FROM document_templates WHERE shop_id = :shop_id AND category != '' ORDER BY category",
            ['shop_id' => $shopId]
        );
    }

    public static function duplicate(int $id): ?int
    {
        $template = self::find($id);
        if (!$template) return null;

        $data = [
            'shop_id' => $template['shop_id'],
            'name' => $template['name'] . ' (কপি)',
            'category' => $template['category'],
            'template_type' => $template['template_type'],
            'content' => $template['content'],
            'variables' => $template['variables'],
            'paper_size' => $template['paper_size'],
            'status' => 'inactive',
            'created_by' => AuthContext::id(),
        ];
        return self::create($data);
    }

    public static function getTypes(): array
    {
        return [
            'certificate' => 'সার্টিফিকেট',
            'application' => 'আবেদনপত্র',
            'cv' => 'সিভি',
            'money_receipt' => 'মণি রিসিট',
            'invoice' => 'ইনভয়েস',
            'job_sheet' => 'জব শিট',
            'warranty_card' => 'ওয়ারেন্টি কার্ড',
            'other' => 'অন্যান্য',
        ];
    }

    public static function paperSizes(): array
    {
        return ['A4', 'A5', 'Letter', 'Legal'];
    }
}
