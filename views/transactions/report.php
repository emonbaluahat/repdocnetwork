<div class="max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <a href="<?= url('transactions') ?>" class="text-sm text-text-secondary dark:text-text-dark-secondary hover:text-text-primary transition font-bengali mb-2 inline-block">← লেনদেন তালিকা</a>
            <h1 class="text-xl font-semibold text-text-primary dark:text-text-dark-primary font-bengali">লেনদেন রিপোর্ট</h1>
        </div>
    </div>

    <div class="bg-white dark:bg-card border border-border dark:border-border-dark rounded-xl p-4 mb-6">
        <form method="GET" action="<?= url('transactions/report') ?>" class="flex items-center gap-4">
            <div>
                <label class="block text-xs text-text-tertiary mb-1 font-bengali">টাইপ</label>
                <select name="type" class="input input-sm font-bengali">
                    <option value="daily" <?= $report_type === 'daily' ? 'selected' : '' ?>>দৈনিক</option>
                    <option value="monthly" <?= $report_type === 'monthly' ? 'selected' : '' ?>>মাসিক</option>
                </select>
            </div>
            <div x-show="$el.closest('form').querySelector('[name=type]').value === 'daily'" x-transition>
                <label class="block text-xs text-text-tertiary mb-1 font-bengali">তারিখ</label>
                <input type="date" name="date" value="<?= e($date) ?>" class="input input-sm">
            </div>
            <div x-show="$el.closest('form').querySelector('[name=type]').value === 'monthly'" x-transition>
                <label class="block text-xs text-text-tertiary mb-1 font-bengali">মাস</label>
                <div class="flex items-center gap-2">
                    <select name="month" class="input input-sm">
                        <?php for ($m = 1; $m <= 12; $m++): ?>
                            <option value="<?= str_pad($m, 2, '0', STR_PAD_LEFT) ?>" <?= ((int) $month) === $m ? 'selected' : '' ?>><?= date('F', mktime(0, 0, 0, $m, 1)) ?></option>
                        <?php endfor; ?>
                    </select>
                    <input type="number" name="year" value="<?= e($year) ?>" class="input input-sm w-20" min="2020" max="2099">
                </div>
            </div>
            <div class="pt-4">
                <button type="submit" class="btn-primary btn-sm font-bengali">দেখুন</button>
            </div>
        </form>
    </div>

    <?php if ($report_type === 'daily'): ?>
        <div class="bg-white dark:bg-card border border-border dark:border-border-dark rounded-xl p-6">
            <h2 class="text-sm font-semibold text-text-primary dark:text-text-dark-primary mb-4 font-bengali"><?= e($date) ?> - দৈনিক সারাংশ</h2>
            <?php if (empty($data['summary'])): ?>
                <p class="text-sm text-text-tertiary text-center py-4 font-bengali">এই দিনে কোনো লেনদেন নেই।</p>
            <?php else: ?>
                <table class="w-full">
                    <thead>
                        <tr class="text-xs text-text-tertiary uppercase tracking-wider border-b border-border dark:border-border-dark">
                            <th class="text-left pb-2 font-bengali">পদ্ধতি</th>
                            <th class="text-right pb-2 font-bengali">পেমেন্ট</th>
                            <th class="text-right pb-2 font-bengali">রিফান্ড</th>
                            <th class="text-right pb-2 font-bengali">মোট</th>
                            <th class="text-right pb-2 font-bengali">লেনদেন</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border dark:divide-border-dark">
                        <?php foreach ($data['summary'] as $s): ?>
                            <tr>
                                <td class="py-2 text-sm font-medium font-bengali">
                                    <?php $methodLabels = ['cash'=>'নগদ','bkash'=>'বিকাশ','nagad'=>'নগদ','rocket'=>'রকেট','bank'=>'ব্যাংক','card'=>'কার্ড','other'=>'অন্যান্য']; ?>
                                    <?= $methodLabels[$s['method']] ?? $s['method'] ?>
                                </td>
                                <td class="py-2 text-sm text-right text-green-600">৳<?= number_format($s['payment'], 2) ?></td>
                                <td class="py-2 text-sm text-right text-error-600">-৳<?= number_format($s['refund'], 2) ?></td>
                                <td class="py-2 text-sm text-right font-semibold">৳<?= number_format($s['payment'] - $s['refund'], 2) ?></td>
                                <td class="py-2 text-sm text-right text-text-tertiary"><?= $s['count'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="border-t border-border dark:border-border-dark font-semibold">
                            <td class="pt-2 font-bengali">সর্বমোট</td>
                            <td class="pt-2 text-right text-green-600">৳<?= number_format(array_sum(array_column($data['summary'], 'payment')), 2) ?></td>
                            <td class="pt-2 text-right text-error-600">-৳<?= number_format(array_sum(array_column($data['summary'], 'refund')), 2) ?></td>
                            <td class="pt-2 text-right">৳<?= number_format($data['grand_total'], 2) ?></td>
                            <td class="pt-2 text-right text-text-tertiary"><?= array_sum(array_column($data['summary'], 'count')) ?></td>
                        </tr>
                    </tfoot>
                </table>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="bg-white dark:bg-card border border-border dark:border-border-dark rounded-xl p-6">
            <h2 class="text-sm font-semibold text-text-primary dark:text-text-dark-primary mb-4 font-bengali"><?= date('F Y', mktime(0, 0, 0, $month, 1, $year)) ?> - মাসিক রিপোর্ট</h2>
            <?php if (empty($data['rows'])): ?>
                <p class="text-sm text-text-tertiary text-center py-4 font-bengali">এই মাসে কোনো লেনদেন নেই।</p>
            <?php else: ?>
                <table class="w-full">
                    <thead>
                        <tr class="text-xs text-text-tertiary uppercase tracking-wider border-b border-border dark:border-border-dark">
                            <th class="text-left pb-2 font-bengali">তারিখ</th>
                            <th class="text-left pb-2 font-bengali">পদ্ধতি</th>
                            <th class="text-left pb-2 font-bengali">ধরন</th>
                            <th class="text-right pb-2 font-bengali">পরিমাণ</th>
                            <th class="text-right pb-2 font-bengali">লেনদেন</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border dark:divide-border-dark">
                        <?php foreach ($data['rows'] as $r): ?>
                            <tr>
                                <td class="py-2 text-sm"><?= e($r['date']) ?></td>
                                <td class="py-2 text-sm font-bengali">
                                    <?= $methodLabels[$r['method']] ?? $r['method'] ?>
                                </td>
                                <td class="py-2 text-sm">
                                    <?php if ($r['type'] === 'refund'): ?>
                                        <span class="badge badge-error text-xs font-bengali">রিফান্ড</span>
                                    <?php else: ?>
                                        <span class="badge badge-green text-xs font-bengali">পেমেন্ট</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-2 text-sm text-right font-semibold <?= $r['type'] === 'refund' ? 'text-error-600' : 'text-green-600' ?>">
                                    ৳<?= number_format((float) $r['total'], 2) ?>
                                </td>
                                <td class="py-2 text-sm text-right text-text-tertiary"><?= (int) $r['count'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="border-t border-border dark:border-border-dark font-semibold">
                            <td colspan="2" class="pt-2 font-bengali">সর্বমোট</td>
                            <td class="pt-2"></td>
                            <td class="pt-2 text-right">মোট পেমেন্ট: ৳<?= number_format($data['totals']['payment'] ?? 0, 2) ?><?= ($data['totals']['refund'] ?? 0) > 0 ? ' | রিফান্ড: ৳' . number_format($data['totals']['refund'], 2) : '' ?></td>
                            <td class="pt-2"></td>
                        </tr>
                    </tfoot>
                </table>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<script>
document.querySelector('[name=type]').addEventListener('change', function() {
    document.querySelectorAll('[x-show]').forEach(el => {
        const showCondition = el.getAttribute('x-show');
        if (showCondition.includes('daily') || showCondition.includes('monthly')) {
            const isDaily = showCondition.includes('daily');
            el.style.display = (isDaily && this.value === 'daily') || (!isDaily && this.value === 'monthly') ? '' : 'none';
        }
    });
});
document.querySelector('[name=type]').dispatchEvent(new Event('change'));
</script>