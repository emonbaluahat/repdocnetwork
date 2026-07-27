<div class="max-w-4xl mx-auto space-y-6">
    <div>
        <h1 class="text-xl font-semibold text-text-primary dark:text-text-dark-primary font-bengali"><?= __('profile.title') ?></h1>
        <p class="text-sm text-text-secondary dark:text-text-dark-secondary mt-1 font-bengali"><?= __('profile.edit') ?></p>
    </div>

    <div class="bg-white dark:bg-card border border-border dark:border-border-dark rounded-xl p-6">
        <form method="POST" action="<?= url('profile') ?>" class="space-y-4">
            <?= csrf_field() ?>

            <div class="flex items-center gap-4 mb-6">
                <div class="w-16 h-16 bg-primary-100 dark:bg-primary-900/30 rounded-full flex items-center justify-center text-xl font-semibold text-primary-700 dark:text-primary-400">
                    <?= e(mb_substr($user['name'], 0, 1, 'UTF-8')) ?>
                </div>
                <div>
                    <p class="font-medium text-text-primary dark:text-text-dark-primary"><?= e($user['name']) ?></p>
                    <p class="text-sm text-text-secondary dark:text-text-dark-secondary"><?= e($user['email']) ?></p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-text-primary dark:text-text-dark-primary mb-1 font-bengali">নাম</label>
                    <input type="text" id="name" name="name" value="<?= e($user['name']) ?>"
                           class="w-full px-3 py-2 border border-border dark:border-border-dark rounded-lg bg-white dark:bg-card text-text-primary dark:text-text-dark-primary placeholder-text-tertiary focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition text-sm" required>
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-text-primary dark:text-text-dark-primary mb-1 font-bengali">ইমেইল</label>
                    <input type="email" id="email" name="email" value="<?= e($user['email']) ?>"
                           class="w-full px-3 py-2 border border-border dark:border-border-dark rounded-lg bg-white dark:bg-card text-text-primary dark:text-text-dark-primary placeholder-text-tertiary focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition text-sm" required>
                </div>
                <div>
                    <label for="phone" class="block text-sm font-medium text-text-primary dark:text-text-dark-primary mb-1 font-bengali">ফোন নম্বর</label>
                    <input type="tel" id="phone" name="phone" value="<?= e($user['phone']) ?>"
                           class="w-full px-3 py-2 border border-border dark:border-border-dark rounded-lg bg-white dark:bg-card text-text-primary dark:text-text-dark-primary placeholder-text-tertiary focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition text-sm" required>
                </div>
            </div>

            <div class="flex justify-end pt-4">
                <button type="submit" class="btn-primary font-bengali"><?= __('form.save') ?></button>
            </div>
        </form>
    </div>

    <div class="bg-white dark:bg-card border border-border dark:border-border-dark rounded-xl p-6">
        <h2 class="text-base font-semibold text-text-primary dark:text-text-dark-primary mb-4 font-bengali">পাসওয়ার্ড পরিবর্তন</h2>
        <form method="POST" action="<?= url('profile/password') ?>" class="space-y-4 max-w-md">
            <?= csrf_field() ?>

            <div>
                <label for="current_password" class="block text-sm font-medium text-text-primary dark:text-text-dark-primary mb-1 font-bengali">বর্তমান পাসওয়ার্ড</label>
                <input type="password" id="current_password" name="current_password"
                       class="w-full px-3 py-2 border border-border dark:border-border-dark rounded-lg bg-white dark:bg-card text-text-primary dark:text-text-dark-primary placeholder-text-tertiary focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition text-sm" required>
            </div>
            <div>
                <label for="new_password" class="block text-sm font-medium text-text-primary dark:text-text-dark-primary mb-1 font-bengali">নতুন পাসওয়ার্ড</label>
                <input type="password" id="new_password" name="new_password"
                       class="w-full px-3 py-2 border border-border dark:border-border-dark rounded-lg bg-white dark:bg-card text-text-primary dark:text-text-dark-primary placeholder-text-tertiary focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition text-sm" required minlength="8">
            </div>
            <div>
                <label for="new_password_confirmation" class="block text-sm font-medium text-text-primary dark:text-text-dark-primary mb-1 font-bengali">নতুন পাসওয়ার্ড নিশ্চিত করুন</label>
                <input type="password" id="new_password_confirmation" name="new_password_confirmation"
                       class="w-full px-3 py-2 border border-border dark:border-border-dark rounded-lg bg-white dark:bg-card text-text-primary dark:text-text-dark-primary placeholder-text-tertiary focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition text-sm" required>
            </div>

            <button type="submit" class="btn-primary font-bengali">পাসওয়ার্ড পরিবর্তন</button>
        </form>
    </div>

    <div class="bg-white dark:bg-card border border-border dark:border-border-dark rounded-xl p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-base font-semibold text-text-primary dark:text-text-dark-primary font-bengali">সক্রিয় সেশন</h2>
            <form method="POST" action="<?= url('profile/sessions/terminate-all') ?>" class="inline" onsubmit="return confirm('<?= __('profile.logout_others') ?>?')">
                <?= csrf_field() ?>
                <button type="submit" class="text-sm text-error-600 dark:text-error-400 hover:underline font-bengali">
                    অন্য সব সেশন লগআউট করুন
                </button>
            </form>
        </div>

        <div class="space-y-3">
            <?php if (empty($active_sessions)): ?>
                <p class="text-sm text-text-tertiary dark:text-text-dark-tertiary text-center py-4 font-bengali">কোনো সেশন নেই</p>
            <?php else: ?>
                <?php foreach ($active_sessions as $session): ?>
                    <div class="flex items-center justify-between py-2 border-b border-border dark:border-border-dark last:border-0">
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <p class="text-sm font-medium text-text-primary dark:text-text-dark-primary">
                                    <?= e($session['ip_address'] ?? 'Unknown') ?>
                                </p>
                                <?php if ($session['id'] === $current_session_id): ?>
                                    <span class="badge-success text-xs font-bengali">বর্তমান</span>
                                <?php endif; ?>
                            </div>
                            <p class="text-xs text-text-tertiary dark:text-text-dark-tertiary mt-0.5">
                                <?= e($session['user_agent'] ?? '') ?>
                            </p>
                            <p class="text-xs text-text-tertiary dark:text-text-dark-tertiary mt-0.5">
                                শেষ কার্যকলাপ: <?= format_datetime($session['last_activity']) ?>
                            </p>
                        </div>
                        <?php if ($session['id'] !== $current_session_id): ?>
                            <form method="POST" action="<?= url('profile/sessions/terminate') ?>">
                                <?= csrf_field() ?>
                                <input type="hidden" name="session_id" value="<?= e($session['id']) ?>">
                                <button type="submit" class="text-sm text-error-600 dark:text-error-400 hover:underline font-bengali">
                                    লগআউট
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
