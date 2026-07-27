<?php

namespace App\Core;

class TenantManager
{
    private static ?array $resolvedShop = null;

    public static function resolveFromSubdomain(): ?int
    {
        $host = $_SERVER['HTTP_HOST'] ?? '';
        if (preg_match('/^([a-z0-9-]+)\.' . preg_quote(parse_url(APP_URL, PHP_URL_HOST) ?? 'localhost', '/') . '$/i', $host, $matches)) {
            $slug = $matches[1];
            $db = Database::getInstance();
            $shop = $db->fetch(
                "SELECT id, name, slug, domain, status, settings, logo, address, phone, email, business_hours, owner_id, verified_at, created_at, updated_at FROM shops WHERE slug = :slug AND status = 'active' LIMIT 1",
                ['slug' => $slug]
            );
            if ($shop) {
                self::$resolvedShop = $shop;
                return (int) $shop['id'];
            }
        }
        return null;
    }

    public static function resolveFromSession(): ?int
    {
        if (AuthContext::hasShop()) {
            $shop = AuthContext::shop();
            self::$resolvedShop = $shop;
            return $shop ? (int) $shop['id'] : null;
        }
        return null;
    }

    public static function current(): ?array
    {
        if (self::$resolvedShop !== null) {
            return self::$resolvedShop;
        }

        $shopId = self::resolveFromSubdomain() ?? self::resolveFromSession();
        if ($shopId && !self::$resolvedShop) {
            $db = Database::getInstance();
            self::$resolvedShop = $db->fetch("SELECT * FROM shops WHERE id = :id", ['id' => $shopId]);
        }

        return self::$resolvedShop;
    }

    public static function id(): ?int
    {
        $shop = self::current();
        return $shop ? (int) $shop['id'] : null;
    }

    public static function isShopActive(?int $shopId = null): bool
    {
        $shopId = $shopId ?? self::id();
        if (!$shopId) return false;

        $db = Database::getInstance();
        $shop = $db->fetch("SELECT status FROM shops WHERE id = :id", ['id' => $shopId]);
        return $shop && $shop['status'] === 'active';
    }

    public static function scopeQuery(string $sql, string $alias = ''): string
    {
        $shopId = self::id();
        if (!$shopId) {
            return $sql;
        }

        $tableAlias = $alias ? "{$alias}." : "";
        $hasWhere = stripos(trim($sql), 'WHERE') !== false;

        if ($hasWhere) {
            return $sql . " AND {$tableAlias}shop_id = " . (int) $shopId;
        }

        return $sql . " WHERE {$tableAlias}shop_id = " . (int) $shopId;
    }

    public static function availableShops(int $userId): array
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT s.*, su.role
             FROM shops s
             JOIN shop_user su ON s.id = su.shop_id
             WHERE su.user_id = :user_id AND su.is_active = 1
             ORDER BY s.name",
            ['user_id' => $userId]
        );
    }

    public static function switchShop(int $shopId, int $userId): bool
    {
        $db = Database::getInstance();
        $shop = $db->fetch(
            "SELECT s.*, su.role
             FROM shops s
             JOIN shop_user su ON s.id = su.shop_id
             WHERE s.id = :shop_id AND su.user_id = :user_id AND su.is_active = 1",
            ['shop_id' => $shopId, 'user_id' => $userId]
        );

        if ($shop) {
            AuthContext::setShop($shop);
            self::$resolvedShop = $shop;
            return true;
        }

        return false;
    }
}
