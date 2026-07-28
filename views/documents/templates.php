<div class="max-w-8xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-semibold text-text-primary dark:text-text-dark-primary font-bengali">টেমপ্লেট</h1>
            <p class="text-sm text-text-secondary dark:text-text-dark-secondary mt-1 font-bengali">মোট <?= e($total) ?> টি টেমপ্লেট</p>
        </div>
        <a href="<?= url('templates/create') ?>" class="btn-primary btn-sm font-bengali">+ নতুন টেমপ্লেট</a>
    </div>

    <div class="bg-white dark:bg-card border border-border dark:border-border-dark rounded-xl overflow-hidden">
        <div class="p-4 border-b border-border dark:border-border-dark">
            <form method="GET" action="<?= url('templates') ?>" class="flex items-center gap-3">
                <div class="flex-1">
                    <input type="text" name="search" value="<?= e($search) ?>"
                           class="input" placeholder="নাম বা ক্যাটাগরি দিয়ে সার্চ...">
                </div>
                <select name="type" class="input input-sm w-36">
                    <option value="">সব ধরনের</option>
                    <?php foreach ($types as $k => $v): ?>
                        <option value="<?= $k ?>" <?= $type === $k ? 'selected' : '' ?>><?= e($v) ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="status" class="input input-sm w-28">
                    <option value="">সব</option>
                    <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>সক্রিয়</option>
                    <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>নিষ্ক্রিয়</option>
                </select>
                <button type="submit" class="btn-primary btn-sm font-bengali">সার্চ</button>
                <?php if ($search || $type || $status): ?>
                    <a href="<?= url('templates') ?>" class="btn-ghost btn-sm font-bengali">ক্লিয়ার</a>
                <?php endif; ?>
            </form>
        </div>

        <?php if (empty($templates)): ?>
            <div class="p-8">
                <?php \App\Core\View::component('empty-state', [
                    'title' => $search ? 'কোনো টেমপ্লেট পাওয়া যায়নি' : 'কোনো টেমপ্লেট নেই',
                    'description' => $search ? 'অন্য কিছু সার্চ করুন' : 'প্রথম টেমপ্লেট তৈরি করুন',
                    'action_url' => $search ? null : url('templates/create'),
                    'action_label' => $search ? null : '+ নতুন টেমপ্লেট',
                ]); ?>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 p-4">
                <?php foreach ($templates as $t): ?>
                    <div class="border border-border dark:border-border-dark rounded-lg p-4 hover:shadow-sm transition bg-white dark:bg-card">
                        <div class="flex items-start justify-between mb-2">
                            <div>
                                <h3 class="text-sm font-semibold text-text-primary dark:text-text-dark-primary"><?= e($t['name']) ?></h3>
                                <p class="text-xs text-text-tertiary dark:text-text-dark-tertiary mt-0.5">
                                    <?= e($types[$t['template_type']] ?? $t['template_type']) ?>
                                    <?php if ($t['category']): ?> · <?= e($t['category']) ?><?php endif; ?>
                                </p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="sr-only peer"
                                       <?= $t['status'] === 'active' ? 'checked' : '' ?>
                                       onchange="fetch('<?= url('templates/' . $t['id'] . '/toggle-status') ?>', {method:'POST',headers:{'X-CSRF-Token':'<?= csrf_token() ?>'}}).then(r=>r.json()).then(d=>{if(!d.status)location.reload()})">
                                <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-300 dark:peer-focus:ring-primary-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-gray-600 peer-checked:bg-primary-600"></div>
                            </label>
                        </div>

                        <?php
                        $vars = json_decode($t['variables'] ?? '[]', true);
                        ?>
                        <?php if (!empty($vars)): ?>
                            <div class="flex flex-wrap gap-1 mb-3">
                                <?php foreach (array_slice($vars, 0, 4) as $v): ?>
                                    <span class="text-xs bg-surface-secondary dark:bg-surface-dark-secondary px-1.5 py-0.5 rounded font-mono">{{<?= e($v) ?>}}</span>
                                <?php endforeach; ?>
                                <?php if (count($vars) > 4): ?>
                                    <span class="text-xs text-text-tertiary">+<?= count($vars) - 4 ?></span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <div class="flex items-center gap-1 pt-2 border-t border-border dark:border-border-dark">
                            <a href="<?= url('templates/' . $t['id'] . '/edit') ?>" class="btn-ghost btn-xs font-bengali">সম্পাদনা</a>
                            <a href="<?= url('templates/' . $t['id'] . '/preview') ?>" target="_blank" class="btn-ghost btn-xs font-bengali">প্রিভিউ</a>
                            <form method="POST" action="<?= url('templates/' . $t['id'] . '/duplicate') ?>" class="inline">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn-ghost btn-xs font-bengali">ডুপ্লিকেট</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if ($total_pages > 1): ?>
                <div class="p-4 border-t border-border dark:border-border-dark">
                    <?php \App\Core\View::component('pagination', [
                        'current_page' => $page,
                        'total_pages' => $total_pages,
                        'has_prev' => $has_prev,
                        'has_next' => $has_next,
                        'base_url' => url('templates'),
                        'query' => array_filter(['search' => $search, 'type' => $type, 'status' => $status]),
                    ]); ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
