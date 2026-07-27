<div class="max-w-8xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-semibold text-text-primary dark:text-text-dark-primary font-bengali">
                ড্যাশবোর্ড
            </h1>
            <p class="text-sm text-text-secondary dark:text-text-dark-secondary font-bengali mt-1">
                <?= e($shop['name'] ?? '') ?> — <?= format_date(date('Y-m-d')) ?>
            </p>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-xs px-2 py-1 bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400 rounded-full font-bengali">
                <?= e(__('role.' . ($role ?? 'owner'))) ?>
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white dark:bg-card border border-border dark:border-border-dark rounded-xl p-4">
            <p class="text-sm text-text-secondary dark:text-text-dark-secondary font-bengali"><?= __('dashboard.today_orders') ?></p>
            <p class="text-2xl font-bold text-text-primary dark:text-text-dark-primary mt-1"><?= (int) ($stats['today_orders'] ?? 0) ?></p>
            <p class="text-xs text-text-tertiary dark:text-text-dark-tertiary mt-1 font-bengali"><?= __('dashboard.today_orders_desc') ?></p>
        </div>
        <div class="bg-white dark:bg-card border border-border dark:border-border-dark rounded-xl p-4">
            <p class="text-sm text-text-secondary dark:text-text-dark-secondary font-bengali"><?= __('dashboard.total_customers') ?></p>
            <p class="text-2xl font-bold text-text-primary dark:text-text-dark-primary mt-1"><?= (int) ($stats['total_customers'] ?? 0) ?></p>
            <p class="text-xs text-text-tertiary dark:text-text-dark-tertiary mt-1 font-bengali"><?= __('dashboard.total_customers_desc') ?></p>
        </div>
        <div class="bg-white dark:bg-card border border-border dark:border-border-dark rounded-xl p-4">
            <p class="text-sm text-text-secondary dark:text-text-dark-secondary font-bengali"><?= __('dashboard.pending_orders') ?></p>
            <p class="text-2xl font-bold text-text-primary dark:text-text-dark-primary mt-1"><?= (int) ($stats['pending_orders'] ?? 0) ?></p>
            <p class="text-xs text-text-tertiary dark:text-text-dark-tertiary mt-1 font-bengali"><?= __('dashboard.pending_orders_desc') ?></p>
        </div>
        <div class="bg-white dark:bg-card border border-border dark:border-border-dark rounded-xl p-4">
            <p class="text-sm text-text-secondary dark:text-text-dark-secondary font-bengali"><?= __('dashboard.monthly_revenue') ?></p>
            <p class="text-2xl font-bold text-text-primary dark:text-text-dark-primary mt-1">৳ <?= number_format((float) ($stats['monthly_revenue'] ?? 0), 2) ?></p>
            <p class="text-xs text-text-tertiary dark:text-text-dark-tertiary mt-1 font-bengali"><?= __('dashboard.monthly_revenue_desc') ?></p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="bg-white dark:bg-card border border-border dark:border-border-dark rounded-xl p-4">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-sm font-semibold text-text-primary dark:text-text-dark-primary font-bengali">
                    <?= __('dashboard.recent_orders') ?>
                </h2>
                <a href="<?= url('orders') ?>" class="text-xs text-primary-600 dark:text-primary-400 hover:underline font-bengali">
                    সব দেখুন →
                </a>
            </div>
            <?php if (empty($recent_orders)): ?>
                <div class="text-center py-8 text-text-tertiary dark:text-text-dark-tertiary">
                    <p class="text-sm font-bengali"><?= __('empty.no_orders') ?></p>
                </div>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($recent_orders as $order): ?>
                        <a href="<?= url('orders/' . $order['id']) ?>" class="block p-3 hover:bg-surface-secondary dark:hover:bg-surface-dark-secondary rounded-lg transition">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="badge badge-primary text-xs"><?= e($order['reference'] ?? '') ?></span>
                                    <span class="text-sm font-medium text-text-primary dark:text-text-dark-primary"><?= e($order['customer_name'] ?? '—') ?></span>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-semibold text-text-primary dark:text-text-dark-primary">৳ <?= number_format((float) ($order['paid_amount'] ?? 0), 2) ?></p>
                                    <p class="text-xs text-text-tertiary dark:text-text-dark-tertiary"><?= format_date($order['created_at'] ?? '') ?></p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 mt-1">
                                <?php
                                $statusMap = ['pending' => 'warning', 'confirmed' => 'info', 'in_progress' => 'primary', 'ready' => 'success', 'completed' => 'green', 'cancelled' => 'error', 'delivered' => 'green'];
                                $badgeClass = $statusMap[$order['status'] ?? 'pending'] ?? 'default';
                                ?>
                                <span class="badge badge-<?= $badgeClass ?> text-xs">
                                    <?= e(__('order.' . ($order['status'] ?? 'pending'))) ?>
                                </span>
                                <span class="text-xs text-text-tertiary dark:text-text-dark-tertiary">
                                    <?= e($order['customer_phone'] ?? '') ?>
                                </span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="bg-white dark:bg-card border border-border dark:border-border-dark rounded-xl p-4">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-sm font-semibold text-text-primary dark:text-text-dark-primary font-bengali">
                    <?= __('dashboard.recent_payments') ?>
                </h2>
                <a href="<?= url('transactions') ?>" class="text-xs text-primary-600 dark:text-primary-400 hover:underline font-bengali">
                    সব দেখুন →
                </a>
            </div>
            <?php if (empty($recent_transactions)): ?>
                <div class="text-center py-8 text-text-tertiary dark:text-text-dark-tertiary">
                    <p class="text-sm font-bengali"><?= __('empty.no_transactions') ?></p>
                </div>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($recent_transactions as $txn): ?>
                        <div class="p-3 hover:bg-surface-secondary dark:hover:bg-surface-dark-secondary rounded-lg transition">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="badge badge-green text-xs"><?= e(__('transaction.method.' . ($txn['method'] ?? 'cash'))) ?></span>
                                    <span class="text-sm font-medium text-text-primary dark:text-text-dark-primary"><?= e($txn['customer_name'] ?? 'Walk-in') ?></span>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-semibold text-green-600 dark:text-green-400">৳ <?= number_format((float) ($txn['amount'] ?? 0), 2) ?></p>
                                    <p class="text-xs text-text-tertiary dark:text-text-dark-tertiary"><?= format_date($txn['created_at'] ?? '') ?></p>
                                </div>
                            </div>
                            <?php if (!empty($txn['order_ref'])): ?>
                                <p class="text-xs text-text-tertiary dark:text-text-dark-tertiary mt-1">
                                    অর্ডার: <?= e($txn['order_ref']) ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="bg-white dark:bg-card border border-border dark:border-border-dark rounded-xl p-4">
        <h2 class="text-sm font-semibold text-text-primary dark:text-text-dark-primary mb-3 font-bengali">
            <?= __('dashboard.quick_actions') ?>
        </h2>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <a href="<?= url('customers/create') ?>" class="btn-secondary text-center py-3 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition font-bengali">
                <svg class="w-6 h-6 mx-auto mb-1 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
                <div><?= __('dashboard.new_customer') ?></div>
            </a>
            <a href="<?= url('orders/create') ?>" class="btn-secondary text-center py-3 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition font-bengali">
                <svg class="w-6 h-6 mx-auto mb-1 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                <div><?= __('dashboard.new_order') ?></div>
            </a>
            <a href="<?= url('services/create') ?>" class="btn-secondary text-center py-3 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition font-bengali">
                <svg class="w-6 h-6 mx-auto mb-1 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
                <div><?= __('dashboard.new_service') ?></div>
            </a>
            <a href="<?= url('transactions') ?>" class="btn-secondary text-center py-3 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition font-bengali">
                <svg class="w-6 h-6 mx-auto mb-1 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div><?= __('dashboard.record_payment') ?></div>
            </a>
        </div>
    </div>
</div>