<div class="max-w-8xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-semibold text-text-primary dark:text-text-dark-primary font-bengali">ভূমিকা-ভিত্তিক অনুমতি</h1>
            <p class="text-sm text-text-secondary dark:text-text-dark-secondary mt-1 font-bengali"><?= e($shop['name'] ?? '') ?></p>
        </div>
    </div>

    <div class="bg-white dark:bg-card border border-border dark:border-border-dark rounded-xl overflow-hidden">
        <form method="POST" action="<?= url('staff/permissions/update') ?>">
            <?= csrf_field() ?>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border dark:border-border-dark bg-surface-secondary dark:bg-surface-dark-secondary">
                            <th class="text-left px-4 py-3 font-medium text-text-secondary dark:text-text-dark-secondary font-bengali w-48">মডিউল / অনুমতি</th>
                            <?php foreach ($roles as $role): ?>
                                <th class="text-center px-4 py-3 font-medium text-text-secondary dark:text-text-dark-secondary font-bengali"><?= __('role.' . $role) ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($grouped_permissions as $module => $permissions): ?>
                            <tr class="border-b border-border dark:border-border-dark bg-surface-secondary dark:bg-surface-dark-secondary">
                                <td colspan="<?= count($roles) + 1 ?>" class="px-4 py-2 font-semibold text-text-primary dark:text-text-dark-primary text-xs uppercase tracking-wider">
                                    <?= e($module) ?>
                                </td>
                            </tr>
                            <?php foreach ($permissions as $perm): ?>
                                <tr class="border-b border-border dark:border-border-dark hover:bg-surface-secondary dark:hover:bg-surface-dark-secondary transition">
                                    <td class="px-4 py-2.5">
                                        <p class="text-sm text-text-primary dark:text-text-dark-primary"><?= e($perm['name']) ?></p>
                                        <p class="text-xs text-text-tertiary dark:text-text-dark-tertiary"><?= e($perm['slug']) ?></p>
                                    </td>
                                    <?php foreach ($roles as $role): ?>
                                        <td class="px-4 py-2.5 text-center">
                                            <input type="checkbox" name="permissions[<?= e($role) ?>][]" value="<?= $perm['id'] ?>"
                                                   class="rounded border-border dark:border-border-dark text-primary-600 focus:ring-primary-500"
                                                   <?= in_array($perm['id'], $role_perm_map[$role] ?? []) ? 'checked' : '' ?>
                                                   <?= $role === ROLE_OWNER ? 'disabled' : '' ?>>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="flex justify-end px-4 py-3 border-t border-border dark:border-border-dark">
                <button type="submit" class="btn-primary font-bengali">অনুমতি সংরক্ষণ করুন</button>
            </div>
        </form>
    </div>
</div>
