<div class="max-w-8xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-semibold text-text-primary dark:text-text-dark-primary font-bengali">
                অ্যাডমিন প্যানেল
            </h1>
            <p class="text-sm text-text-secondary dark:text-text-dark-secondary font-bengali mt-1">
                সিস্টেম ওভারভিউ — <?= format_date(date('Y-m-d')) ?>
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
        <div class="bg-white dark:bg-card border border-border dark:border-border-dark rounded-xl p-4">
            <p class="text-sm text-text-secondary dark:text-text-dark-secondary font-bengali">মোট ব্যবহারকারী</p>
            <p class="text-2xl font-bold text-text-primary dark:text-text-dark-primary mt-1"><?= (int) ($stats['total_users'] ?? 0) ?></p>
        </div>
        <div class="bg-white dark:bg-card border border-border dark:border-border-dark rounded-xl p-4">
            <p class="text-sm text-text-secondary dark:text-text-dark-secondary font-bengali">মোট শপ</p>
            <p class="text-2xl font-bold text-text-primary dark:text-text-dark-primary mt-1"><?= (int) ($stats['total_shops'] ?? 0) ?></p>
        </div>
        <div class="bg-white dark:bg-card border border-border dark:border-border-dark rounded-xl p-4">
            <p class="text-sm text-text-secondary dark:text-text-dark-secondary font-bengali">মোট অর্ডার</p>
            <p class="text-2xl font-bold text-text-primary dark:text-text-dark-primary mt-1"><?= (int) ($stats['total_orders'] ?? 0) ?></p>
        </div>
        <div class="bg-white dark:bg-card border border-border dark:border-border-dark rounded-xl p-4">
            <p class="text-sm text-text-secondary dark:text-text-dark-secondary font-bengali">মোট গ্রাহক</p>
            <p class="text-2xl font-bold text-text-primary dark:text-text-dark-primary mt-1"><?= (int) ($stats['total_customers'] ?? 0) ?></p>
        </div>
        <div class="bg-white dark:bg-card border border-border dark:border-border-dark rounded-xl p-4">
            <p class="text-sm text-text-secondary dark:text-text-dark-secondary font-bengali">মোট আয়</p>
            <p class="text-2xl font-bold text-text-primary dark:text-text-dark-primary mt-1">৳ <?= number_format((float) ($stats['total_revenue'] ?? 0), 2) ?></p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="bg-white dark:bg-card border border-border dark:border-border-dark rounded-xl p-4">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-sm font-semibold text-text-primary dark:text-text-dark-primary font-bengali">
                    সর্বশেষ ব্যবহারকারী
                </h2>
                <a href="<?= url('admin/users') ?>" class="text-xs text-primary-600 dark:text-primary-400 hover:underline font-bengali">
                    সব দেখুন →
                </a>
            </div>
            <?php if (empty($recent_users)): ?>
                <div class="text-center py-8 text-text-tertiary dark:text-text-dark-tertiary">
                    <p class="text-sm font-bengali">কোনো ব্যবহারকারী নেই</p>
                </div>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($recent_users as $u): ?>
                        <a href="<?= url('admin/users/' . $u['id']) ?>" class="block p-3 hover:bg-surface-secondary dark:hover:bg-surface-dark-secondary rounded-lg transition">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-text-primary dark:text-text-dark-primary"><?= e($u['name'] ?? '—') ?></p>
                                    <p class="text-xs text-text-tertiary dark:text-text-dark-tertiary"><?= e($u['email'] ?? '') ?></p>
                                </div>
                                <span class="text-xs px-2 py-1 rounded-full <?= $u['status'] === 'active' ? 'bg-green-100 dark:bg-green-900/20 text-green-700 dark:text-green-400' : 'bg-red-100 dark:bg-red-900/20 text-red-700 dark:text-red-400' ?>">
                                    <?= e($u['status'] ?? '') ?>
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
                    সর্বশেষ শপ
                </h2>
            </div>
            <?php if (empty($recent_shops)): ?>
                <div class="text-center py-8 text-text-tertiary dark:text-text-dark-tertiary">
                    <p class="text-sm font-bengali">কোনো শপ নেই</p>
                </div>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($recent_shops as $s): ?>
                        <div class="p-3 rounded-lg">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-text-primary dark:text-text-dark-primary"><?= e($s['name'] ?? '—') ?></p>
                                    <p class="text-xs text-text-tertiary dark:text-text-dark-tertiary"><?= e($s['slug'] ?? '') ?></p>
                                </div>
                                <span class="text-xs px-2 py-1 rounded-full <?= $s['status'] === 'active' ? 'bg-green-100 dark:bg-green-900/20 text-green-700 dark:text-green-400' : 'bg-red-100 dark:bg-red-900/20 text-red-700 dark:text-red-400' ?>">
                                    <?= e($s['status'] ?? '') ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>