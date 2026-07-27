<div class="max-w-8xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-semibold text-text-primary dark:text-text-dark-primary font-bengali">অডিট লগ</h1>
    </div>

    <div class="bg-white dark:bg-card border border-border dark:border-border-dark rounded-xl overflow-hidden">
        <div class="p-4 border-b border-border dark:border-border-dark">
            <form method="GET" class="flex gap-3 flex-wrap">
                <input type="text" name="action" value="<?= e($filters['action'] ?? '') ?>"
                       class="px-3 py-2 border border-border dark:border-border-dark rounded-lg bg-white dark:bg-card text-text-primary dark:text-text-dark-primary placeholder-text-tertiary focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition text-sm"
                       placeholder="অ্যাকশন">
                <input type="text" name="entity_type" value="<?= e($filters['entity_type'] ?? '') ?>"
                       class="px-3 py-2 border border-border dark:border-border-dark rounded-lg bg-white dark:bg-card text-text-primary dark:text-text-dark-primary placeholder-text-tertiary focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition text-sm"
                       placeholder="এন্টিটি টাইপ">
                <input type="date" name="date_from" value="<?= e($filters['date_from'] ?? '') ?>"
                       class="px-3 py-2 border border-border dark:border-border-dark rounded-lg bg-white dark:bg-card text-text-primary dark:text-text-dark-primary focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition text-sm">
                <input type="date" name="date_to" value="<?= e($filters['date_to'] ?? '') ?>"
                       class="px-3 py-2 border border-border dark:border-border-dark rounded-lg bg-white dark:bg-card text-text-primary dark:text-text-dark-primary focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition text-sm">
                <button type="submit" class="btn-primary btn-sm font-bengali">ফিল্টার</button>
                <a href="<?= url('admin/audit-logs') ?>" class="btn-secondary btn-sm font-bengali">রিসেট</a>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-border dark:border-border-dark bg-surface-secondary dark:bg-surface-dark-secondary">
                        <th class="text-left px-4 py-3 font-medium text-text-secondary dark:text-text-dark-secondary">সময়</th>
                        <th class="text-left px-4 py-3 font-medium text-text-secondary dark:text-text-dark-secondary">ব্যবহারকারী</th>
                        <th class="text-left px-4 py-3 font-medium text-text-secondary dark:text-text-dark-secondary">অ্যাকশন</th>
                        <th class="text-left px-4 py-3 font-medium text-text-secondary dark:text-text-dark-secondary">এন্টিটি</th>
                        <th class="text-left px-4 py-3 font-medium text-text-secondary dark:text-text-dark-secondary">আইপি</th>
                        <th class="text-left px-4 py-3 font-medium text-text-secondary dark:text-text-dark-secondary">বিস্তারিত</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-8 text-text-tertiary dark:text-text-dark-tertiary font-bengali">
                                কোনো লগ পাওয়া যায়নি
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <tr class="border-b border-border dark:border-border-dark hover:bg-surface-secondary dark:hover:bg-surface-dark-secondary transition">
                                <td class="px-4 py-3 text-xs text-text-tertiary dark:text-text-dark-tertiary whitespace-nowrap">
                                    <?= format_datetime($log['created_at']) ?>
                                </td>
                                <td class="px-4 py-3 text-sm text-text-primary dark:text-text-dark-primary">
                                    <?= e($log['user_name'] ?? 'সিস্টেম') ?>
                                </td>
                                <td class="px-4 py-3">
                                    <code class="text-xs bg-gray-100 dark:bg-gray-800 px-1.5 py-0.5 rounded text-text-primary dark:text-text-dark-primary">
                                        <?= e($log['action']) ?>
                                    </code>
                                </td>
                                <td class="px-4 py-3 text-sm text-text-secondary dark:text-text-dark-secondary">
                                    <?= e($log['entity_type'] ?? '-') ?>
                                    <?php if ($log['entity_id']): ?>#<?= $log['entity_id'] ?><?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-xs text-text-tertiary dark:text-text-dark-tertiary font-mono">
                                    <?= e($log['ip_address'] ?? '-') ?>
                                </td>
                                <td class="px-4 py-3 text-xs text-text-tertiary dark:text-text-dark-tertiary max-w-xs truncate">
                                    <?php if ($log['old_data']): ?>পুরনো: <?= e($log['old_data']) ?><?php endif; ?>
                                    <?php if ($log['new_data']): ?>নতুন: <?= e($log['new_data']) ?><?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if (($total_pages ?? 1) > 1): ?>
            <div class="flex items-center justify-between px-4 py-3 border-t border-border dark:border-border-dark">
                <p class="text-sm text-text-tertiary dark:text-text-dark-tertiary">Showing <?= (($page - 1) * $perPage + 1) ?> - <?= min($page * $perPage, $total) ?> of <?= $total ?></p>
                <div class="flex gap-2">
                    <?php if ($page > 1): ?>
                        <a href="<?= url('admin/audit-logs?page=' . ($page - 1)) ?>" class="btn-secondary btn-sm">Previous</a>
                    <?php endif; ?>
                    <?php if ($page < $total_pages): ?>
                        <a href="<?= url('admin/audit-logs?page=' . ($page + 1)) ?>" class="btn-secondary btn-sm">Next</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
