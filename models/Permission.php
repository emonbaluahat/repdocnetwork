<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Permission extends Model
{
    protected static string $table = 'permissions';
    protected static string $primaryKey = 'id';
    protected static bool $tenantAware = false;
    protected static array $fillable = [
        'name',
        'slug',
        'module',
        'description',
    ];

    public static function getByRole(string $role): array
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT p.*
             FROM permissions p
             JOIN role_permission rp ON p.id = rp.permission_id
             WHERE rp.role = :role
             ORDER BY p.module, p.name",
            ['role' => $role]
        );
    }

    public static function getByModule(string $module): array
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT * FROM permissions WHERE module = :module ORDER BY name",
            ['module' => $module]
        );
    }

    public static function getAllGrouped(): array
    {
        $db = Database::getInstance();
        $permissions = $db->fetchAll("SELECT * FROM permissions ORDER BY module, name");

        $grouped = [];
        foreach ($permissions as $perm) {
            $grouped[$perm['module']][] = $perm;
        }
        return $grouped;
    }

    public static function setRolePermissions(string $role, array $permissionIds): void
    {
        $db = Database::getInstance();

        $db->beginTransaction();
        try {
            $db->delete('role_permission', 'role = :role', ['role' => $role]);
            foreach ($permissionIds as $permId) {
                $db->insert('role_permission', [
                    'role' => $role,
                    'permission_id' => (int) $permId,
                ]);
            }
            $db->commit();
        } catch (\Exception $e) {
            $db->rollback();
            throw $e;
        }
    }

    public static function userHasPermission(int $userId, string $permissionSlug): bool
    {
        $db = Database::getInstance();
        $result = $db->fetch(
            "SELECT 1
             FROM shop_user su
             JOIN role_permission rp ON su.role = rp.role
             JOIN permissions p ON rp.permission_id = p.id
             WHERE su.user_id = :user_id
             AND p.slug = :slug
             AND su.is_active = 1
             LIMIT 1",
            ['user_id' => $userId, 'slug' => $permissionSlug]
        );
        return $result !== null;
    }

    public static function userHasPermissionInShop(int $userId, int $shopId, string $permissionSlug): bool
    {
        $db = Database::getInstance();
        $result = $db->fetch(
            "SELECT 1
             FROM shop_user su
             JOIN role_permission rp ON su.role = rp.role
             JOIN permissions p ON rp.permission_id = p.id
             WHERE su.user_id = :user_id
             AND su.shop_id = :shop_id
             AND su.is_active = 1
             AND p.slug = :slug
             LIMIT 1",
            ['user_id' => $userId, 'shop_id' => $shopId, 'slug' => $permissionSlug]
        );
        return $result !== null;
    }

    public static function loadPermissionsToGlobal(): void
    {
        $db = Database::getInstance();
        $rows = $db->fetchAll(
            "SELECT rp.role, p.slug
             FROM role_permission rp
             JOIN permissions p ON rp.permission_id = p.id"
        );

        $permissions = [];
        foreach ($rows as $row) {
            $permissions[$row['role']][] = $row['slug'];
        }

        foreach ($permissions as $role => $perms) {
            $GLOBALS['role_permissions'][$role] = $perms;
        }
    }
}
