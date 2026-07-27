<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <a href="<?= url('customers') ?>" class="text-sm text-text-secondary dark:text-text-dark-secondary hover:text-text-primary dark:hover:text-text-dark-primary transition font-bengali mb-2 inline-block">
                ← গ্রাহক তালিকা
            </a>
            <h1 class="text-xl font-semibold text-text-primary dark:text-text-dark-primary font-bengali">
                <?= e($customer['name']) ?>
            </h1>
        </div>
        <div class="flex items-center gap-2">
            <a href="<?= url('customers/' . $customer['id'] . '/edit') ?>"
               class="btn-secondary btn-sm font-bengali">সম্পাদনা</a>
            <form method="POST" action="<?= url('customers/' . $customer['id'] . '/delete') ?>"
                  onsubmit="return confirm('<?= __('customer.delete_confirm') ?>')">
                <?= csrf_field() ?>
                <button type="submit" class="btn-ghost btn-sm text-error-600 dark:text-error-400 font-bengali">মুছুন</button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white dark:bg-card border border-border dark:border-border-dark rounded-xl p-6">
                <h2 class="text-sm font-semibold text-text-primary dark:text-text-dark-primary mb-4 font-bengali">গ্রাহক তথ্য</h2>
                <dl class="space-y-3">
                    <div class="flex justify-between">
                        <dt class="text-sm text-text-secondary dark:text-text-dark-secondary font-bengali">নাম</dt>
                        <dd class="text-sm text-text-primary dark:text-text-dark-primary font-medium"><?= e($customer['name']) ?></dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-text-secondary dark:text-text-dark-secondary font-bengali">ফোন</dt>
                        <dd class="text-sm text-text-primary dark:text-text-dark-primary font-medium"><?= e($customer['phone']) ?></dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-text-secondary dark:text-text-dark-secondary font-bengali">ইমেইল</dt>
                        <dd class="text-sm text-text-primary dark:text-text-dark-primary font-medium"><?= e($customer['email'] ?? '—') ?></dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-text-secondary dark:text-text-dark-secondary font-bengali">এনআইডি</dt>
                        <dd class="text-sm font-mono text-text-primary dark:text-text-dark-primary"><?= e($customer['nid'] ?? '—') ?></dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-text-secondary dark:text-text-dark-secondary font-bengali">ঠিকানা</dt>
                        <dd class="text-sm text-text-primary dark:text-text-dark-primary text-right max-w-xs"><?= e($customer['address'] ?? '—') ?></dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-text-secondary dark:text-text-dark-secondary font-bengali">ট্যাগ</dt>
                        <dd class="text-sm">
                            <?php if ($customer['tags']): ?>
                                <div class="flex gap-1 flex-wrap justify-end">
                                    <?php foreach (json_decode($customer['tags'], true) ?? [] as $tag): ?>
                                        <span class="badge badge-primary"><?= e($tag) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <span class="text-text-tertiary">—</span>
                            <?php endif; ?>
                        </dd>
                    </div>
                    <?php if ($customer['notes']): ?>
                        <div class="pt-3 border-t border-border dark:border-border-dark">
                            <dt class="text-sm text-text-secondary dark:text-text-dark-secondary mb-1 font-bengali">নোট</dt>
                            <dd class="text-sm text-text-primary dark:text-text-dark-primary bg-surface-secondary dark:bg-surface-dark-secondary rounded-lg p-3"><?= e($customer['notes']) ?></dd>
                        </div>
                    <?php endif; ?>
                </dl>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white dark:bg-card border border-border dark:border-border-dark rounded-xl p-4">
                <h3 class="text-xs font-semibold text-text-tertiary dark:text-text-dark-tertiary uppercase tracking-wider mb-3 font-bengali">পরিসংখ্যান</h3>
                <div class="space-y-3">
                    <div>
                        <p class="text-2xl font-bold text-text-primary dark:text-text-dark-primary">—</p>
                        <p class="text-xs text-text-secondary dark:text-text-dark-secondary font-bengali">ডকুমেন্ট</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-text-primary dark:text-text-dark-primary">—</p>
                        <p class="text-xs text-text-secondary dark:text-text-dark-secondary font-bengali">অর্ডার</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-card border border-border dark:border-border-dark rounded-xl p-4">
                <h3 class="text-xs font-semibold text-text-tertiary dark:text-text-dark-tertiary uppercase tracking-wider mb-3 font-bengali">তৈরির তারিখ</h3>
                <p class="text-sm text-text-primary dark:text-text-dark-primary"><?= e(format_datetime($customer['created_at'])) ?></p>
                <p class="text-xs text-text-secondary dark:text-text-dark-secondary mt-1 font-bengali">
                    <?= e($customer['updated_at'] !== $customer['created_at'] ? 'সর্বশেষ আপডেট: ' . format_datetime($customer['updated_at']) : '') ?>
                </p>
            </div>
        </div>
    </div>

    <div class="mt-6 bg-white dark:bg-card border border-border dark:border-border-dark rounded-xl p-6">
        <h2 class="text-sm font-semibold text-text-primary dark:text-text-dark-primary mb-4 font-bengali">কার্যকলাপ</h2>
        <?php if (empty($timeline)): ?>
            <div class="text-center py-6 text-text-tertiary dark:text-text-dark-tertiary">
                <p class="text-sm font-bengali">কোনো কার্যকলাপ নেই</p>
            </div>
        <?php else: ?>
            <div class="space-y-3">
                <?php foreach ($timeline as $entry): ?>
                    <div class="flex items-start gap-3 pb-3 border-b border-border dark:border-border-dark last:border-0">
                        <div class="w-2 h-2 mt-2 rounded-full bg-primary-400 dark:bg-primary-600 flex-shrink-0"></div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-text-primary dark:text-text-dark-primary"><?= e($entry['description'] ?? $entry['action']) ?></p>
                            <div class="flex items-center gap-2 mt-0.5">
                                <span class="text-xs text-text-tertiary dark:text-text-dark-tertiary"><?= e(format_datetime($entry['created_at'])) ?></span>
                                <?php if ($entry['user_name']): ?>
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