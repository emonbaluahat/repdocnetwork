<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\AuthContext;
use App\Core\View;
use App\Core\Database;
use App\Core\Response;
use App\Core\TenantManager;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Transaction;

class DashboardController extends Controller
{
    public function index(): void
    {
        if (AuthContext::guest()) {
            View::setLayout('layouts/auth');
            $this->render('auth/login');
            return;
        }

        if (AuthContext::isSuperAdmin()) {
            $this->renderAdminDashboard();
            return;
        }

        if (!AuthContext::hasShop()) {
            $this->render('dashboard/shop-select', [
                'shops' => AuthContext::shops(),
            ]);
            return;
        }

        $this->renderShopDashboard();
    }

    private function renderAdminDashboard(): void
    {
        $db = Database::getInstance();

        $totalUsers = $db->fetch("SELECT COUNT(*) as count FROM users")['count'] ?? 0;
        $totalShops = $db->fetch("SELECT COUNT(*) as count FROM shops")['count'] ?? 0;
        $totalOrders = $db->fetch("SELECT COUNT(*) as count FROM orders")['count'] ?? 0;
        $totalRevenue = $db->fetch("SELECT COALESCE(SUM(paid_amount), 0) as total FROM orders WHERE status != 'cancelled'")['total'] ?? 0;
        $totalCustomers = $db->fetch("SELECT COUNT(*) as count FROM customers")['count'] ?? 0;

        $recentUsers = $db->fetchAll("SELECT id, name, email, status, created_at FROM users ORDER BY created_at DESC LIMIT 5");
        $recentShops = $db->fetchAll("SELECT id, name, slug, status, created_at FROM shops ORDER BY created_at DESC LIMIT 5");

        $this->render('dashboard/admin', [
            'stats' => [
                'total_users' => (int) $totalUsers,
                'total_shops' => (int) $totalShops,
                'total_orders' => (int) $totalOrders,
                'total_revenue' => (float) $totalRevenue,
                'total_customers' => (int) $totalCustomers,
            ],
            'recent_users' => $recentUsers,
            'recent_shops' => $recentShops,
        ]);
    }

    private function renderShopDashboard(): void
    {
        $shopId = AuthContext::shopId();
        $db = Database::getInstance();

        $todayStart = date('Y-m-d 00:00:00');
        $todayEnd = date('Y-m-d 23:59:59');
        $monthStart = date('Y-m-01 00:00:00');

        $todayOrders = $db->fetch(
            "SELECT COUNT(*) as count FROM orders WHERE shop_id = :shop_id AND created_at BETWEEN :start AND :end",
            ['shop_id' => $shopId, 'start' => $todayStart, 'end' => $todayEnd]
        );
        $todayOrdersCount = (int) ($todayOrders['count'] ?? 0);

        $totalCustomers = $db->fetch(
            "SELECT COUNT(*) as count FROM customers WHERE shop_id = :shop_id",
            ['shop_id' => $shopId]
        );
        $totalCustomersCount = (int) ($totalCustomers['count'] ?? 0);

        $pendingOrders = $db->fetch(
            "SELECT COUNT(*) as count FROM orders WHERE shop_id = :shop_id AND status IN ('pending', 'confirmed', 'in_progress')",
            ['shop_id' => $shopId]
        );
        $pendingOrdersCount = (int) ($pendingOrders['count'] ?? 0);

        $monthlyRevenue = $db->fetch(
            "SELECT COALESCE(SUM(paid_amount), 0) as total FROM orders WHERE shop_id = :shop_id AND created_at >= :month_start AND status != 'cancelled'",
            ['shop_id' => $shopId, 'month_start' => $monthStart]
        );
        $monthlyRevenueAmount = (float) ($monthlyRevenue['total'] ?? 0);

        $recentOrders = $db->fetchAll(
            "SELECT o.*, c.name as customer_name, c.phone as customer_phone
             FROM orders o
             LEFT JOIN customers c ON o.customer_id = c.id
             WHERE o.shop_id = :shop_id
             ORDER BY o.created_at DESC
             LIMIT 5",
            ['shop_id' => $shopId]
        );

        $recentTransactions = $db->fetchAll(
            "SELECT t.*, o.reference as order_ref, c.name as customer_name
             FROM transactions t
             LEFT JOIN orders o ON t.order_id = o.id
             LEFT JOIN customers c ON t.customer_id = c.id
             WHERE t.shop_id = :shop_id
             ORDER BY t.created_at DESC
             LIMIT 5",
            ['shop_id' => $shopId]
        );

        $this->render('dashboard/index', [
            'user' => AuthContext::user(),
            'shop' => AuthContext::shop(),
            'role' => AuthContext::role(),
            'stats' => [
                'today_orders' => $todayOrdersCount,
                'total_customers' => $totalCustomersCount,
                'pending_orders' => $pendingOrdersCount,
                'monthly_revenue' => $monthlyRevenueAmount,
            ],
            'recent_orders' => $recentOrders,
            'recent_transactions' => $recentTransactions,
        ]);
    }

    public function switchShop(int $shopId): void
    {
        if (AuthContext::guest()) {
            Response::redirect('/login');
            return;
        }

        $userId = AuthContext::id();

        if (TenantManager::switchShop($shopId, $userId)) {
            flash('success', __('shop.switched'));
        } else {
            flash('error', __('shop.not_found'));
        }

        Response::redirect('/');
    }
}