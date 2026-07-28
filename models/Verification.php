<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Verification extends Model
{
    protected static string $table = 'verifications';
    protected static bool $tenantAware = false;
    protected static array $fillable = [
        'document_id', 'verification_code', 'qr_data', 'status',
    ];

    public static function generateCode(): string
    {
        $year = date('Y');
        $rand = strtoupper(bin2hex(random_bytes(5)));
        return $year . $rand;
    }

    public static function findByCode(string $code): ?array
    {
        return static::findWhere('verification_code', $code);
    }

    public static function createForDocument(int $documentId): array
    {
        $code = static::generateCode();
        $id = static::create([
            'document_id' => $documentId,
            'verification_code' => $code,
            'status' => 'active',
        ]);
        return static::find($id);
    }

    public static function verifyByCode(string $code): ?array
    {
        $record = static::findByCode($code);
        if (!$record || $record['status'] !== 'active') {
            return null;
        }

        $db = Database::getInstance();
        $sql = "SELECT d.*, dt.name as template_name, dt.category as template_category,
                       c.name as customer_name, c.phone as customer_phone, c.nid as customer_nid
                FROM documents d
                LEFT JOIN document_templates dt ON d.template_id = dt.id
                LEFT JOIN customers c ON d.customer_id = c.id
                WHERE d.id = :document_id";
        $document = $db->fetch($sql, ['document_id' => $record['document_id']]);

        if (!$document) {
            return null;
        }

        return [
            'verification' => $record,
            'document' => $document,
        ];
    }
}
