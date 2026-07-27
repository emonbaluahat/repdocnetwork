<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\AuthContext;
use App\Core\Request;
use App\Core\View;
use App\Core\Database;
use App\Core\Security;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Customer;
use App\Models\Service;
use App\Models\Transaction;
use App\Models\AuditLog;

class OrderController extends Controller
{
    private function getShopId(): int
    {
        return AuthContext::shopId();
    }

    public function index(): void
    {
        $this->authorize('view_orders');
        $shopId = $this->getShopId();

        $search = trim(Request::input('search'));
        $status = trim(Request::input('status'));
        $dateFrom = trim(Request::input('date_from'));
        $dateTo = trim(Request::input('date_to'));
        $page = max(1, (int) (Request::input('page') ?? 1));
        $perPage = min(100, max(1, (int) (Request::input('per_page') ?? 25)));

        $result = Order::search($shopId, $search, $status, $dateFrom, $dateTo, $page, $perPage);

        $todayStats = Order::getTodayStats($shopId);
        $monthlyStats = Order::getMonthlyStats($shopId);

        $this->render('orders/index', [
            'orders' => $result['items'],
            'total' => $result['total'],
            'page' => $result['page'],
            'per_page' => $result['per_page'],
            'total_pages' => $result['total_pages'],
            'has_prev' => $result['has_prev'],
            'has_next' => $result['has_next'],
            'search' => $search,
            'status' => $status,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'today_stats' => $todayStats,
            'monthly_stats' => $monthlyStats,
        ]);
    }

    public function create(): void
    {
        $this->authorize('create_orders');
        $shopId = $this->getShopId();

        $services = Service::getActiveByCategory($shopId);

        $this->render('orders/create', [
            'services' => $services,
        ]);
    }

    public function store(): void
    {
        $this->authorize('create_orders');
        $shopId = $this->getShopId();
        $userId = AuthContext::id();

        $customerId = (int) Request::input('customer_id');
        if (!$customerId) {
            flash('error', 'অনুগ্রহ করে গ্রাহক নির্বাচন করুন।');
            $this->back();
        }

        $customer = Customer::find($customerId);
        if (!$customer || (int) $customer['shop_id'] !== $shopId) {
            flash('error', __('customer.not_found'));
            $this->back();
        }

        $itemNames = Request::input('item_name', []);
        $itemServices = Request::input('item_service', []);
        $itemQtys = Request::input('item_qty', []);
        $itemPrices = Request::input('item_price', []);

        $items = [];
        $totalAmount = 0;

        foreach ($itemNames as $i => $name) {
            $name = trim($name);
            if (empty($name)) continue;

            $qty = max(1, (int) ($itemQtys[$i] ?? 1));
            $price = (float) ($itemPrices[$i] ?? 0);
            $total = $qty * $price;

            $items[] = [
                'service_id' => !empty($itemServices[$i]) ? (int) $itemServices[$i] : null,
                'name' => $name,
                'quantity' => $qty,
                'unit_price' => $price,
                'total_price' => $total,
            ];
            $totalAmount += $total;
        }

        if (empty($items)) {
            flash('error', 'অনুগ্রহ করে কমপক্ষে একটি আইটেম যোগ করুন।');
            $this->back();
        }

        $discountAmount = (float) (Request::input('discount_amount') ?? 0);
        $discountType = Request::input('discount_type');
        $taxRate = (float) (Request::input('tax_rate') ?? 0);
        $netAmount = $totalAmount;

        if ($discountAmount > 0 && $discountType === 'percentage') {
            $discountAmount = min($discountAmount, 100);
            $discountValue = $netAmount * ($discountAmount / 100);
        } else {
            $discountValue = min($discountAmount, $netAmount);
        }
        $netAmount -= $discountValue;

        $taxValue = $netAmount * ($taxRate / 100);
        $netAmount += $taxValue;

        $reference = 'ORD-' . date('Y') . '-' . date('m') . '-' . strtoupper(bin2hex(random_bytes(3)));

        $data = [
            'shop_id' => $shopId,
            'customer_id' => $customerId,
            'reference' => $reference,
            'status' => 'pending',
            'priority' => Request::input('priority') ?? 'normal',
            'amount' => $netAmount,
            'paid_amount' => 0,
            'due_amount' => $netAmount,
            'discount_amount' => $discountValue,
            'discount_type' => $discountValue > 0 ? $discountType : null,
            'tax_amount' => $taxValue,
            'notes' => Request::input('notes'),
            'internal_notes' => Request::input('internal_notes'),
            'created_by' => $userId,
            'updated_by' => $userId,
        ];

        $estimatedReady = Request::input('estimated_ready_at');
        if ($estimatedReady) {
            $data['estimated_ready_at'] = $estimatedReady;
        }

        $db = Database::getInstance();

        try {
            $orderId = Order::create($data);

            foreach ($items as $item) {
                $db->insert('order_items', [
                    'order_id' => $orderId,
                    'service_id' => $item['service_id'],
                    'name' => $item['name'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['total_price'],
                ]);
            }

            Order::addTimeline($orderId, $shopId, 'created', 'অর্ডার তৈরি করা হয়েছে।', [
                'reference' => $reference,
                'customer_name' => $customer['name'],
                'amount' => $netAmount,
            ]);

            AuditLog::log('order.created', 'order', $orderId, null, $data, $userId, $shopId);

            $paymentMethod = Request::input('payment_method');
            $paymentAmount = (float) (Request::input('payment_amount') ?? 0);

            if ($paymentMethod && $paymentAmount > 0) {
                Transaction::create([
                    'shop_id' => $shopId,
                    'order_id' => $orderId,
                    'customer_id' => $customerId,
                    'type' => 'payment',
                    'method' => $paymentMethod,
                    'amount' => $paymentAmount,
                    'notes' => Request::input('payment_notes') ?? '',
                    'status' => 'completed',
                    'processed_by' => $userId,
                ]);
            }

            flash('success', __('order.created'));
            $this->redirect('/orders/' . $orderId);
        } catch (\Exception $e) {
            flash('error', 'অর্ডার তৈরি করতে সমস্যা হয়েছে: ' . $e->getMessage());
            $this->back();
        }
    }

    public function show(int $id): void
    {
        $this->authorize('view_orders');
        $shopId = $this->getShopId();

        $orderData = $this->loadOrder($id, $shopId);
        if (!$orderData) return;

        $order = $orderData['order'];
        $items = Order::getItems($id);
        $timeline = Order::getTimeline($id);
        $transactions = Order::getTransactions($id);

        $this->render('orders/detail', [
            'order' => $order,
            'items' => $items,
            'timeline' => $timeline,
            'transactions' => $transactions,
        ]);
    }

    public function edit(int $id): void
    {
        $this->authorize('update_orders');
        $shopId = $this->getShopId();

        $orderData = $this->loadOrder($id, $shopId);
        if (!$orderData) return;

        $order = $orderData['order'];
        $items = Order::getItems($id);
        $services = Service::getActiveByCategory($shopId);
        $customers = Customer::getByShop($shopId, '', '', 'name ASC', 1, 1000);

        $this->render('orders/edit', [
            'order' => $order,
            'items' => $items,
            'services' => $services,
            'customers' => $customers['items'] ?? [$orderData['customer']],
        ]);
    }

    public function update(int $id): void
    {
        $this->authorize('update_orders');
        $shopId = $this->getShopId();
        $userId = AuthContext::id();

        $orderData = $this->loadOrder($id, $shopId);
        if (!$orderData) return;

        $oldOrder = $orderData['order'];

        $updateData = [
            'notes' => Request::input('notes'),
            'internal_notes' => Request::input('internal_notes'),
            'priority' => Request::input('priority') ?? 'normal',
            'updated_by' => $userId,
        ];

        $estimatedReady = Request::input('estimated_ready_at');
        if ($estimatedReady) {
            $updateData['estimated_ready_at'] = $estimatedReady;
        } else {
            $updateData['estimated_ready_at'] = null;
        }

        Order::update($id, $updateData);

        Order::addTimeline($id, $shopId, 'updated', 'অর্ডার তথ্য আপডেট করা হয়েছে।');

        AuditLog::log('order.updated', 'order', $id, $oldOrder, $updateData, $userId, $shopId);

        flash('success', __('order.updated'));
        $this->redirect('/orders/' . $id);
    }

    public function destroy(int $id): void
    {
        $this->authorize('delete_orders');
        $shopId = $this->getShopId();
        $userId = AuthContext::id();

        $orderData = $this->loadOrder($id, $shopId);
        if (!$orderData) return;

        $db = Database::getInstance();

        $db->delete('order_items', 'order_id = :order_id', ['order_id' => $id]);
        $db->delete('order_timeline', 'order_id = :order_id', ['order_id' => $id]);

        Order::delete($id);

        AuditLog::log('order.deleted', 'order', $id, $orderData['order'], null, $userId, $shopId);

        flash('success', __('order.deleted'));
        $this->redirect('/orders');
    }

    public function updateStatus(int $id): void
    {
        $this->authorize('change_order_status');
        $shopId = $this->getShopId();
        $userId = AuthContext::id();

        $orderData = $this->loadOrder($id, $shopId);
        if (!$orderData) return;

        $newStatus = Request::input('status');
        $validStatuses = ['pending', 'confirmed', 'in_progress', 'ready', 'completed', 'cancelled', 'delivered'];

        if (!in_array($newStatus, $validStatuses)) {
            $this->json(['error' => 'Invalid status.'], 422);
        }

        $oldStatus = $orderData['order']['status'];
        $updateData = ['status' => $newStatus, 'updated_by' => $userId];

        if ($newStatus === 'completed') {
            $updateData['completed_at'] = date('Y-m-d H:i:s');
        }
        if ($newStatus === 'delivered') {
            $updateData['delivered_at'] = date('Y-m-d H:i:s');
        }

        Order::update($id, $updateData);

        $statusLabels = [
            'pending' => 'পেন্ডিং', 'confirmed' => 'নিশ্চিত', 'in_progress' => 'প্রক্রিয়াধীন',
            'ready' => 'প্রস্তুত', 'completed' => 'সম্পন্ন', 'cancelled' => 'বাতিল', 'delivered' => 'ডেলিভারি',
        ];

        Order::addTimeline($id, $shopId, 'status_changed', "স্ট্যাটাস পরিবর্তন: {$statusLabels[$oldStatus]} → {$statusLabels[$newStatus]}", [
            'from' => $oldStatus,
            'to' => $newStatus,
        ]);

        AuditLog::log('order.status_changed', 'order', $id,
            ['status' => $oldStatus], ['status' => $newStatus], $userId, $shopId);

        $this->json([
            'status' => $newStatus,
            'message' => __('order.status_updated'),
        ]);
    }

    public function addPayment(int $id): void
    {
        $this->authorize('create_transactions');
        $shopId = $this->getShopId();
        $userId = AuthContext::id();

        $orderData = $this->loadOrder($id, $shopId);
        if (!$orderData) return;

        $order = $orderData['order'];

        $amount = (float) (Request::input('amount') ?? 0);
        $method = Request::input('method');
        $notes = Request::input('notes');

        if ($amount <= 0) {
            $this->json(['error' => 'Invalid amount.'], 422);
        }

        $validMethods = ['cash', 'bkash', 'nagad', 'rocket', 'bank', 'card', 'other'];
        if (!in_array($method, $validMethods)) {
            $this->json(['error' => 'Invalid payment method.'], 422);
        }

        $txnId = Transaction::create([
            'shop_id' => $shopId,
            'order_id' => $id,
            'customer_id' => $order['customer_id'],
            'type' => 'payment',
            'method' => $method,
            'amount' => $amount,
            'notes' => $notes,
            'status' => 'completed',
            'processed_by' => $userId,
        ]);

        $newPaid = (float) $order['paid_amount'] + $amount;
        Order::update($id, [
            'paid_amount' => $newPaid,
            'updated_by' => $userId,
        ]);
        Order::recalculateDue($id);

        $updatedOrder = Order::find($id);

        Order::addTimeline($id, $shopId, 'payment_received', "পেমেন্ট গৃহীত: ৳{$amount} (" . strtoupper($method) . ")", [
            'amount' => $amount,
            'method' => $method,
            'transaction_id' => $txnId,
        ]);

        AuditLog::log('transaction.created', 'transaction', $txnId, null, [
            'order_id' => $id,
            'amount' => $amount,
            'method' => $method,
        ], $userId, $shopId);

        $this->json([
            'paid_amount' => $updatedOrder['paid_amount'],
            'due_amount' => $updatedOrder['due_amount'],
            'message' => 'পেমেন্ট রেকর্ড করা হয়েছে।',
        ]);
    }

    public function printReceipt(int $id): void
    {
        $this->authorize('print_orders');
        $shopId = $this->getShopId();

        $orderData = $this->loadOrder($id, $shopId);
        if (!$orderData) return;

        $order = $orderData['order'];
        $customer = $orderData['customer'];
        $shop = $orderData['shop'];
        $items = Order::getItems($id);
        $transactions = Order::getTransactions($id);

        $settings = \App\Models\Shop::getSettings($shopId);
        $businessHours = $settings['business_hours'] ?? [];
        $invoiceSettings = $settings['invoice'] ?? [];

        View::setLayout(null);

        $this->render('orders/print', [
            'order' => $order,
            'customer' => $customer,
            'shop' => $shop,
            'items' => $items,
            'transactions' => $transactions,
            'business_hours' => $businessHours,
            'invoice_settings' => $invoiceSettings,
        ]);
    }

    public function timeline(int $id): void
    {
        $this->authorize('view_orders');
        $shopId = $this->getShopId();

        $order = Order::find($id);
        if (!$order || (int) $order['shop_id'] !== $shopId) {
            $this->json([]);
        }

        $timeline = Order::getTimeline($id);
        $this->json($timeline);
    }

    public function searchCustomer(): void
    {
        $this->authorize('view_customers');
        $shopId = $this->getShopId();

        $query = trim(Request::input('q'));
        $limit = min(20, (int) (Request::input('limit') ?? 10));

        if (strlen($query) < 1) {
            $this->json([]);
        }

        $customers = Customer::search($shopId, $query, $limit);
        $this->json($customers);
    }

    public function searchService(): void
    {
        $this->authorize('view_services');
        $shopId = $this->getShopId();

        $query = trim(Request::input('q'));
        if (strlen($query) < 1) {
            $this->json([]);
        }

        $result = Service::search($shopId, $query, null, 'active', 'name ASC', 1, 20);
        $this->json($result['items']);
    }

    private function loadOrder(int $id, int $shopId): ?array
    {
        $order = Order::find($id);
        if (!$order || (int) $order['shop_id'] !== $shopId) {
            if (Request::isAjax()) {
                $this->json(['error' => __('order.not_found')], 404);
            }
            flash('error', __('order.not_found'));
            $this->redirect('/orders');
            return null;
        }

        $customer = Customer::find((int) $order['customer_id']);
        $shop = \App\Models\Shop::find($shopId);

        return [
            'order' => $order,
            'customer' => $customer,
            'shop' => $shop,
        ];
    }
}