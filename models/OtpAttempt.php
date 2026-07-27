<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class OtpAttempt extends Model
{
    protected static string $table = 'otp_attempts';
    protected static string $primaryKey = 'id';
    protected static bool $tenantAware = false;
    protected static array $fillable = [
        'phone',
        'email',
        'ip_address',
        'attempts',
        'locked_until',
        'last_attempt_at',
    ];

    public static function findByIdentifier(string $identifier): ?array
    {
        $db = Database::getInstance();
        $field = self::getIdentifierField($identifier);
        return $db->fetch(
            "SELECT * FROM " . self::$table . " WHERE {$field} = :identifier ORDER BY last_attempt_at DESC LIMIT 1",
            ['identifier' => $identifier]
        );
    }

    public static function record(string $identifier, string $ip): void
    {
        $db = Database::getInstance();
        $field = self::getIdentifierField($identifier);
        $existing = $db->fetch(
            "SELECT * FROM " . self::$table . " WHERE {$field} = :identifier ORDER BY last_attempt_at DESC LIMIT 1",
            ['identifier' => $identifier]
        );

        if ($existing) {
            $maxAttempts = (int) env('OTP_MAX_ATTEMPTS', 3);
            $newAttempts = $existing['attempts'] + 1;
            $lockoutMinutes = (int) env('OTP_LOCKOUT_MINUTES', 15);

            $updateData = [
                'attempts' => $newAttempts,
                'last_attempt_at' => date('Y-m-d H:i:s'),
            ];

            if ($newAttempts >= $maxAttempts) {
                $updateData['locked_until'] = date('Y-m-d H:i:s', time() + ($lockoutMinutes * 60));
            }

            $db->update(self::$table, $updateData, 'id = :id', ['id' => $existing['id']]);
        } else {
            $data = [
                'ip_address' => $ip,
                'attempts' => 1,
                'last_attempt_at' => date('Y-m-d H:i:s'),
            ];
            $data[$field] = $identifier;
            $db->insert(self::$table, $data);
        }
    }

    public static function lock(string $identifier, int $minutes): void
    {
        $db = Database::getInstance();
        $field = self::getIdentifierField($identifier);
        $existing = $db->fetch(
            "SELECT id FROM " . self::$table . " WHERE {$field} = :identifier ORDER BY last_attempt_at DESC LIMIT 1",
            ['identifier' => $identifier]
        );

        if ($existing) {
            $db->update(
                self::$table,
                ['locked_until' => date('Y-m-d H:i:s', time() + ($minutes * 60))],
                'id = :id',
                ['id' => $existing['id']]
            );
        }
    }

    public static function clearByIdentifier(string $identifier): void
    {
        $db = Database::getInstance();
        $field = self::getIdentifierField($identifier);
        $db->delete(self::$table, "{$field} = :identifier", ['identifier' => $identifier]);
    }

    private static function getIdentifierField(string $identifier): string
    {
        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            return 'email';
        }
        return 'phone';
    }
}
