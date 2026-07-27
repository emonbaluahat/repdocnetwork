<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use App\Core\Request;

class SessionModel extends Model
{
    protected static string $table = 'sessions';
    protected static string $primaryKey = 'id';
    protected static bool $tenantAware = false;
    protected static array $fillable = [
        'id',
        'user_id',
        'shop_id',
        'ip_address',
        'user_agent',
        'payload',
        'last_activity',
    ];

    public static function createSession(int $userId, ?int $shopId = null): string
    {
        return self::create([
            'id' => session_id() ?: bin2hex(random_bytes(32)),
            'user_id' => $userId,
            'shop_id' => $shopId,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'payload' => json_encode($_SESSION ?? []),
            'last_activity' => date('Y-m-d H:i:s'),
        ]);
    }

    public static function updateActivity(string $sessionId): void
    {
        $db = Database::getInstance();
        $db->update(
            self::$table,
            [
                'last_activity' => date('Y-m-d H:i:s'),
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
            ],
            'id = :id',
            ['id' => $sessionId]
        );
    }

    public static function getUserActiveSessions(int $userId): array
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT * FROM sessions
             WHERE user_id = :user_id
             AND last_activity > DATE_SUB(NOW(), INTERVAL 24 HOUR)
             ORDER BY last_activity DESC",
            ['user_id' => $userId]
        );
    }

    public static function terminateSession(string $sessionId): void
    {
        $db = Database::getInstance();
        $db->delete(self::$table, 'id = :id', ['id' => $sessionId]);
    }

    public static function terminateAllUserSessions(int $userId, ?string $exceptSessionId = null): int
    {
        $db = Database::getInstance();
        if ($exceptSessionId) {
            return $db->query(
                "DELETE FROM sessions WHERE user_id = :user_id AND id != :except_id",
                ['user_id' => $userId, 'except_id' => $exceptSessionId]
            )->rowCount();
        }
        return $db->delete(self::$table, 'user_id = :user_id', ['user_id' => $userId]);
    }

    public static function cleanupExpired(): int
    {
        $db = Database::getInstance();
        return $db->query(
            "DELETE FROM sessions WHERE last_activity < DATE_SUB(NOW(), INTERVAL :lifetime MINUTE)",
            ['lifetime' => SESSION_LIFETIME]
        )->rowCount();
    }
}
