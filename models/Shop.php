<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use App\Core\Security;

class Shop extends Model
{
    protected static string $table = 'shops';
    protected static string $primaryKey = 'id';
    protected static bool $tenantAware = false;
    protected static array $fillable = [
        'name',
        'slug',
        'domain',
        'status',
        'settings',
        'subscription',
        'expires_at',
        'logo',
        'address',
        'phone',
        'email',
        'business_hours',
        'owner_id',
        'created_by',
        'verified_at',
    ];

    public static function findBySlug(string $slug): ?array
    {
        $db = Database::getInstance();
        return $db->fetch(
            "SELECT * FROM shops WHERE slug = :slug LIMIT 1",
            ['slug' => $slug]
        );
    }

    public static function findByDomain(string $domain): ?array
    {
        $db = Database::getInstance();
        return $db->fetch(
            "SELECT * FROM shops WHERE domain = :domain LIMIT 1",
            ['domain' => $domain]
        );
    }

    public static function createWithOwner(array $shopData, int $ownerId): int
    {
        $db = Database::getInstance();

        $slug = $shopData['slug'] ?? slugify($shopData['name']);

        $originalSlug = $slug;
        $counter = 1;
        while (self::findBySlug($slug)) {
            $slug = $originalSlug . '-' . $counter++;
        }

        $shopId = $db->insert('shops', [
            'name' => $shopData['name'],
            'slug' => $slug,
            'status' => $shopData['status'] ?? 'active',
            'settings' => $shopData['settings'] ?? '{}',
            'owner_id' => $ownerId,
            'created_by' => $ownerId,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $db->insert('shop_user', [
            'shop_id' => $shopId,
            'user_id' => $ownerId,
            'role' => 'owner',
            'joined_at' => date('Y-m-d H:i:s'),
            'invited_by' => $ownerId,
            'is_active' => 1,
        ]);

        return $shopId;
    }

    public static function operators(int $shopId): array
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT u.*, su.role, su.joined_at
             FROM users u
             JOIN shop_user su ON u.id = su.user_id
             WHERE su.shop_id = :shop_id AND su.is_active = 1
             ORDER BY su.joined_at ASC",
            ['shop_id' => $shopId]
        );
    }

    public static function addUser(int $shopId, int $userId, string $role, int $invitedBy): void
    {
        $db = Database::getInstance();
        $db->insert('shop_user', [
            'shop_id' => $shopId,
            'user_id' => $userId,
            'role' => $role,
            'joined_at' => date('Y-m-d H:i:s'),
            'invited_by' => $invitedBy,
            'is_active' => 1,
        ]);
    }

    public static function removeUser(int $shopId, int $userId): void
    {
        $db = Database::getInstance();
        $db->update('shop_user', ['is_active' => 0], 'shop_id = :shop_id AND user_id = :user_id', [
            'shop_id' => $shopId,
            'user_id' => $userId,
        ]);
    }

    public static function updateSettings(int $shopId, array $settings): void
    {
        $db = Database::getInstance();
        $db->update('shops', ['settings' => json_encode($settings, JSON_UNESCAPED_UNICODE)], 'id = :id', ['id' => $shopId]);
    }

    public static function getSettings(int $shopId): array
    {
        $db = Database::getInstance();
        $shop = $db->fetch("SELECT settings FROM shops WHERE id = :id", ['id' => $shopId]);
        if (!$shop || !$shop['settings']) {
            return [];
        }
        $decoded = json_decode($shop['settings'], true);
        return is_array($decoded) ? $decoded : [];
    }
}
