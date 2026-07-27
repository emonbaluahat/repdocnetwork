<div class="max-w-8xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-semibold text-text-primary dark:text-text-dark-primary font-bengali">স্টাফ ব্যবস্থাপনা</h1>
            <p class="text-sm text-text-secondary dark:text-text-dark-secondary mt-1 font-bengali"><?= e($shop['name'] ?? '') ?></p>
        </div>
        <a href="<?= url('staff/invite') ?>" class="btn-primary font-bengali">+ স্টাফ আমন্ত্রণ</a>
    </div>

    <div class="bg-white dark:bg-card border border-border dark:border-border-dark rounded-xl overflow-hidden mb-6">
        <div class="px-4 py-3 border-b border-border dark:border-border-dark">
            <h2 class="text-base font-semibold text-text-primary dark:text-text-dark-primary font-bengali">সক্রিয় স্টাফ</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-border dark:border-border-dark bg-surface-secondary dark:bg-surface-dark-secondary">
                        <th class="text-left px-4 py-3 font-medium text-text-secondary dark:text-text-dark-secondary font-bengali">নাম</th>
                        <th class="text-left px-4 py-3 font-medium text-text-secondary dark:text-text-dark-secondary font-bengali">ইমেইল</th>
                        <th class="text-left px-4 py-3 font-medium text-text-secondary dark:text-text-dark-secondary font-bengali">ফোন</th>
                        <th class="text-left px-4 py-3 font-medium text-text-secondary dark:text-text-dark-secondary font-bengali">ভূমিকা</th>
                        <th class="text-left px-4 py-3 font-medium text-text-secondary dark:text-text-dark-secondary font-bengali">যোগদান</th>
                        <th class="text-right px-4 py-3 font-medium text-text-secondary dark:text-text-dark-secondary font-bengali">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($staff)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-8 text-text-tertiary dark:text-text-dark-tertiary font-bengali">
                                কোনো স্টাফ নেই
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($staff as $member): ?>
                            <tr class="border-b border-border dark:border-border-dark hover:bg-surface-secondary dark:hover:bg-surface-dark-secondary transition">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 bg-primary-100 dark:bg-primary-900/30 rounded-full flex items-center justify-center text-xs font-semibold text-primary-700 dark:text-primary-400">
                                            <?= e(mb_substr($member['name'], 0, 1, 'UTF-8')) ?>
                                        </div>
                                        <span class="font-medium text-text-primary dark:text-text-dark-primary"><?= e($member['name']) ?></span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-text-secondary dark:text-text-dark-secondary"><?= e($member['email'] ?? '-') ?></td>
                                <td class="px-4 py-3 text-text-secondary dark:text-text-dark-secondary"><?= e($member['phone'] ?? '-') ?></td>
                                <td class="px-4 py-3">
                                    <form method="POST" action="<?= url('staff/' . $member['id'] . '/role') ?>" class="flex items-center gap-2">
                                        <?= csrf_field() ?>
                                        <select name="role" onchange="this.form.submit()"
                                                class="px-2 py-1 border border-border dark:border-border-dark rounded text-sm bg-white dark:bg-card text-text-primary dark:text-text-dark-primary">
                                            <option value="admin" <?= $member['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                            <option value="operator" <?= $member['role'] === 'operator' ? 'selected' : '' ?>>Operator</option>
                                            <option value="customer" <?= $member['role'] === 'customer' ? 'selected' : '' ?>>Customer</option>
                                        </select>
                                    </form>
                                </td>
                                <td class="px-4 py-3 text-xs text-text-tertiary dark:text-text-dark-tertiary">
                                    <?= format_date($member['joined_at']) ?>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <form method="POST" action="<?= url('staff/' . $member['id'] . '/remove') ?>" class="inline"
                                          onsubmit="return confirm('<?= __('staff.remove_confirm') ?>')">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="text-sm text-error-600 dark:text-error-400 hover:underline font-bengali">
                                            সরান
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if (!empty($invitations)): ?>
        <div class="bg-white dark:bg-card border border-border dark:border-border-dark rounded-xl overflow-hidden">
            <div class="px-4 py-3 border-b border-border dark:border-border-dark">
                <h2 class="text-base font-semibold text-text-primary dark:text-text-dark-primary font-bengali">পেন্ডিং আমন্ত্রণ</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border dark:border-border-dark bg-surface-secondary dark:bg-surface-dark-secondary">
                            <th class="text-left px-4 py-3 font-medium text-text-secondary dark:text-text-dark-secondary font-bengali">ইমেইল/ফোন</th>
                            <th class="text-left px-4 py-3 font-medium text-text-secondary dark:text-text-dark-secondary font-bengali">ভূমিকা</th>
                            <th class="text-left px-4 py-3 font-medium text-text-secondary dark:text-text-dark-secondary font-bengali">স্ট্যাটাস</th>
                            <th class="text-left px-4 py-3 font-medium text-text-secondary dark:text-text-dark-secondary font-bengali">মেয়াদ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($invitations as $inv): ?>
                            <tr class="border-b border-border dark:border-border-dark">
                                <td class="px-4 py-3 text-text-primary dark:text-text-dark-primary"><?= e($inv['email'] ?? $inv['phone'] ?? '-') ?></td>
                                <td class="px-4 py-3"><?= __('role.' . $inv['role']) ?></td>
                                <td class="px-4 py-3"><span class="badge-warning text-xs font-bengali">পেন্ডিং</span></td>
                                <td class="px-4 py-3 text-xs text-text-tertiary dark:text-text-dark-tertiary"><?= format_date($inv['expires_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>
