<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\AuthContext;
use App\Core\Request;
use App\Core\View;
use App\Models\Transaction;
use App\Models\Order;
use App\Models\AuditLog;

class TransactionController extends Controller
{
    private function getShopId(): int
    {
        return AuthContext::shopId();
    }

    public function index(): void
    {
        $this->authorize('view_transactions');
        $shopId = $this->getShopId();

        $search = trim(Request::input('search'));
        $type = Request::input('type');
        $method = Request::input('method');
        $dateFrom = Request::input('date_from');
        $dateTo = Request::input('date_to');
        $page = max(1, (int) (Request::input('page') ?? 1));
        $perPage = min(100, max(1, (int) (Request::input('per_page') ?? 25)));

        $result = Transaction::getByShop($shopId, [
            'search' => $search,
            'type' => $type,
            'method' => $method,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ], $page, $perPage);

        $summary = Transaction::getDailySummary($shopId, date('Y-m-d'));

        $this->render('transactions/index', [
            'transactions' => $result['rows'],
            'total' => $result['total'],
            'page' => $result['page'],
            'per_page' => $result['perPage'],
            'total_pages' => $result['pages'],
            'has_prev' => $page > 1,
            'has_next' => $page < $result['pages'],
            'search' => $search,
            'type' => $type,
            'method' => $method,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'summary' => $summary,
        ]);
    }

    public function store(): void
    {
        $this->authorize('create_transactions');
        $shopId = $this->getShopId();
        $userId = AuthContext::id();

        $orderId = (int) (Request::input('order_id') ?? 0);
        $amount = (float) (Request::input('amount') ?? 0);
        $method = Request::input('method');
        $notes = Request::input('notes');

        if ($amount <= 0) {
            $this->json(['error' => 'পরিমাণ বৈধ নয়।'], 422);
        }

        $validMethods = ['cash', 'bkash', 'nagad', 'rocket', 'bank', 'card', 'other'];
        if (!in_array($method, $validMethods)) {
            $this->json(['error' => 'পেমেন্ট মেথড বৈধ নয়।'], 422);
        }

        $order = null;
        $customerId = null;
        if ($orderId > 0) {
            $order = Order::find($orderId);
            if (!$order || (int) $order['shop_id'] !== $shopId) {
                $this->json(['error' => 'অর্ডার পাওয়া যায়নি।'], 404);
            }
            $customerId = (int) $order['customer_id'];
        }

        $reference = Transaction::generateReference();

        $txnId = Transaction::create([
            'shop_id' => $shopId,
            'order_id' => $orderId ?: null,
            'customer_id' => $customerId,
            'reference' => $reference,
            'type' => Transaction::TYPE_PAYMENT,
            'method' => $method,
            'amount' => $amount,
            'status' => Transaction::STATUS_COMPLETED,
            'notes' => $notes,
            'processed_by' => $userId,
        ]);

        if ($order) {
            $newPaid = (float) $order['paid_amount'] + $amount;
            Order::update($orderId, ['paid_amount' => $newPaid]);
            Order::recalculateDue($orderId);
            Order::addTimeline($orderId, $shopId, 'payment_received', "পেমেন্ট: ৳{$amount}");
        }

        AuditLog::log('transaction.created', 'transaction', $txnId, null, [
            'order_id' => $orderId,
            'amount' => $amount,
            'method' => $method,
        ], $userId, $shopId);

        $this->json(['message' => 'লেনদেন সফল হয়েছে।', 'id' => $txnId, 'reference' => $reference]);
    }

    public function show(int $id): void
    {
        $this->authorize('view_transactions');
        $shopId = $this->getShopId();

        $txn = Transaction::find($id);
        if (!$txn || (int) $txn['shop_id'] !== $shopId) {
            $this->notFound();
            return;
        }

        $this->render('transactions/show', ['transaction' => $txn]);
    }

    public function refund(int $id): void
    {
        $this->authorize('refund_transactions');
        $shopId = $this->getShopId();
        $userId = AuthContext::id();

        $txn = Transaction::find($id);
        if (!$txn || (int) $txn['shop_id'] !== $shopId) {
            $this->json(['error' => 'লেনদেন পাওয়া যায়নি।'], 404);
        }

        if ($txn['type'] !== Transaction::TYPE_PAYMENT || $txn['status'] !== Transaction::STATUS_COMPLETED) {
            $this->json(['error' => 'শুধুমাত্র সম্পন্ন পেমেন্ট রিফান্ড করা যাবে।'], 422);
        }

        $amount = (float) $txn['amount'];
        $refundAmount = min($amount, (float) (Request::input('amount') ?? $amount));

        $refId = Transaction::generateReference();
        $refundTxnId = Transaction::create([
            'shop_id' => $shopId,
            'order_id' => $txn['order_id'],
            'customer_id' => $txn['customer_id'],
            'reference' => $refId,
            'type' => Transaction::TYPE_REFUND,
            'method' => $txn['method'],
            'amount' => $refundAmount,
            'status' => Transaction::STATUS_COMPLETED,
            'notes' => 'রিফান্ড: ' . $txn['reference'] . ' — ' . (Request::input('notes') ?? ''),
            'processed_by' => $userId,
        ]);

        Transaction::update($id, ['status' => Transaction::STATUS_REFUNDED]);

        if ($txn['order_id']) {
            $order = Order::find($txn['order_id']);
            if ($order && (int) $order['shop_id'] === $shopId) {
                $newPaid = max(0, (float) $order['paid_amount'] - $refundAmount);
                Order::update($txn['order_id'], ['paid_amount' => $newPaid]);
                Order::recalculateDue($txn['order_id']);
                Order::addTimeline($txn['order_id'], $shopId, 'refund_issued', "রিফান্ড: ৳{$refundAmount}");
            }
        }

        AuditLog::log('transaction.refund', 'transaction', $refundTxnId, null, [
            'original_transaction_id' => $id,
            'amount' => $refundAmount,
        ], $userId, $shopId);

        $this->json(['message' => 'রিফান্ড সফল হয়েছে।', 'id' => $refundTxnId, 'reference' => $refId]);
    }

    public function report(): void
    {
        $this->authorize('view_transactions');
        $shopId = $this->getShopId();

        $reportType = Request::input('type', 'daily');
        $date = Request::input('date', date('Y-m-d'));
        $year = (int) (Request::input('year', date('Y')));
        $month = (int) (Request::input('month', date('m')));

        $data = [];
        if ($reportType === 'daily') {
            $summary = Transaction::getDailySummary($shopId, $date);
            $data['summary'] = $summary['rows'];
            $data['grand_total'] = $summary['grand_total'];
        } else {
            $data['rows'] = Transaction::getMonthlySummary($shopId, $year, $month);
            $totals = ['payment' => 0, 'refund' => 0];
            foreach ($data['rows'] as $r) {
                $totals[$r['type']] = ($totals[$r['type']] ?? 0) + (float) $r['total'];
            }
            $data['totals'] = $totals;
        }

        $this->render('transactions/report', [
            'report_type' => $reportType,
            'date' => $date,
            'year' => $year,
            'month' => $month,
            'data' => $data,
        ]);
    }
}