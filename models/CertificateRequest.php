<?php

namespace App\Models;

use App\Core\Model;
use App\Core\AuthContext;

class CertificateRequest extends Model
{
    protected static string $table = 'certificate_requests';
    protected static bool $tenantAware = true;
    protected static array $fillable = [
        'shop_id', 'certificate_type_id', 'customer_id',
        'data', 'status', 'created_by',
    ];

    public static function getPending(): array
    {
        return static::findAll(['status' => 'submitted'], 'created_at DESC');
    }

    public static function getByCustomer(int $customerId): array
    {
        return static::findAll(['customer_id' => $customerId], 'created_at DESC');
    }

    public static function submit(int $id): bool
    {
        return static::update($id, ['status' => 'submitted']) > 0;
    }

    public static function complete(int $id): bool
    {
        return static::update($id, ['status' => 'completed']) > 0;
    }

    public static function cancel(int $id): bool
    {
        return static::update($id, ['status' => 'cancelled']) > 0;
    }
}
