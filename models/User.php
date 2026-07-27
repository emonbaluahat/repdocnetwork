<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class User extends Model
{
    protected static string $table = 'users';
    protected static string $primaryKey = 'id';
    protected static bool $tenantAware = false;
    protected static array $fillable = [
        'name',
        'username',
        'email',
        'phone',
        'password',
        'avatar',
        'preferences',
        'language',
        'theme',
        'email_verified_at',
        'phone_verified_at',
        'last_login_at',
        'status',
    ];

    public static function findByEmail(string $email): ?array
    {
        $db = Database::getInstance();
        return $db->fetch(
            "SELECT * FROM users WHERE email = :email LIMIT 1",
            ['email' => $email]
        );
    }

    public static function findByPhone(string $phone): ?array
    {
        $db = Database::getInstance();
        return $db->fetch(
            "SELECT * FROM users WHERE phone = :phone LIMIT 1",
            ['phone' => $phone]
        );
    }

    public static function findByUsername(string $username): ?array
    {
        $db = Database::getInstance();
        return $db->fetch(
            "SELECT * FROM users WHERE username = :username LIMIT 1",
            ['username' => $username]
        );
    }

    public static function shops(int $userId): array
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT s.*, su.role, su.joined_at
             FROM shops s
             JOIN shop_user su ON s.id = su.shop_id
             WHERE su.user_id = :user_id AND su.is_active = 1
             ORDER BY s.name",
            ['user_id' => $userId]
        );
    }

    public static function hasAccessToShop(int $userId, int $shopId): bool
    {
        $db = Database::getInstance();
        $result = $db->fetch(
            "SELECT id FROM shop_user WHERE user_id = :user_id AND shop_id = :shop_id AND is_active = 1 LIMIT 1",
            ['user_id' => $userId, 'shop_id' => $shopId]
        );
        return $result !== null;
    }
}
