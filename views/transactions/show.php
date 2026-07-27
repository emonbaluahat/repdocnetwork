<div class="max-w-3xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <a href="<?= url('transactions') ?>" class="text-sm text-text-secondary dark:text-text-dark-secondary hover:text-text-primary transition font-bengali mb-2 inline-block">← লেনদেন তালিকা</a>
            <h1 class="text-xl font-semibold text-text-primary dark:text-text-dark-primary font-mono"><?= e($transaction['reference']) ?></h1>
        </div>
    </div>

    <div class="bg-white dark:bg-card border border-border dark:border-border-dark rounded-xl p-6">
        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div>
                <dt class="text-text-tertiary mb-1 font-bengali">রেফারেন্স</dt>
                <dd class="font-mono font-medium"><?= e($transaction['reference']) ?></dd>
            </div>
            <div>
                <dt class="text-text-tertiary mb-1 font-bengali">ধরন</dt>
                <dd>
                    <?php if ($transaction['type'] === 'refund'): ?>
                        <span class="badge badge-error font-bengali">রিফান্ড</span>
                    <?php elseif ($transaction['type'] === 'adjustment'): ?>
                        <span class="badge badge-warning font-bengali">অ্যাডজাস্টমেন্ট</span>
                    <?php else: ?>
                        <span class="badge badge-green font-bengali">পেমেন্ট</span>
                    <?php endif; ?>
                </dd>
            </div>
            <div>
                <dt class="text-text-tertiary mb-1 font-bengali">পদ্ধতি</dt>
                <dd class="font-medium">
                    <?php $methodLabels = ['cash'=>'নগদ','bkash'=>'বিকাশ','nagad'=>'নগদ','rocket'=>'রকেট','bank'=>'ব্যাংক','card'=>'কার্ড','other'=>'অন্যান্য']; ?>
                    <?= $methodLabels[$transaction['method']] ?? $transaction['method'] ?>
                </dd>
            </div>
            <div>
                <dt class="text-text-tertiary mb-1 font-bengali">পরিমাণ</dt>
                <dd class="font-semibold text-lg <?= $transaction['type'] === 'refund' ? 'text-error-600' : 'text-green-600' ?>">
                    <?= $transaction['type'] === 'refund' ? '-' : '+' ?>৳<?= number_format((float) $transaction['amount'], 2) ?>
                </dd>
            </div>
            <div>
                <dt class="text-text-tertiary mb-1 font-bengali">স্ট্যাটাস</dt>
                <dd>
                    <?php $statusLabels = ['pending'=>'পেন্ডিং','completed'=>'সম্পন্ন','failed'=>'ব্যর্থ','refunded'=>'রিফান্ডকৃত']; ?>
                    <span class="badge <?= $transaction['status'] === 'completed' ? 'badge-green' : ($transaction['status'] === 'refunded' ? 'badge-error' : 'badge-warning') ?>">
                        <?= $statusLabels[$transaction['status']] ?? $transaction['status'] ?>
                    </span>
                </dd>
            </div>
            <div>
                <dt class="text-text-tertiary mb-1 font-bengali">তারিখ</dt>
                <dd><?= format_datetime($transaction['created_at'] ?? '') ?></dd>
            </div>
            <?php if ($transaction['order_id']): ?>
                <div>
                    <dt class="text-text-tertiary mb-1 font-bengali">অর্ডার</dt>
                    <dd><a href="<?= url('orders/' . $transaction['order_id']) ?>" class="text-primary-600 hover:underline font-mono">ORD-<?= $transaction['order_id'] ?></a></dd>
                </div>
            <?php endif; ?>
            <?php if ($transaction['notes']): ?>
                <div class="md:col-span-2">
                    <dt class="text-text-tertiary mb-1 font-bengali">নোট</dt>
                    <dd class="text-text-primary"><?= nl2br(e($transaction['notes'])) ?></dd>
                </div>
            <?php endif; ?>
        </dl>
    </div>
</div>