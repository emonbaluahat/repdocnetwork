<?php

namespace App\Models;

use App\Core\Model;
use App\Core\Database;

class Transaction extends Model
{
    protected static string $table = 'transactions';
    protected static string $primaryKey = 'id';
    protected static bool $tenantAware = true;
    protected static string $tenantColumn = 'shop_id';
    protected static array $fillable = [
        'shop_id',
        'order_id',
        'customer_id',
        'reference',
        'type',
        'method',
        'amount',
        'status',
        'notes',
        'processed_by',
    ];

    const TYPE_PAYMENT = 'payment';
    const TYPE_REFUND = 'refund';
    const TYPE_ADJUSTMENT = 'adjustment';

    const METHOD_CASH = 'cash';
    const METHOD_BKASH = 'bkash';
    const METHOD_NAGAD = 'nagad';
    const METHOD_ROCKET = 'rocket';
    const METHOD_BANK = 'bank';
    const METHOD_CARD = 'card';
    const METHOD_OTHER = 'other';

    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_REFUNDED = 'refunded';

    public static function generateReference(): string
    {
        $db = Database::getInstance();
        $prefix = 'TXN-' . date('Ymd') . '-';
        $suffix = strtoupper(bin2hex(random_bytes(3)));
        return $prefix . $suffix;
    }

    public static function getByShop(int $shopId, array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $db = Database::getInstance();
        $where = 'WHERE t.shop_id = :shop_id';
        $params = ['shop_id' => $shopId];

        if (!empty($filters['type'])) {
            $where .= ' AND t.type = :type';
            $params['type'] = $filters['type'];
        }
        if (!empty($filters['method'])) {
            $where .= ' AND t.method = :method';
            $params['method'] = $filters['method'];
        }
        if (!empty($filters['search'])) {
            $where .= ' AND (t.reference LIKE :search OR o.reference LIKE :search2 OR c.name LIKE :search3)';
            $params['search'] = '%' . $filters['search'] . '%';
            $params['search2'] = '%' . $filters['search'] . '%';
            $params['search3'] = '%' . $filters['search'] . '%';
        }
        if (!empty($filters['date_from'])) {
            $where .= ' AND DATE(t.created_at) >= :date_from';
            $params['date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where .= ' AND DATE(t.created_at) <= :date_to';
            $params['date_to'] = $filters['date_to'];
        }

        $count = $db->fetch(
            "SELECT COUNT(*) as total FROM transactions t LEFT JOIN orders o ON t.order_id = o.id LEFT JOIN customers c ON t.customer_id = c.id {$where}",
            $params
        );
        $total = (int) ($count['total'] ?? 0);
        $offset = ($page - 1) * $perPage;

        $rows = $db->fetchAll(
            "SELECT t.*, o.reference as order_reference, c.name as customer_name, c.phone as customer_phone,
                    u.name as processor_name
             FROM transactions t
             LEFT JOIN orders o ON t.order_id = o.id
             LEFT JOIN customers c ON t.customer_id = c.id
             LEFT JOIN users u ON t.processed_by = u.id
             {$where}
             ORDER BY t.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        return [
            'rows' => $rows,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'pages' => ceil($total / $perPage),
        ];
    }

    public static function getDailySummary(int $shopId, string $date): array
    {
        $db = Database::getInstance();
        $rows = $db->fetchAll(
            "SELECT method, type, SUM(amount) as total, COUNT(*) as count
             FROM transactions
             WHERE shop_id = :shop_id AND DATE(created_at) = :date AND status = 'completed'
             GROUP BY method, type
             ORDER BY method",
            ['shop_id' => $shopId, 'date' => $date]
        );

        $summary = [];
        $grandTotal = 0;
        foreach ($rows as $r) {
            $key = $r['method'];
            if (!isset($summary[$key])) {
                $summary[$key] = ['method' => $r['method'], 'payment' => 0, 'refund' => 0, 'count' => 0];
            }
            $summary[$key][$r['type']] = (float) $r['total'];
            $summary[$key]['count'] += (int) $r['count'];
            $grandTotal += (float) $r['total'];
        }

        return ['rows' => $summary, 'grand_total' => $grandTotal];
    }

    public static function getMonthlySummary(int $shopId, int $year, int $month): array
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT DATE(created_at) as date, method, type, SUM(amount) as total, COUNT(*) as count
             FROM transactions
             WHERE shop_id = :shop_id
               AND YEAR(created_at) = :year
               AND MONTH(created_at) = :month
               AND status = 'completed'
             GROUP BY DATE(created_at), method, type
             ORDER BY date DESC, method",
            ['shop_id' => $shopId, 'year' => $year, 'month' => $month]
        );
    }
}