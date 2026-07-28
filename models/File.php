<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use App\Core\Security;
use App\Core\AuthContext;

class File extends Model
{
    protected static string $table = 'files';
    protected static string $primaryKey = 'id';
    protected static bool $tenantAware = true;
    protected static string $tenantColumn = 'shop_id';
    protected static array $fillable = [
        'shop_id', 'user_id', 'file_name', 'file_path', 'file_type', 'size',
    ];

    private static array $allowedTypes = ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'doc', 'docx', 'xls', 'xlsx', 'csv'];

    public static function getByShop(int $shopId, int $page = 1, int $perPage = 25): array
    {
        $db = Database::getInstance();
        $where = 'shop_id = :shop_id';
        $params = ['shop_id' => $shopId];

        $countResult = $db->fetch("SELECT COUNT(*) as count FROM files WHERE {$where}", $params);
        $total = (int) ($countResult['count'] ?? 0);
        $totalPages = max(1, (int) ceil($total / $perPage));
        $page = max(1, min($page, $totalPages));
        $offset = ($page - 1) * $perPage;

        $items = $db->fetchAll(
            "SELECT * FROM files WHERE {$where} ORDER BY created_at DESC LIMIT :limit OFFSET :offset",
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

    public static function upload(array $file, int $shopId, int $userId): ?int
    {
        $valid = Security::validateFile($file, self::$allowedTypes);
        if (!$valid['valid']) {
            return null;
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $safeName = Security::sanitizeFilename(pathinfo($file['name'], PATHINFO_FILENAME));
        $storedName = $safeName . '_' . bin2hex(random_bytes(8)) . '.' . $ext;

        $uploadDir = APP_ROOT . '/storage/uploads/' . $shopId;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $destPath = $uploadDir . '/' . $storedName;
        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            return null;
        }

        $data = [
            'shop_id' => $shopId,
            'user_id' => $userId,
            'file_name' => $file['name'],
            'file_path' => 'storage/uploads/' . $shopId . '/' . $storedName,
            'file_type' => $ext,
            'size' => $file['size'],
        ];

        return self::create($data);
    }

    public static function deleteFile(int $id): bool
    {
        $file = self::find($id);
        if (!$file) return false;

        $fullPath = APP_ROOT . '/' . $file['file_path'];
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }

        return (bool) self::delete($id);
    }

    public static function getAllowedTypes(): array
    {
        return self::$allowedTypes;
    }
}
