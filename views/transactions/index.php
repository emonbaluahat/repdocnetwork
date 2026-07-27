<div class="max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-semibold text-text-primary dark:text-text-dark-primary font-bengali">লেনদেন</h1>
        <a href="<?= url('transactions/report') ?>" class="btn-secondary btn-sm font-bengali">রিপোর্ট</a>
    </div>

    <?php if (!empty($summary['rows'])): ?>
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-2 mb-6">
            <?php foreach ($summary['rows'] as $s): ?>
                <div class="bg-white dark:bg-card border border-border dark:border-border-dark rounded-lg p-3 text-center">
                    <p class="text-xs text-text-tertiary font-bengali">
                        <?php $methodLabels = ['cash'=>'নগদ','bkash'=>'বিকাশ','nagad'=>'নগদ','rocket'=>'রকেট','bank'=>'ব্যাংক','card'=>'কার্ড','other'=>'অন্যান্য']; ?>
                        <?= $methodLabels[$s['method']] ?? $s['method'] ?>
                    </p>
                    <p class="text-lg font-bold text-green-600">৳<?= number_format($s['payment'], 2) ?></p>
                    <?php if ($s['refund'] > 0): ?>
                        <p class="text-xs text-error-500">-৳<?= number_format($s['refund'], 2) ?></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="bg-white dark:bg-card border border-border dark:border-border-dark rounded-xl">
        <form method="GET" action="<?= url('transactions') ?>" class="p-4 border-b border-border dark:border-border-dark">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
                <div>
                    <input type="text" name="search" placeholder="সার্চ..." value="<?= e($search ?? '') ?>" class="input input-sm w-full">
                </div>
                <div>
                    <select name="type" class="input input-sm w-full font-bengali">
                        <option value="">সব ধরন</option>
                        <option value="payment" <?= $type === 'payment' ? 'selected' : '' ?>>পেমেন্ট</option>
                        <option value="refund" <?= $type === 'refund' ? 'selected' : '' ?>>রিফান্ড</option>
                        <option value="adjustment" <?= $type === 'adjustment' ? 'selected' : '' ?>>অ্যাডজাস্টমেন্ট</option>
                    </select>
                </div>
                <div>
                    <select name="method" class="input input-sm w-full font-bengali">
                        <option value="">সব মেথড</option>
                        <option value="cash" <?= $method === 'cash' ? 'selected' : '' ?>>নগদ</option>
                        <option value="bkash" <?= $method === 'bkash' ? 'selected' : '' ?>>বিকাশ</option>
                        <option value="nagad" <?= $method === 'nagad' ? 'selected' : '' ?>>নগদ</option>
                        <option value="rocket" <?= $method === 'rocket' ? 'selected' : '' ?>>রকেট</option>
                        <option value="bank" <?= $method === 'bank' ? 'selected' : '' ?>>ব্যাংক</option>
                        <option value="card" <?= $method === 'card' ? 'selected' : '' ?>>কার্ড</option>
                        <option value="other" <?= $method === 'other' ? 'selected' : '' ?>>অন্যান্য</option>
                    </select>
                </div>
                <div>
                    <input type="date" name="date_from" value="<?= e($date_from ?? '') ?>" class="input input-sm w-full">
                </div>
                <div class="flex items-center gap-2">
                    <input type="date" name="date_to" value="<?= e($date_to ?? '') ?>" class="input input-sm flex-1">
                    <button type="submit" class="btn-primary btn-sm font-bengali">ফিল্টার</button>
                    <a href="<?= url('transactions') ?>" class="btn-ghost btn-sm">×</a>
                </div>
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="text-xs text-text-tertiary uppercase tracking-wider border-b border-border dark:border-border-dark">
                        <th class="text-left p-3 font-bengali">রেফারেন্স</th>
                        <th class="text-left p-3 font-bengali">গ্রাহক</th>
                        <th class="text-left p-3 font-bengali">অর্ডার</th>
                        <th class="text-left p-3 font-bengali">ধরন</th>
                        <th class="text-left p-3 font-bengali">মেথড</th>
                        <th class="text-right p-3 font-bengali">পরিমাণ</th>
                        <th class="text-left p-3 font-bengali">তারিখ</th>
                        <th class="text-left p-3 font-bengali">প্রক্রিয়াকারী</th>
                        <?php if (\App\Core\AuthContext::hasPermission('refund_transactions')): ?>
                            <th class="text-center p-3"></th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border dark:divide-border-dark">
                    <?php if (empty($transactions)): ?>
                        <tr>
                            <td colspan="<?= \App\Core\AuthContext::hasPermission('refund_transactions') ? 9 : 8 ?>" class="p-6 text-center text-sm text-text-tertiary font-bengali">
                                <?php if (!empty($search) || !empty($type) || !empty($method)): ?>
                                    ফিল্টার অনুযায়ী কোনো লেনদেন নেই।
                                <?php else: ?>
                                    এখনো কোনো লেনদেন নেই।
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($transactions as $txn): ?>
                            <tr class="hover:bg-surface dark:hover:bg-surface-dark/50 transition">
                                <td class="p-3">
                                    <a href="<?= url('transactions/' . $txn['id']) ?>" class="text-sm font-mono text-primary-600 hover:underline">
                                        <?= e($txn['reference']) ?>
                                    </a>
                                </td>
                                <td class="p-3 text-sm text-text-secondary">
                                    <?php if ($txn['customer_name']): ?>
                                        <?= e($txn['customer_name']) ?>
                                    <?php else: ?>
                                        <span class="text-text-tertiary">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-3 text-sm">
                                    <?php if ($txn['order_reference']): ?>
                                        <a href="<?= url('orders/' . $txn['order_id']) ?>" class="text-primary-600 hover:underline font-mono"><?= e($txn['order_reference']) ?></a>
                                    <?php else: ?>
                                        <span class="text-text-tertiary">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-3">
                                    <?php if ($txn['type'] === 'refund'): ?>
                                        <span class="badge badge-error text-xs font-bengali">রিফান্ড</span>
                                    <?php elseif ($txn['type'] === 'adjustment'): ?>
                                        <span class="badge badge-warning text-xs font-bengali">অ্যাডজাস্টমেন্ট</span>
                                    <?php else: ?>
                                        <span class="badge badge-green text-xs font-bengali">পেমেন্ট</span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-3 text-sm text-text-secondary font-bengali">
                                    <?php $methodLabels = ['cash'=>'নগদ','bkash'=>'বিকাশ','nagad'=>'নগদ','rocket'=>'রকেট','bank'=>'ব্যাংক','card'=>'কার্ড','other'=>'অন্যান্য']; ?>
                                    <?= $methodLabels[$txn['method']] ?? $txn['method'] ?>
                                </td>
                                <td class="p-3 text-sm text-right font-semibold <?= $txn['type'] === 'refund' ? 'text-error-600' : 'text-green-600' ?>">
                                    <?= $txn['type'] === 'refund' ? '-' : '+' ?>৳<?= number_format((float) $txn['amount'], 2) ?>
                                </td>
                                <td class="p-3 text-sm text-text-tertiary"><?= format_datetime($txn['created_at'] ?? '') ?></td>
                                <td class="p-3 text-sm text-text-secondary"><?= e($txn['processor_name'] ?? '') ?></td>
                                <?php if (AuthContext::hasPermission('refund_transactions')): ?>
                                    <td class="p-3 text-center">
                                        <?php if ($txn['type'] === 'payment' && $txn['status'] === 'completed'): ?>
                                            <button type="button" class="btn-ghost btn-xs text-error-500 font-bengali"
                                                    onclick="refundTxn(<?= $txn['id'] ?>, '<?= e($txn['reference']) ?>')">রিফান্ড</button>
                                        <?php endif; ?>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($total_pages > 1): ?>
            <div class="flex items-center justify-between p-4 border-t border-border dark:border-border-dark">
                <p class="text-sm text-text-tertiary"><?= $total ?> টি লেনদেন</p>
                <div class="flex items-center gap-2">
                    <?php if ($page > 1): ?>
                        <a href="<?= url('transactions?page=' . ($page - 1) . (!empty($search) ? '&search=' . urlencode($search) : '') . (!empty($type) ? '&type=' . $type : '') . (!empty($method) ? '&method=' . $method : '') . (!empty($date_from) ? '&date_from=' . $date_from : '') . (!empty($date_to) ? '&date_to=' . $date_to : '') ) ?>" class="btn-ghost btn-sm font-bengali">পূর্ববর্তী</a>
                    <?php endif; ?>
                    <?php if ($page < $total_pages): ?>
                        <a href="<?= url('transactions?page=' . ($page + 1) . (!empty($search) ? '&search=' . urlencode($search) : '') . (!empty($type) ? '&type=' . $type : '') . (!empty($method) ? '&method=' . $method : '') . (!empty($date_from) ? '&date_from=' . $date_from : '') . (!empty($date_to) ? '&date_to=' . $date_to : '') ) ?>" class="btn-ghost btn-sm font-bengali">পরবর্তী</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function refundTxn(id, ref) {
    const amount = prompt('রিফান্ড পরিমাণ (সর্বোচ্চ):');
    if (amount === null) return;
    const notes = prompt('রিফান্ড নোট (ঐচ্ছিক):');
    const params = new URLSearchParams({ amount: amount || '', notes: notes || '', _csrf_token: '<?= csrf_token() ?>' });
    fetch('<?= url('transactions/') ?>' + id + '/refund', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: params.toString()
    }).then(r => r.json()).then(data => {
        if (data.message) { alert(data.message); location.reload(); }
        else alert(data.error || 'ত্রুটি');
    });
}
</script>