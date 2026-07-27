<div class="max-w-8xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-semibold text-text-primary dark:text-text-dark-primary font-bengali">অর্ডার</h1>
            <p class="text-sm text-text-secondary dark:text-text-dark-secondary mt-1 font-bengali">মোট <?= e($total) ?> টি অর্ডার</p>
        </div>
        <a href="<?= url('orders/create') ?>" class="btn-primary btn-sm font-bengali">+ নতুন অর্ডার</a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-card border border-border dark:border-border-dark rounded-xl p-4">
            <p class="text-sm text-text-secondary dark:text-text-dark-secondary font-bengali">আজকের অর্ডার</p>
            <p class="text-2xl font-bold text-text-primary dark:text-text-dark-primary mt-1"><?= (int) ($today_stats['count'] ?? 0) ?></p>
            <p class="text-xs text-text-tertiary dark:text-text-dark-tertiary mt-1 font-bengali">আয়: ৳<?= number_format((float) ($today_stats['revenue'] ?? 0), 2) ?></p>
        </div>
        <div class="bg-white dark:bg-card border border-border dark:border-border-dark rounded-xl p-4">
            <p class="text-sm text-text-secondary dark:text-text-dark-secondary font-bengali">মাসিক অর্ডার</p>
            <p class="text-2xl font-bold text-text-primary dark:text-text-dark-primary mt-1"><?= (int) ($monthly_stats['count'] ?? 0) ?></p>
            <p class="text-xs text-text-tertiary dark:text-text-dark-tertiary mt-1 font-bengali">আয়: ৳<?= number_format((float) ($monthly_stats['revenue'] ?? 0), 2) ?></p>
        </div>
        <div class="bg-white dark:bg-card border border-border dark:border-border-dark rounded-xl p-4">
            <p class="text-sm text-text-secondary dark:text-text-dark-secondary font-bengali">পেন্ডিং</p>
            <p class="text-2xl font-bold text-warning-600 dark:text-warning-400 mt-1">—</p>
        </div>
        <div class="bg-white dark:bg-card border border-border dark:border-border-dark rounded-xl p-4">
            <p class="text-sm text-text-secondary dark:text-text-dark-secondary font-bengali">প্রক্রিয়াধীন</p>
            <p class="text-2xl font-bold text-primary-600 dark:text-primary-400 mt-1">—</p>
        </div>
    </div>

    <div class="bg-white dark:bg-card border border-border dark:border-border-dark rounded-xl overflow-hidden">
        <div class="p-4 border-b border-border dark:border-border-dark">
            <form method="GET" action="<?= url('orders') ?>" class="flex items-center gap-3 flex-wrap">
                <div class="flex-1 min-w-[200px]">
                    <input type="text" name="search" value="<?= e($search) ?>"
                           class="input" placeholder="রেফারেন্স, গ্রাহকের নাম বা ফোন...">
                </div>
                <select name="status" class="input input-sm w-32">
                    <option value="">সব স্ট্যাটাস</option>
                    <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>পেন্ডিং</option>
                    <option value="confirmed" <?= $status === 'confirmed' ? 'selected' : '' ?>>নিশ্চিত</option>
                    <option value="in_progress" <?= $status === 'in_progress' ? 'selected' : '' ?>>প্রক্রিয়াধীন</option>
                    <option value="ready" <?= $status === 'ready' ? 'selected' : '' ?>>প্রস্তুত</option>
                    <option value="completed" <?= $status === 'completed' ? 'selected' : '' ?>>সম্পন্ন</option>
                    <option value="cancelled" <?= $status === 'cancelled' ? 'selected' : '' ?>>বাতিল</option>
                    <option value="delivered" <?= $status === 'delivered' ? 'selected' : '' ?>>ডেলিভারি</option>
                </select>
                <input type="date" name="date_from" value="<?= e($date_from) ?>" class="input input-sm w-36" placeholder="তারিখ থেকে">
                <input type="date" name="date_to" value="<?= e($date_to) ?>" class="input input-sm w-36" placeholder="তারিখ পর্যন্ত">
                <button type="submit" class="btn-primary btn-sm font-bengali">সার্চ</button>
                <?php if ($search || $status || $date_from || $date_to): ?>
                    <a href="<?= url('orders') ?>" class="btn-ghost btn-sm font-bengali">ক্লিয়ার</a>
                <?php endif; ?>
            </form>
        </div>

        <?php if (empty($orders)): ?>
            <div class="p-8">
                <div class="text-center py-8 text-text-tertiary dark:text-text-dark-tertiary">
                    <p class="text-sm font-bengali"><?= $search ? 'কোনো ফলাফল পাওয়া যায়নি।' : 'কোনো অর্ডার নেই।' ?></p>
                    <?php if (!$search): ?>
                        <a href="<?= url('orders/create') ?>" class="btn-primary btn-sm mt-3 inline-block font-bengali">+ নতুন অর্ডার</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-surface-secondary dark:bg-surface-dark-secondary">
                            <th class="text-left px-4 py-3 text-xs font-medium text-text-tertiary uppercase tracking-wider font-bengali">রেফারেন্স</th>
                            <th class="text-left px-4 py-3 text-xs font-medium text-text-tertiary uppercase tracking-wider font-bengali">গ্রাহক</th>
                            <th class="text-center px-4 py-3 text-xs font-medium text-text-tertiary uppercase tracking-wider font-bengali">স্ট্যাটাস</th>
                            <th class="text-right px-4 py-3 text-xs font-medium text-text-tertiary uppercase tracking-wider font-bengali">মোট</th>
                            <th class="text-right px-4 py-3 text-xs font-medium text-text-tertiary uppercase tracking-wider hidden md:table-cell font-bengali">বকেয়া</th>
                            <th class="text-right px-4 py-3 text-xs font-medium text-text-tertiary uppercase tracking-wider hidden lg:table-cell font-bengali">তারিখ</th>
                            <th class="text-right px-4 py-3 font-bengali"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border dark:divide-border-dark">
                        <?php foreach ($orders as $o): ?>
                            <tr class="hover:bg-surface-secondary dark:hover:bg-surface-dark-secondary transition cursor-pointer"
                                onclick="window.location='<?= url('orders/' . $o['id']) ?>'">
                                <td class="px-4 py-3">
                                    <span class="text-sm font-mono font-medium text-primary-700 dark:text-primary-400"><?= e($o['reference']) ?></span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-sm font-medium text-text-primary dark:text-text-dark-primary"><?= e($o['customer_name'] ?? '—') ?></span>
                                    <p class="text-xs text-text-tertiary dark:text-text-dark-tertiary"><?= e($o['customer_phone'] ?? '') ?></p>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <?php
                                    $badgeStyles = ['pending' => 'badge-warning', 'confirmed' => 'badge-info', 'in_progress' => 'badge-primary', 'ready' => 'badge-success', 'completed' => 'badge-green', 'cancelled' => 'badge-error', 'delivered' => 'badge-green'];
                                    $badgeClass = $badgeStyles[$o['status']] ?? 'badge-default';
                                    $statusLabels = ['pending' => 'পেন্ডিং', 'confirmed' => 'নিশ্চিত', 'in_progress' => 'প্রক্রিয়াধীন', 'ready' => 'প্রস্তুত', 'completed' => 'সম্পন্ন', 'cancelled' => 'বাতিল', 'delivered' => 'ডেলিভারি'];
                                    ?>
                                    <span class="badge <?= $badgeClass ?> text-xs"><?= $statusLabels[$o['status']] ?? $o['status'] ?></span>
                                </td>
                                <td class="px-4 py-3 text-right text-sm font-semibold text-text-primary dark:text-text-dark-primary">
                                    ৳ <?= number_format((float) ($o['amount'] ?? 0), 2) ?>
                                </td>
                                <td class="px-4 py-3 text-right text-sm hidden md:table-cell <?= (float) ($o['due_amount'] ?? 0) > 0 ? 'text-error-600 dark:text-error-400 font-semibold' : 'text-green-600 dark:text-green-400' ?>">
                                    ৳ <?= number_format((float) ($o['due_amount'] ?? 0), 2) ?>
                                </td>
                                <td class="px-4 py-3 text-right text-sm text-text-tertiary dark:text-text-dark-tertiary hidden lg:table-cell whitespace-nowrap">
                                    <?= format_date($o['created_at']) ?>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="<?= url('orders/' . $o['id']) ?>" class="btn-ghost btn-sm"
                                       onclick="event.stopPropagation()">বিস্তারিত</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($total_pages > 1): ?>
                <div class="p-4 border-t border-border dark:border-border-dark">
                    <?php \App\Core\View::component('pagination', [
                        'current_page' => $page,
                        'total_pages' => $total_pages,
                        'has_prev' => $has_prev,
                        'has_next' => $has_next,
                        'base_url' => url('orders'),
                        'query' => array_filter(['search' => $search, 'status' => $status, 'date_from' => $date_from, 'date_to' => $date_to]),
                    ]); ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>