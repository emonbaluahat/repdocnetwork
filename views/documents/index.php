<div class="max-w-8xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-semibold text-text-primary dark:text-text-dark-primary font-bengali">ডকুমেন্ট</h1>
            <p class="text-sm text-text-secondary dark:text-text-dark-secondary mt-1 font-bengali">মোট <?= e($total) ?> টি ডকুমেন্ট</p>
        </div>
        <a href="<?= url('documents/create') ?>" class="btn-primary btn-sm font-bengali">+ নতুন ডকুমেন্ট</a>
    </div>

    <div class="bg-white dark:bg-card border border-border dark:border-border-dark rounded-xl overflow-hidden">
        <div class="p-4 border-b border-border dark:border-border-dark">
            <form method="GET" action="<?= url('documents') ?>" class="flex items-center gap-3">
                <div class="flex-1">
                    <input type="text" name="search" value="<?= e($search) ?>"
                           class="input" placeholder="ডকুমেন্ট নং বা গ্রাহকের নাম...">
                </div>
                <select name="status" class="input input-sm w-32">
                    <option value="">সব স্ট্যাটাস</option>
                    <?php foreach ($statuses as $k => $v): ?>
                        <option value="<?= $k ?>" <?= $status === $k ? 'selected' : '' ?>><?= e($v) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn-primary btn-sm font-bengali">সার্চ</button>
                <?php if ($search || $status): ?>
                    <a href="<?= url('documents') ?>" class="btn-ghost btn-sm font-bengali">ক্লিয়ার</a>
                <?php endif; ?>
            </form>
        </div>

        <?php if (empty($documents)): ?>
            <div class="p-8">
                <?php \App\Core\View::component('empty-state', [
                    'title' => $search ? 'কোনো ডকুমেন্ট পাওয়া যায়নি' : 'কোনো ডকুমেন্ট নেই',
                    'description' => $search ? 'অন্য কিছু সার্চ করুন' : 'প্রথম ডকুমেন্ট তৈরি করুন',
                    'action_url' => $search ? null : url('documents/create'),
                    'action_label' => $search ? null : '+ নতুন ডকুমেন্ট',
                ]); ?>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-surface-secondary dark:bg-surface-dark-secondary">
                            <th class="text-left px-4 py-3 text-xs font-medium text-text-tertiary uppercase tracking-wider font-bengali">ডকুমেন্ট নং</th>
                            <th class="text-left px-4 py-3 text-xs font-medium text-text-tertiary uppercase tracking-wider font-bengali">গ্রাহক</th>
                            <th class="text-left px-4 py-3 text-xs font-medium text-text-tertiary uppercase tracking-wider hidden sm:table-cell font-bengali">টেমপ্লেট</th>
                            <th class="text-center px-4 py-3 text-xs font-medium text-text-tertiary uppercase tracking-wider font-bengali">স্ট্যাটাস</th>
                            <th class="text-right px-4 py-3 text-xs font-medium text-text-tertiary uppercase tracking-wider hidden md:table-cell font-bengali">তারিখ</th>
                            <th class="text-right px-4 py-3 text-xs font-medium text-text-tertiary uppercase tracking-wider font-bengali">অ্যাকশন</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border dark:divide-border-dark">
                        <?php foreach ($documents as $d): ?>
                            <tr class="hover:bg-surface-secondary dark:hover:bg-surface-dark-secondary transition">
                                <td class="px-4 py-3">
                                    <a href="<?= url('documents/' . $d['id']) ?>" class="text-sm font-mono font-medium text-primary-600 dark:text-primary-400 hover:underline">
                                        <?= e($d['document_number']) ?>
                                    </a>
                                </td>
                                <td class="px-4 py-3 text-sm text-text-primary dark:text-text-dark-primary"><?= e($d['customer_name'] ?? '—') ?></td>
                                <td class="px-4 py-3 text-sm text-text-secondary dark:text-text-dark-secondary hidden sm:table-cell"><?= e($d['template_name'] ?? '—') ?></td>
                                <td class="px-4 py-3 text-center">
                                    <span class="badge <?= $d['status'] === 'generated' ? 'badge-success' : ($d['status'] === 'voided' ? 'badge-error' : 'badge-warning') ?> text-xs">
                                        <?= e($statuses[$d['status']] ?? $d['status']) ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-text-tertiary dark:text-text-dark-tertiary text-right hidden md:table-cell">
                                    <?= e(format_date($d['created_at'])) ?>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="<?= url('documents/' . $d['id']) ?>" class="btn-ghost btn-sm font-bengali">দেখুন</a>
                                        <?php if ($d['generated_file']): ?>
                                            <a href="<?= url('documents/' . $d['id'] . '/pdf') ?>" target="_blank" class="btn-ghost btn-sm font-bengali">পিডিএফ</a>
                                        <?php endif; ?>
                                    </div>
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
                        'base_url' => url('documents'),
                        'query' => array_filter(['search' => $search, 'status' => $status]),
                    ]); ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
