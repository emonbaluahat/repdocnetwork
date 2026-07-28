<?php

namespace App\Models;

use App\Core\Model;

class CertificateField extends Model
{
    protected static string $table = 'certificate_fields';
    protected static bool $tenantAware = false;
    protected static array $fillable = [
        'certificate_type_id', 'field_name', 'label_bn',
        'field_type', 'options', 'required', 'position',
    ];

    public static function getByType(int $typeId): array
    {
        return static::findAll(['certificate_type_id' => $typeId], 'position ASC');
    }
}
