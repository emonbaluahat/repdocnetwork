<div class="max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <a href="<?= url('orders') ?>" class="text-sm text-text-secondary dark:text-text-dark-secondary hover:text-text-primary transition font-bengali mb-2 inline-block">← অর্ডার তালিকা</a>
            <h1 class="text-xl font-semibold text-text-primary dark:text-text-dark-primary font-mono"><?= e($order['reference']) ?></h1>
        </div>
        <div class="flex items-center gap-2">
            <?php if (AuthContext::hasPermission('print_orders')): ?>
                <a href="<?= url('orders/' . $order['id'] . '/print') ?>" target="_blank" class="btn-secondary btn-sm font-bengali">প্রিন্ট</a>
            <?php endif; ?>
            <?php if (AuthContext::hasPermission('delete_orders')): ?>
                <form method="POST" action="<?= url('orders/' . $order['id'] . '/delete') ?>"
                      onsubmit="return confirm('এই অর্ডার মুছে ফেলবেন?')">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn-ghost btn-sm text-error-600 font-bengali">মুছুন</button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white dark:bg-card border border-border dark:border-border-dark rounded-xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-semibold text-text-primary dark:text-text-dark-primary font-bengali">আইটেম</h2>
                    <span class="text-xs text-text-tertiary"><?= count($items) ?> টি আইটেম</span>
                </div>
                <table class="w-full">
                    <thead>
                        <tr class="text-xs text-text-tertiary uppercase tracking-wider">
                            <th class="text-left pb-2 font-bengali">আইটেম</th>
                            <th class="text-center pb-2 font-bengali">পরিমাণ</th>
                            <th class="text-right pb-2 font-bengali">ইউনিট মূল্য</th>
                            <th class="text-right pb-2 font-bengali">মোট</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border dark:divide-border-dark">
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td class="py-2 text-sm font-medium text-text-primary"><?= e($item['name']) ?></td>
                                <td class="py-2 text-sm text-center text-text-secondary">x<?= (int) $item['quantity'] ?></td>
                                <td class="py-2 text-sm text-right text-text-secondary">৳<?= number_format((float) $item['unit_price'], 2) ?></td>
                                <td class="py-2 text-sm text-right font-semibold">৳<?= number_format((float) $item['total_price'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="mt-4 pt-4 border-t border-border space-y-1 text-sm text-right">
                    <?php if ((float) $order['discount_amount'] > 0): ?>
                        <p class="text-text-secondary">ছাড়: -৳<?= number_format((float) $order['discount_amount'], 2) ?> <?= $order['discount_type'] === 'percentage' ? '(' . (float) $order['discount_amount'] . '%)' : '' ?></p>
                    <?php endif; ?>
                    <?php if ((float) $order['tax_amount'] > 0): ?>
                        <p class="text-text-secondary">ট্যাক্স: ৳<?= number_format((float) $order['tax_amount'], 2) ?></p>
                    <?php endif; ?>
                    <p class="text-lg font-bold">মোট: ৳<?= number_format((float) $order['amount'], 2) ?></p>
                    <?php if ((float) $order['due_amount'] > 0): ?>
                        <p class="text-error-600 font-semibold">বকেয়া: ৳<?= number_format((float) $order['due_amount'], 2) ?></p>
                    <?php else: ?>
                        <p class="text-green-600 font-semibold">পরিশোধিত ✓</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="bg-white dark:bg-card border border-border dark:border-border-dark rounded-xl p-6">
                <h2 class="text-sm font-semibold text-text-primary dark:text-text-dark-primary mb-4 font-bengali">কার্যকলাপ</h2>
                <?php if (empty($timeline)): ?>
                    <p class="text-sm text-text-tertiary text-center py-4 font-bengali">কোনো কার্যকলাপ নেই</p>
                <?php else: ?>
                    <div class="space-y-3 max-h-64 overflow-y-auto">
                        <?php foreach ($timeline as $entry): ?>
                            <div class="flex items-start gap-3 pb-3 border-b border-border dark:border-border-dark last:border-0">
                                <div class="w-2 h-2 mt-2 rounded-full bg-primary-400 dark:bg-primary-600 flex-shrink-0"></div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm text-text-primary dark:text-text-dark-primary"><?= e($entry['description'] ?? $entry['action']) ?></p>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <span class="text-xs text-text-tertiary dark:text-text-dark-tertiary"><?= format_datetime($entry['created_at']) ?></span>
                                        <?php if (!empty($entry['user_name'])): ?>
                                            <span class="text-xs text-text-tertiary dark:text-text-dark-tertiary">• <?= e($entry['user_name']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white dark:bg-card border border-border dark:border-border-dark rounded-xl p-4">
                <h3 class="text-xs font-semibold text-text-tertiary uppercase tracking-wider mb-3 font-bengali">অর্ডার তথ্য</h3>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-text-secondary font-bengali">গ্রাহক</dt>
                        <dd class="font-medium text-right">
                            <a href="<?= url('customers/' . $order['customer_id']) ?>" class="text-primary-600 hover:underline">
                                <?= e($order['customer_name'] ?? '') ?>
                            </a>
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-text-secondary font-bengali">ফোন</dt>
                        <dd class="font-medium"><?= e($order['customer_phone'] ?? '') ?></dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-text-secondary font-bengali">স্ট্যাটাস</dt>
                        <dd>
                            <span class="badge badge-primary text-xs">
                                <?php $labels = ['pending'=>'পেন্ডিং','confirmed'=>'নিশ্চিত','in_progress'=>'প্রক্রিয়াধীন','ready'=>'প্রস্তুত','completed'=>'সম্পন্ন','cancelled'=>'বাতিল','delivered'=>'ডেলিভারি']; ?>
                                <?= $labels[$order['status']] ?? $order['status'] ?>
                            </span>
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-text-secondary font-bengali">প্রায়োরিটি</dt>
                        <dd class="font-medium">
                            <?php $priorityLabels = ['normal'=>'নরমাল','urgent'=>'জরুরি','express'=>'এক্সপ্রেস']; ?>
                            <?= $priorityLabels[$order['priority']] ?? $order['priority'] ?>
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-text-secondary font-bengali">তৈরির তারিখ</dt>
                        <dd class="text-text-tertiary"><?= format_datetime($order['created_at'] ?? '') ?></dd>
                    </div>
                </dl>
            </div>

            <?php if (AuthContext::hasPermission('change_order_status')): ?>
                <div class="bg-white dark:bg-card border border-border dark:border-border-dark rounded-xl p-4">
                    <h3 class="text-xs font-semibold text-text-tertiary uppercase tracking-wider mb-3 font-bengali">স্ট্যাটাস আপডেট</h3>
                    <div class="space-y-2">
                        <?php
                        $statusFlow = ['pending', 'confirmed', 'in_progress', 'ready', 'completed'];
                        $currentIdx = array_search($order['status'], $statusFlow);
                        ?>
                        <div class="flex items-center gap-2 flex-wrap">
                            <?php foreach (['pending', 'confirmed', 'in_progress', 'ready', 'completed'] as $i => $s): ?>
                                <button type="button"
                                        class="btn-ghost btn-sm <?= $i <= ($currentIdx !== false ? $currentIdx : -1) ? 'text-primary-600' : 'text-text-tertiary' ?>"
                                        onclick="updateStatus('<?= $s ?>')"
                                        <?= $s === $order['status'] ? 'disabled' : '' ?>>
                                    <?php $labels = ['pending'=>'পেন্ডিং','confirmed'=>'নিশ্চিত','in_progress'=>'প্রক্রিয়াধীন','ready'=>'প্রস্তুত','completed'=>'সম্পন্ন']; ?>
                                    <?= $labels[$s] ?>
                                </button>
                                <?php if ($i < 4): ?><span class="text-text-tertiary text-xs">→</span><?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                        <?php if ($order['status'] !== 'cancelled'): ?>
                            <button type="button" class="btn-ghost btn-sm text-error-600 w-full mt-2 font-bengali"
                                    onclick="updateStatus('cancelled')">বাতিল করুন</button>
                        <?php endif; ?>
                        <?php if ($order['status'] === 'completed'): ?>
                            <button type="button" class="btn-ghost btn-sm w-full mt-2 font-bengali"
                                    onclick="updateStatus('delivered')">ডেলিভারি কমপ্লিট</button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (AuthContext::hasPermission('create_transactions') && $order['status'] !== 'cancelled'): ?>
                <div class="bg-white dark:bg-card border border-border dark:border-border-dark rounded-xl p-4" x-data="paymentForm()">
                    <h3 class="text-xs font-semibold text-text-tertiary uppercase tracking-wider mb-3 font-bengali">পেমেন্ট নিন</h3>
                    <form x-on:submit.prevent="submitPayment(<?= $order['id'] ?>)" class="space-y-2">
                        <select name="method" x-model="method" class="input input-sm">
                            <option value="cash">নগদ</option>
                            <option value="bkash">বিকাশ</option>
                            <option value="nagad">নগদ</option>
                            <option value="rocket">রকেট</option>
                            <option value="bank">ব্যাংক</option>
                            <option value="card">কার্ড</option>
                            <option value="other">অন্যান্য</option>
                        </select>
                        <input type="number" name="amount" x-model="amount" step="0.01" min="0"
                               class="input input-sm" placeholder="পরিমাণ" required>
                        <input type="text" name="notes" x-model="notes" class="input input-sm"
                               placeholder="নোট (ঐচ্ছিক)">
                        <button type="submit" class="btn-primary btn-sm w-full font-bengali">পেমেন্ট রেকর্ড করুন</button>
                    </form>
                    <p x-show="message" x-text="message" class="text-xs mt-2" :class="error ? 'text-error-600' : 'text-green-600'"></p>
                </div>
            <?php endif; ?>

            <?php if (!empty($transactions)): ?>
                <div class="bg-white dark:bg-card border border-border dark:border-border-dark rounded-xl p-4">
                    <h3 class="text-xs font-semibold text-text-tertiary uppercase tracking-wider mb-3 font-bengali">পেমেন্ট লগ</h3>
                    <div class="space-y-2">
                        <?php foreach ($transactions as $txn): ?>
                            <div class="flex items-center justify-between py-1">
                                <div>
                                    <span class="badge text-xs">
                                        <?php $methodLabels = ['cash'=>'নগদ','bkash'=>'বিকাশ','nagad'=>'নগদ','rocket'=>'রকেট','bank'=>'ব্যাংক','card'=>'কার্ড','other'=>'অন্যান্য']; ?>
                                        <?= $methodLabels[$txn['method']] ?? $txn['method'] ?>
                                    </span>
                                    <span class="text-xs text-text-tertiary ml-1"><?= format_date($txn['created_at']) ?></span>
                                </div>
                                <span class="text-sm font-semibold text-green-600">৳<?= number_format((float) $txn['amount'], 2) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function updateStatus(status) {
    if (!confirm('স্ট্যাটাস আপডেট করবেন?')) return;
    fetch('<?= url('orders/' . $order['id'] . '/status') ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'status=' + encodeURIComponent(status) + '&_csrf_token=<?= csrf_token() ?>'
    }).then(r => r.json()).then(data => {
        if (data.message) location.reload();
        else alert(data.error || 'ত্রুটি');
    });
}

function paymentForm() {
    return {
        method: 'cash',
        amount: '',
        notes: '',
        message: '',
        error: false,
        submitPayment(orderId) {
            if (!this.amount || parseFloat(this.amount) <= 0) {
                this.message = 'পরিমাণ লিখুন।';
                this.error = true;
                return;
            }
            const params = new URLSearchParams({
                method: this.method,
                amount: this.amount,
                notes: this.notes,
                _csrf_token: '<?= csrf_token() ?>'
            });
            fetch('<?= url('orders/' . $order['id'] . '/payment') ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: params.toString()
            }).then(r => r.json()).then(data => {
                if (data.message) {
                    this.message = data.message;
                    this.error = false;
                    this.amount = '';
                    this.notes = '';
                    location.reload();
                } else {
                    this.message = data.error || 'ত্রুটি';
                    this.error = true;
                }
            });
        }
    };
}
</script>