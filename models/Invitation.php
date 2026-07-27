<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use App\Core\Security;

class Invitation extends Model
{
    protected static string $table = 'invitations';
    protected static string $primaryKey = 'id';
    protected static bool $tenantAware = false;
    protected static array $fillable = [
        'shop_id',
        'invited_by',
        'email',
        'phone',
        'role',
        'token',
        'status',
        'expires_at',
        'accepted_at',
    ];

    public static function createInvitation(int $shopId, int $invitedBy, string $role, ?string $email = null, ?string $phone = null): array
    {
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', time() + 86400 * 7); // 7 days

        $id = self::create([
            'shop_id' => $shopId,
            'invited_by' => $invitedBy,
            'email' => $email,
            'phone' => $phone,
            'role' => $role,
            'token' => hash('sha256', $token),
            'status' => 'pending',
            'expires_at' => $expiresAt,
        ]);

        return [
            'id' => $id,
            'raw_token' => $token,
            'expires_at' => $expiresAt,
        ];
    }

    public static function findByToken(string $token): ?array
    {
        $db = Database::getInstance();
        return $db->fetch(
            "SELECT i.*, s.name as shop_name, s.slug as shop_slug
             FROM " . self::$table . " i
             JOIN shops s ON i.shop_id = s.id
             WHERE i.token = :token AND i.status = 'pending' AND i.expires_at > NOW()
             LIMIT 1",
            ['token' => hash('sha256', $token)]
        );
    }

    public static function findPendingByEmail(string $email, int $shopId): array
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT * FROM " . self::$table . "
             WHERE email = :email AND shop_id = :shop_id AND status = 'pending' AND expires_at > NOW()
             ORDER BY created_at DESC",
            ['email' => $email, 'shop_id' => $shopId]
        );
    }

    public static function findPendingByPhone(string $phone, int $shopId): array
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT * FROM " . self::$table . "
             WHERE phone = :phone AND shop_id = :shop_id AND status = 'pending' AND expires_at > NOW()
             ORDER BY created_at DESC",
            ['phone' => $phone, 'shop_id' => $shopId]
        );
    }

    public static function accept(int $id): void
    {
        $db = Database::getInstance();
        $db->update(
            self::$table,
            ['status' => 'accepted', 'accepted_at' => date('Y-m-d H:i:s')],
            'id = :id',
            ['id' => $id]
        );
    }

    public static function decline(int $id): void
    {
        $db = Database::getInstance();
        $db->update(
            self::$table,
            ['status' => 'declined'],
            'id = :id',
            ['id' => $id]
        );
    }

    public static function getByShop(int $shopId, string $status = ''): array
    {
        $db = Database::getInstance();
        $sql = "SELECT i.*, u.name as invited_by_name
                FROM " . self::$table . " i
                LEFT JOIN users u ON i.invited_by = u.id
                WHERE i.shop_id = :shop_id";
        $params = ['shop_id' => $shopId];

        if ($status) {
            $sql .= " AND i.status = :status";
            $params['status'] = $status;
        }

        $sql .= " ORDER BY i.created_at DESC";

        return $db->fetchAll($sql, $params);
    }

    public static function expireOld(): int
    {
        $db = Database::getInstance();
        $stmt = $db->query(
            "UPDATE " . self::$table . " SET status = 'expired'
             WHERE status = 'pending' AND expires_at < NOW()"
        );
        return $stmt->rowCount();
    }
}
