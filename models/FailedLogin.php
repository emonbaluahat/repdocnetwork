<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use App\Core\Request;

class FailedLogin extends Model
{
    protected static string $table = 'failed_logins';
    protected static string $primaryKey = 'id';
    protected static bool $tenantAware = false;
    protected static array $fillable = [
        'user_id',
        'ip_address',
        'key_identifier',
        'attempted_at',
    ];

    public static function record(string $key): void
    {
        $db = Database::getInstance();
        $db->insert(self::$table, [
            'key_identifier' => $key,
            'ip_address' => Request::ip(),
            'attempted_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Count recent failed attempts for a given key within decay minutes
     */
    public static function countRecent(string $key, int $decayMinutes = 15): int
    {
        $db = Database::getInstance();
        $cutoff = date('Y-m-d H:i:s', time() - ($decayMinutes * 60));
        $result = $db->fetch(
            "SELECT COUNT(*) as count FROM " . self::$table . "
             WHERE key_identifier = :key AND attempted_at > :cutoff",
            ['key' => $key, 'cutoff' => $cutoff]
        );
        return (int) ($result['count'] ?? 0);
    }

    /**
     * Get the most recent failed attempt for a key
     */
    public static function getMostRecent(string $key): ?array
    {
        $db = Database::getInstance();
        return $db->fetch(
            "SELECT * FROM " . self::$table . "
             WHERE key_identifier = :key
             ORDER BY attempted_at DESC LIMIT 1",
            ['key' => $key]
        );
    }

    /**
     * Clear failed attempts for a key (on successful login)
     */
    public static function clearByKey(string $key): void
    {
        $db = Database::getInstance();
        $db->delete(self::$table, 'key_identifier = :key', ['key' => $key]);
    }

    /**
     * Clear old failed attempts (cleanup)
     */
    public static function clearOld(int $hours = 24): int
    {
        $db = Database::getInstance();
        $cutoff = date('Y-m-d H:i:s', time() - ($hours * 3600));
        $stmt = $db->query(
            "DELETE FROM " . self::$table . " WHERE attempted_at < :cutoff",
            ['cutoff' => $cutoff]
        );
        return $stmt->rowCount();
    }
}
