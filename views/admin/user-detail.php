<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center gap-3 mb-6">
        <a href="<?= url('admin/users') ?>" class="text-text-secondary dark:text-text-dark-secondary hover:text-text-primary dark:hover:text-text-dark-primary transition">
            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>
            </svg>
        </a>
        <h1 class="text-xl font-semibold text-text-primary dark:text-text-dark-primary font-bengali">ব্যবহারকারী বিস্তারিত</h1>
    </div>

    <div class="bg-white dark:bg-card border border-border dark:border-border-dark rounded-xl p-6">
        <div class="flex items-center gap-4 mb-6">
            <div class="w-16 h-16 bg-primary-100 dark:bg-primary-900/30 rounded-full flex items-center justify-center text-xl font-semibold text-primary-700 dark:text-primary-400">
                <?= e(mb_substr($user['name'], 0, 1, 'UTF-8')) ?>
            </div>
            <div class="flex-1">
                <div class="flex items-center gap-2">
                    <p class="text-lg font-semibold text-text-primary dark:text-text-dark-primary"><?= e($user['name']) ?></p>
                    <?php if ($user['is_super_admin']): ?>
                        <span class="badge-primary text-xs font-bengali">সুপার অ্যাডমিন</span>
                    <?php endif; ?>
                </div>
                <p class="text-sm text-text-secondary dark:text-text-dark-secondary mt-1"><?= e($user['email']) ?> • <?= e($user['phone']) ?></p>
                <div class="flex items-center gap-2 mt-1">
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
                    <span class="text-xs text-text-tertiary dark:text-text-dark-tertiary font-bengali">নিবন্ধন: <?= format_date($user['created_at']) ?></span>
                    <span class="text-xs text-text-tertiary dark:text-text-dark-tertiary font-bengali">শেষ লগইন: <?= $user['last_login_at'] ? format_datetime($user['last_login_at']) : 'কখনো না' ?></span>
                </div>
            </div>
            <div class="flex gap-2">
                <?php if (!$user['is_super_admin']): ?>
                    <form method="POST" action="<?= url('admin/users/' . $user['id'] . '/toggle-status') ?>" class="inline">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn-<?= $user['status'] === 'active' ? 'warning' : 'success' ?> btn-sm font-bengali">
                            <?= $user['status'] === 'active' ? __('admin.deactivate') : __('admin.activate') ?>
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if (!empty($shops)): ?>
        <div class="bg-white dark:bg-card border border-border dark:border-border-dark rounded-xl p-6">
            <h2 class="text-base font-semibold text-text-primary dark:text-text-dark-primary mb-4 font-bengali">শপ অ্যাক্সেস</h2>
            <div class="space-y-3">
                <?php foreach ($shops as $shop): ?>
                    <div class="flex items-center justify-between py-2 px-3 rounded-lg hover:bg-surface-secondary dark:hover:bg-surface-dark-secondary transition">
                        <div>
                            <p class="font-medium text-text-primary dark:text-text-dark-primary"><?= e($shop['name']) ?></p>
                            <p class="text-sm text-text-secondary dark:text-text-dark-secondary font-bengali">ভূমিকা: <?= __('role.' . $shop['role']) ?></p>
                        </div>
                        <?php if (!$user['is_super_admin']): ?>
                            <form method="POST" action="<?= url('admin/users/' . $user['id'] . '/change-role') ?>" class="flex items-center gap-2">
                                <?= csrf_field() ?>
                                <input type="hidden" name="shop_id" value="<?= $shop['id'] ?>">
                                <select name="role" class="px-2 py-1 border border-border dark:border-border-dark rounded text-sm bg-white dark:bg-card text-text-primary dark:text-text-dark-primary">
                                    <option value="owner" <?= $shop['role'] === 'owner' ? 'selected' : '' ?>>Owner</option>
                                    <option value="admin" <?= $shop['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                    <option value="operator" <?= $shop['role'] === 'operator' ? 'selected' : '' ?>>Operator</option>
                                    <option value="customer" <?= $shop['role'] === 'customer' ? 'selected' : '' ?>>Customer</option>
                                </select>
                                <button type="submit" class="btn-ghost btn-sm font-bengali">পরিবর্তন</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="bg-white dark:bg-card border border-border dark:border-border-dark rounded-xl p-6">
        <h2 class="text-base font-semibold text-text-primary dark:text-text-dark-primary mb-4 font-bengali">অ্যাডমিন অ্যাকশন</h2>
        <div class="flex gap-3">
            <form method="POST" action="<?= url('admin/users/' . $user['id'] . '/reset-password') ?>" class="inline">
                <?= csrf_field() ?>
                <button type="submit" class="btn-secondary btn-sm font-bengali" onclick="return confirm('পাসওয়ার্ড রিসেট করবেন?')">
                    পাসওয়ার্ড রিসেট
                </button>
            </form>
        </div>
    </div>

    <div class="bg-white dark:bg-card border border-border dark:border-border-dark rounded-xl p-6">
        <h2 class="text-base font-semibold text-text-primary dark:text-text-dark-primary mb-4 font-bengali">সাম্প্রতিক কার্যকলাপ</h2>
        <?php if (empty($audit_logs)): ?>
            <p class="text-sm text-text-tertiary dark:text-text-dark-tertiary text-center py-4 font-bengali">কোনো কার্যকলাপ নেই</p>
        <?php else: ?>
            <div class="space-y-2">
                <?php foreach ($audit_logs as $log): ?>
                    <div class="flex items-start gap-3 py-2 border-b border-border dark:border-border-dark last:border-0">
                        <div class="w-2 h-2 mt-2 rounded-full bg-primary-400 flex-shrink-0"></div>
                        <div class="flex-1">
                            <p class="text-sm text-text-primary dark:text-text-dark-primary"><?= e($log['action']) ?></p>
                            <p class="text-xs text-text-tertiary dark:text-text-dark-tertiary mt-0.5">
                                <?= e($log['ip_address'] ?? '') ?> • <?= format_datetime($log['created_at']) ?>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
