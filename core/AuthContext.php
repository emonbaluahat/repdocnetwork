<?php

namespace App\Core;

class AuthContext
{
    public static function user(): ?array
    {
        return Session::get('user');
    }

    public static function id(): ?int
    {
        $user = self::user();
        return $user ? (int) $user['id'] : null;
    }

    public static function check(): bool
    {
        return Session::has('user') && Session::get('user') !== null;
    }

    public static function guest(): bool
    {
        return !self::check();
    }

    public static function login(array $user): void
    {
        Session::set('user', $user);
        Session::regenerate();
    }

    public static function logout(): void
    {
        Session::destroy();
    }

    public static function shop(): ?array
    {
        return Session::get('active_shop');
    }

    public static function shopId(): ?int
    {
        $shop = self::shop();
        return $shop ? (int) $shop['id'] : null;
    }

    public static function hasShop(): bool
    {
        return Session::has('active_shop') && Session::get('active_shop') !== null;
    }

    public static function setShop(array $shop): void
    {
        Session::set('active_shop', $shop);
        Session::set('active_role', $shop['role'] ?? null);
    }

    public static function clearShop(): void
    {
        Session::remove('active_shop');
        Session::remove('active_role');
    }

    public static function role(): ?string
    {
        return Session::get('active_role');
    }

    public static function hasRole(string $role): bool
    {
        return self::role() === $role;
    }

    public static function isOwner(): bool
    {
        return self::hasRole(ROLE_OWNER);
    }

    public static function isAdmin(): bool
    {
        return in_array(self::role(), [ROLE_OWNER, ROLE_ADMIN]);
    }

    public static function isOperator(): bool
    {
        return in_array(self::role(), [ROLE_OWNER, ROLE_ADMIN, ROLE_OPERATOR]);
    }

    public static function isSuperAdmin(): bool
    {
        $user = self::user();
        return $user && !empty($user['is_super_admin']);
    }

    public static function isStaffManager(): bool
    {
        return in_array(self::role(), [ROLE_OWNER, ROLE_ADMIN]);
    }

    public static function hasPermission(string $permission): bool
    {
        if (self::isSuperAdmin()) {
            return true;
        }

        $role = self::role();
        if (!$role) return false;

        $permissions = $GLOBALS['role_permissions'][$role] ?? [];
        return in_array($permission, $permissions);
    }

    public static function shops(): array
    {
        return Session::get('user_shops', []);
    }

    public static function setShops(array $shops): void
    {
        Session::set('user_shops', $shops);
    }

    public static function canImpersonate(): bool
    {
        return Session::has('impersonating');
    }
}
