<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;
use App\Core\AuthContext;

class Order extends Model
{
    protected static string $table = 'orders';
    protected static string $primaryKey = 'id';
    protected static bool $tenantAware = true;
    protected static string $tenantColumn = 'shop_id';
    protected static array $fillable = [
        'shop_id',
        'customer_id',
        'reference',
        'status',
        'priority',
        'amount',
        'paid_amount',
        'due_amount',
        'discount_amount',
        'discount_type',
        'tax_amount',
        'notes',
        'internal_notes',
        'estimated_ready_at',
        'completed_at',
        'delivered_at',
        'created_by',
        'updated_by',
    ];

    public static function search(int $shopId, string $search = '', string $status = '', string $dateFrom = '', string $dateTo = '', int $page = 1, int $perPage = 25): array
    {
        $db = Database::getInstance();
        $where = "o.shop_id = :shop_id";
        $params = ['shop_id' => $shopId];

        if ($search) {
            $like = "%{$search}%";
            $where .= " AND (o.reference LIKE :search OR c.name LIKE :search2 OR c.phone LIKE :search3)";
            $params['search'] = $like;
            $params['search2'] = $like;
            $params['search3'] = $like;
        }

        if ($status && in_array($status, ['pending', 'confirmed', 'in_progress', 'ready', 'completed', 'cancelled', 'delivered'])) {
            $where .= " AND o.status = :status";
            $params['status'] = $status;
        }

        if ($dateFrom) {
            $where .= " AND o.created_at >= :date_from";
            $params['date_from'] = $dateFrom . ' 00:00:00';
        }

        if ($dateTo) {
            $where .= " AND o.created_at <= :date_to";
            $params['date_to'] = $dateTo . ' 23:59:59';
        }

        $countResult = $db->fetch(
            "SELECT COUNT(*) as count FROM orders o LEFT JOIN customers c ON o.customer_id = c.id WHERE {$where}",
            $params
        );
        $total = (int) ($countResult['count'] ?? 0);
        $totalPages = max(1, (int) ceil($total / $perPage));
        $page = max(1, min($page, $totalPages));
        $offset = ($page - 1) * $perPage;

        $items = $db->fetchAll(
            "SELECT o.*, c.name as customer_name, c.phone as customer_phone
             FROM orders o
             LEFT JOIN customers c ON o.customer_id = c.id
             WHERE {$where}
             ORDER BY o.created_at DESC
             LIMIT :limit OFFSET :offset",
            array_merge($params, ['limit' => $perPage, 'offset' => $offset])
        );

        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => $totalPages,
            'has_prev' => $page > 1,
            'has_next' => $page < $totalPages,
        ];
    }

    public static function getItems(int $orderId): array
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT * FROM order_items WHERE order_id = :order_id",
            ['order_id' => $orderId]
        );
    }

    public static function getTimeline(int $orderId): array
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT ot.*, u.name as user_name
             FROM order_timeline ot
             LEFT JOIN users u ON ot.user_id = u.id
             WHERE ot.order_id = :order_id
             ORDER BY ot.created_at DESC",
            ['order_id' => $orderId]
        );
    }

    public static function getTransactions(int $orderId): array
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT * FROM transactions WHERE order_id = :order_id ORDER BY created_at DESC",
            ['order_id' => $orderId]
        );
    }

    public static function addTimeline(int $orderId, int $shopId, string $action, ?string $description = null, ?array $metadata = null): void
    {
        $db = Database::getInstance();
        $db->insert('order_timeline', [
            'order_id' => $orderId,
            'shop_id' => $shopId,
            'user_id' => AuthContext::id(),
            'action' => $action,
            'description' => $description,
            'metadata' => $metadata ? json_encode($metadata, JSON_UNESCAPED_UNICODE) : null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public static function recalculateDue(int $orderId): void
    {
        $db = Database::getInstance();
        $order = $db->fetch("SELECT amount, paid_amount FROM orders WHERE id = :id", ['id' => $orderId]);
        if (!$order) return;

        $due = max(0, (float) $order['amount'] - (float) $order['paid_amount']);
        $db->update('orders', ['due_amount' => $due], 'id = :id', ['id' => $orderId]);
    }

    public static function getTodayStats(int $shopId): array
    {
        $db = Database::getInstance();
        $todayStart = date('Y-m-d 00:00:00');
        $todayEnd = date('Y-m-d 23:59:59');

        $result = $db->fetch(
            "SELECT COUNT(*) as count, COALESCE(SUM(paid_amount), 0) as revenue
             FROM orders
             WHERE shop_id = :shop_id AND created_at BETWEEN :start AND :end AND status != 'cancelled'",
            ['shop_id' => $shopId, 'start' => $todayStart, 'end' => $todayEnd]
        );

        return [
            'count' => (int) ($result['count'] ?? 0),
            'revenue' => (float) ($result['revenue'] ?? 0),
        ];
    }

    public static function getMonthlyStats(int $shopId): array
    {
        $db = Database::getInstance();
        $monthStart = date('Y-m-01 00:00:00');
        $monthEnd = date('Y-m-t 23:59:59');

        $result = $db->fetch(
            "SELECT COUNT(*) as count, COALESCE(SUM(paid_amount), 0) as revenue
             FROM orders
             WHERE shop_id = :shop_id AND created_at BETWEEN :start AND :end AND status != 'cancelled'",
            ['shop_id' => $shopId, 'start' => $monthStart, 'end' => $monthEnd]
        );

        return [
            'count' => (int) ($result['count'] ?? 0),
            'revenue' => (float) ($result['revenue'] ?? 0),
        ];
    }
}