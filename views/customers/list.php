<div class="max-w-8xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-semibold text-text-primary dark:text-text-dark-primary font-bengali">গ্রাহক</h1>
            <p class="text-sm text-text-secondary dark:text-text-dark-secondary mt-1 font-bengali">মোট <?= e($total) ?> জন গ্রাহক</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="<?= url('customers/import') ?>"
               class="btn-secondary btn-sm font-bengali">ইম্পোর্ট</a>
            <a href="<?= url('customers/export') ?>"
               class="btn-secondary btn-sm font-bengali">এক্সপোর্ট</a>
            <a href="<?= url('customers/create') ?>"
               class="btn-primary btn-sm font-bengali">+ নতুন গ্রাহক</a>
        </div>
    </div>

    <div class="bg-white dark:bg-card border border-border dark:border-border-dark rounded-xl overflow-hidden">
        <div class="p-4 border-b border-border dark:border-border-dark">
            <form method="GET" action="<?= url('customers') ?>" class="flex items-center gap-3">
                <div class="flex-1">
                    <input type="text" name="search" value="<?= e($search) ?>"
                           class="input" placeholder="নাম, ফোন, ইমেইল বা এনআইডি দ্বারা সার্চ করুন..."
                           data-command-palette>
                </div>
                <button type="submit" class="btn-primary btn-sm font-bengali">সার্চ</button>
                <?php if ($search || $tag): ?>
                    <a href="<?= url('customers') ?>" class="btn-ghost btn-sm font-bengali">ক্লিয়ার</a>
                <?php endif; ?>
            </form>
        </div>

        <?php if (empty($customers)): ?>
            <div class="p-8">
                <?php \App\Core\View::component('empty-state', [
                    'title' => $search ? __('customer.no_results') : __('customer.no_customers'),
                    'description' => $search ? __('customer.try_different_search') : __('customer.add_first_customer'),
                    'action_url' => $search ? null : url('customers/create'),
                    'action_label' => $search ? null : __('customer.add_customer_btn'),
                ]); ?>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-surface-secondary dark:bg-surface-dark-secondary">
                            <th class="text-left px-4 py-3 text-xs font-medium text-text-tertiary dark:text-text-dark-tertiary uppercase tracking-wider">নাম</th>
                            <th class="text-left px-4 py-3 text-xs font-medium text-text-tertiary dark:text-text-dark-tertiary uppercase tracking-wider">ফোন</th>
                            <th class="text-left px-4 py-3 text-xs font-medium text-text-tertiary dark:text-text-dark-tertiary uppercase tracking-wider hidden sm:table-cell">ইমেইল</th>
                            <th class="text-left px-4 py-3 text-xs font-medium text-text-tertiary dark:text-text-dark-tertiary uppercase tracking-wider hidden lg:table-cell">এনআইডি</th>
                            <th class="text-left px-4 py-3 text-xs font-medium text-text-tertiary dark:text-text-dark-tertiary uppercase tracking-wider hidden md:table-cell">ট্যাগ</th>
                            <th class="text-right px-4 py-3 text-xs font-medium text-text-tertiary dark:text-text-dark-tertiary uppercase tracking-wider">তারিখ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border dark:divide-border-dark">
                        <?php foreach ($customers as $c): ?>
                            <tr class="hover:bg-surface-secondary dark:hover:bg-surface-dark-secondary transition cursor-pointer"
                                onclick="window.location='<?= url('customers/' . $c['id']) ?>'">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 bg-primary-100 dark:bg-primary-900/30 rounded-full flex items-center justify-center text-xs font-semibold text-primary-700 dark:text-primary-400 flex-shrink-0">
                                            <?= e(mb_substr($c['name'], 0, 1, 'UTF-8')) ?>
                                        </div>
                                        <span class="text-sm font-medium text-text-primary dark:text-text-dark-primary font-bengali">
                                            <?= e($c['name']) ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm text-text-secondary dark:text-text-dark-secondary"><?= e($c['phone']) ?></td>
                                <td class="px-4 py-3 text-sm text-text-secondary dark:text-text-dark-secondary hidden sm:table-cell"><?= e($c['email'] ?? '—') ?></td>
                                <td class="px-4 py-3 text-sm font-mono text-text-secondary dark:text-text-dark-secondary hidden lg:table-cell"><?= e($c['nid'] ?? '—') ?></td>
                                <td class="px-4 py-3 hidden md:table-cell">
                                    <?php if ($c['tags']): ?>
                                        <?php foreach (json_decode($c['tags'], true) ?? [] as $tag): ?>
                                            <span class="badge badge-primary mr-1"><?= e($tag) ?></span>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span class="text-sm text-text-tertiary">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-sm text-text-tertiary dark:text-text-dark-tertiary text-right whitespace-nowrap">
                                    <?= e(format_date($c['created_at'])) ?>
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
                        'base_url' => url('customers'),
                        'query' => array_filter(['search' => $search, 'tag' => $tag]),
                    ]); ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>