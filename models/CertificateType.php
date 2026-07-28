<?php

namespace App\Models;

use App\Core\Model;

class CertificateType extends Model
{
    protected static string $table = 'certificate_types';
    protected static bool $tenantAware = false;
    protected static array $fillable = [
        'slug', 'name_bn', 'name_en', 'category', 'fee', 'status',
    ];

    public static function findActive(): array
    {
        return static::findAll(['status' => 'active'], 'category ASC, name_bn ASC');
    }

    public static function findByCategory(string $category): array
    {
        return static::findAll(['category' => $category, 'status' => 'active'], 'name_bn ASC');
    }

    public static function findBySlug(string $slug): ?array
    {
        return static::findWhere('slug', $slug);
    }

    public static function getCategories(): array
    {
        return [
            'vital'      => 'জীবন ও মৃত্যু',
            'general'    => 'সাধারণ',
            'financial'  => 'আর্থিক',
            'legal'      => 'আইনগত',
            'land'       => 'জমি সংক্রান্ত',
            'education'  => 'শিক্ষা',
            'health'     => 'স্বাস্থ্য',
            'agriculture'=> 'কৃষি',
        ];
    }

    public static function getFields(int $typeId): array
    {
        return CertificateField::findAll(['certificate_type_id' => $typeId], 'position ASC');
    }
}
