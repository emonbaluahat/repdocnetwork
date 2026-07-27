<div class="max-w-8xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-semibold text-text-primary dark:text-text-dark-primary font-bengali">ব্যবহারকারী ব্যবস্থাপনা</h1>
    </div>

    <div class="bg-white dark:bg-card border border-border dark:border-border-dark rounded-xl overflow-hidden">
        <div class="p-4 border-b border-border dark:border-border-dark">
            <form method="GET" class="flex gap-3 flex-wrap">
                <div class="flex-1 min-w-[200px]">
                    <input type="text" name="search" value="<?= e($search ?? '') ?>"
                           class="w-full px-3 py-2 border border-border dark:border-border-dark rounded-lg bg-white dark:bg-card text-text-primary dark:text-text-dark-primary placeholder-text-tertiary focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition text-sm"
                           placeholder="নাম, ইমেইল বা ফোন দ্বারা সার্চ করুন...">
                </div>
                <select name="status" class="px-3 py-2 border border-border dark:border-border-dark rounded-lg bg-white dark:bg-card text-text-primary dark:text-text-dark-primary text-sm">
                    <option value="">সব স্ট্যাটাস</option>
                    <option value="active" <?= ($status ?? '') === 'active' ? 'selected' : '' ?>>সক্রিয়</option>
                    <option value="inactive" <?= ($status ?? '') === 'inactive' ? 'selected' : '' ?>>নিষ্ক্রিয়</option>
                    <option value="blocked" <?= ($status ?? '') === 'blocked' ? 'selected' : '' ?>>ব্লকড</option>
                    <option value="pending" <?= ($status ?? '') === 'pending' ? 'selected' : '' ?>>পেন্ডিং</option>
                </select>
                <button type="submit" class="btn-primary font-bengali">ফিল্টার</button>
                <a href="<?= url('admin/users') ?>" class="btn-secondary font-bengali">রিসেট</a>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-border dark:border-border-dark bg-surface-secondary dark:bg-surface-dark-secondary">
                        <th class="text-left px-4 py-3 font-medium text-text-secondary dark:text-text-dark-secondary font-bengali">নাম</th>
                        <th class="text-left px-4 py-3 font-medium text-text-secondary dark:text-text-dark-secondary font-bengali">ইমেইল</th>
                        <th class="text-left px-4 py-3 font-medium text-text-secondary dark:text-text-dark-secondary font-bengali">ফোন</th>
                        <th class="text-left px-4 py-3 font-medium text-text-secondary dark:text-text-dark-secondary font-bengali">স্ট্যাটাস</th>
                        <th class="text-left px-4 py-3 font-medium text-text-secondary dark:text-text-dark-secondary font-bengali">সুপার অ্যাডমিন</th>
                        <th class="text-left px-4 py-3 font-medium text-text-secondary dark:text-text-dark-secondary font-bengali">শপ রোল</th>
                        <th class="text-left px-4 py-3 font-medium text-text-secondary dark:text-text-dark-secondary font-bengali">নিবন্ধন</th>
                        <th class="text-right px-4 py-3 font-medium text-text-secondary dark:text-text-dark-secondary font-bengali">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-8 text-text-tertiary dark:text-text-dark-tertiary font-bengali">
                                কোনো ব্যবহারকারী পাওয়া যায়নি
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                            <tr class="border-b border-border dark:border-border-dark hover:bg-surface-secondary dark:hover:bg-surface-dark-secondary transition">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 bg-primary-100 dark:bg-primary-900/30 rounded-full flex items-center justify-center text-xs font-semibold text-primary-700 dark:text-primary-400">
                                            <?= e(mb_substr($user['name'], 0, 1, 'UTF-8')) ?>
                                        </div>
                                        <span class="font-medium text-text-primary dark:text-text-dark-primary"><?= e($user['name']) ?></span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-text-secondary dark:text-text-dark-secondary"><?= e($user['email'] ?? '-') ?></td>
                                <td class="px-4 py-3 text-text-secondary dark:text-text-dark-secondary"><?= e($user['phone'] ?? '-') ?></td>
                                <td class="px-4 py-3">
                                    <?php
                                    $statusClasses = [
                                        'active' => 'badge-success',
                                        'inactive' => 'badge-warning',
                                        'blocked' => 'badge-error',
                                        'pending' => 'badge-info',
                                    ];
                                    $statusClass = $statusClasses[$user['status']] ?? 'badge';
                                    ?>
                                    <span class="<?= $statusClass ?> text-xs font-bengali"><?= __('status.' . $user['status']) ?></span>
                                </td>
                                <td class="px-4 py-3">
                                    <?php if ($user['is_super_admin']): ?>
                                        <span class="badge-primary text-xs">হ্যাঁ</span>
                                    <?php else: ?>
                                        <span class="text-text-tertiary dark:text-text-dark-tertiary text-xs">না</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-xs text-text-secondary dark:text-text-dark-secondary"><?= e($user['shop_roles'] ?? '-') ?></span>
                                </td>
                                <td class="px-4 py-3 text-xs text-text-tertiary dark:text-text-dark-tertiary">
                                    <?= format_date($user['created_at']) ?>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="<?= url('admin/users/' . $user['id']) ?>" class="text-primary-700 dark:text-primary-400 hover:underline text-sm font-bengali">
                                        বিস্তারিত
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if (($total_pages ?? 1) > 1): ?>
            <div class="flex items-center justify-between px-4 py-3 border-t border-border dark:border-border-dark">
                <p class="text-sm text-text-tertiary dark:text-text-dark-tertiary font-bengali">
                    মোট <?= $total ?? 0 ?> টির মধ্যে <?= (($page - 1) * $perPage + 1) ?> - <?= min($page * $perPage, $total) ?> দেখানো হচ্ছে
                </p>
                <div class="flex gap-2">
                    <?php if ($page > 1): ?>
                        <a href="<?= url('admin/users?page=' . ($page - 1) . (!empty($search) ? '&search=' . urlencode($search) : '') . (!empty($status) ? '&status=' . urlencode($status) : '')) ?>" class="btn-secondary btn-sm font-bengali">পূর্ববর্তী</a>
                    <?php endif; ?>
                    <?php if ($page < $total_pages): ?>
                        <a href="<?= url('admin/users?page=' . ($page + 1) . (!empty($search) ? '&search=' . urlencode($search) : '') . (!empty($status) ? '&status=' . urlencode($status) : '')) ?>" class="btn-secondary btn-sm font-bengali">পরবর্তী</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
