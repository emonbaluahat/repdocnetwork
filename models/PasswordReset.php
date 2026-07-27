<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class PasswordReset extends Model
{
    protected static string $table = 'password_resets';
    protected static string $primaryKey = 'id';
    protected static bool $tenantAware = false;
    protected static array $fillable = [
        'user_id',
        'token',
        'type',
        'expires_at',
        'used_at',
    ];

    public static function createToken(int $userId, string $type = 'email'): array
    {
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', time() + 3600); // 1 hour

        $id = self::create([
            'user_id' => $userId,
            'token' => hash('sha256', $token),
            'type' => $type,
            'expires_at' => $expiresAt,
        ]);

        return [
            'id' => $id,
            'raw_token' => $token,
            'expires_at' => $expiresAt,
        ];
    }

    public static function findValid(int $userId, string $token): ?array
    {
        $db = Database::getInstance();
        $hashedToken = hash('sha256', $token);
        return $db->fetch(
            "SELECT * FROM " . self::$table . "
             WHERE user_id = :user_id
             AND token = :token
             AND used_at IS NULL
             AND expires_at > NOW()
             ORDER BY id DESC LIMIT 1",
            ['user_id' => $userId, 'token' => $hashedToken]
        );
    }

    public static function markUsed(int $id): void
    {
        $db = Database::getInstance();
        $db->update(
            self::$table,
            ['used_at' => date('Y-m-d H:i:s')],
            'id = :id',
            ['id' => $id]
        );
    }

    public static function expireOldTokens(int $userId): void
    {
        $db = Database::getInstance();
        $db->query(
            "UPDATE " . self::$table . " SET used_at = NOW()
             WHERE user_id = :user_id AND used_at IS NULL",
            ['user_id' => $userId]
        );
    }

    public static function cleanupExpired(): int
    {
        $db = Database::getInstance();
        $stmt = $db->query(
            "DELETE FROM " . self::$table . " WHERE expires_at < DATE_SUB(NOW(), INTERVAL 7 DAY)"
        );
        return $stmt->rowCount();
    }
}
