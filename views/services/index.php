<div class="max-w-8xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-semibold text-text-primary dark:text-text-dark-primary font-bengali">সার্ভিস</h1>
            <p class="text-sm text-text-secondary dark:text-text-dark-secondary mt-1 font-bengali">মোট <?= e($total) ?> টি সার্ভিস</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="<?= url('services/categories') ?>" class="btn-secondary btn-sm font-bengali">ক্যাটাগরি</a>
            <a href="<?= url('services/create') ?>" class="btn-primary btn-sm font-bengali">+ নতুন সার্ভিস</a>
        </div>
    </div>

    <div class="bg-white dark:bg-card border border-border dark:border-border-dark rounded-xl overflow-hidden">
        <div class="p-4 border-b border-border dark:border-border-dark">
            <form method="GET" action="<?= url('services') ?>" class="flex items-center gap-3">
                <div class="flex-1">
                    <input type="text" name="search" value="<?= e($search) ?>"
                           class="input" placeholder="সার্ভিসের নাম খুঁজুন...">
                </div>
                <select name="category_id" class="input input-sm w-40">
                    <option value="">সব ক্যাটাগরি</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $category_id == $cat['id'] ? 'selected' : '' ?>>
                            <?= e($cat['name']) ?> (<?= (int) ($cat['service_count'] ?? 0) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <select name="status" class="input input-sm w-28">
                    <option value="">সব</option>
                    <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>সক্রিয়</option>
                    <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>নিষ্ক্রিয়</option>
                </select>
                <button type="submit" class="btn-primary btn-sm font-bengali">সার্চ</button>
                <?php if ($search || $category_id || $status): ?>
                    <a href="<?= url('services') ?>" class="btn-ghost btn-sm font-bengali">ক্লিয়ার</a>
                <?php endif; ?>
            </form>
        </div>

        <?php if (empty($services)): ?>
            <div class="p-8">
                <?php \App\Core\View::component('empty-state', [
                    'title' => $search ? __('empty.no_results') : __('empty.no_services'),
                    'description' => $search ? __('empty.try_different_search') : __('empty.add_first_service'),
                    'action_url' => $search ? null : url('services/create'),
                    'action_label' => $search ? null : '+ নতুন সার্ভিস',
                ]); ?>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-surface-secondary dark:bg-surface-dark-secondary">
                            <th class="text-left px-4 py-3 text-xs font-medium text-text-tertiary uppercase tracking-wider font-bengali">নাম</th>
                            <th class="text-left px-4 py-3 text-xs font-medium text-text-tertiary uppercase tracking-wider hidden sm:table-cell font-bengali">ক্যাটাগরি</th>
                            <th class="text-right px-4 py-3 text-xs font-medium text-text-tertiary uppercase tracking-wider font-bengali">মূল্য</th>
                            <th class="text-right px-4 py-3 text-xs font-medium text-text-tertiary uppercase tracking-wider hidden md:table-cell font-bengali">খরচ</th>
                            <th class="text-center px-4 py-3 text-xs font-medium text-text-tertiary uppercase tracking-wider font-bengali">স্ট্যাটাস</th>
                            <th class="text-right px-4 py-3 text-xs font-medium text-text-tertiary uppercase tracking-wider font-bengali">অ্যাকশন</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border dark:divide-border-dark">
                        <?php foreach ($services as $s): ?>
                            <tr class="hover:bg-surface-secondary dark:hover:bg-surface-dark-secondary transition">
                                <td class="px-4 py-3">
                                    <span class="text-sm font-medium text-text-primary dark:text-text-dark-primary"><?= e($s['name']) ?></span>
                                    <?php if ($s['description']): ?>
                                        <p class="text-xs text-text-tertiary dark:text-text-dark-tertiary mt-0.5 truncate max-w-xs"><?= e($s['description']) ?></p>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 hidden sm:table-cell">
                                    <?php if ($s['category_name']): ?>
                                        <span class="badge text-xs" style="background: <?= e($s['category_color'] ?? '#e5e7eb') ?>20; color: <?= e($s['category_color'] ?? '#374151') ?>">
                                            <?= e($s['category_name']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-sm text-text-tertiary">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-right text-sm font-semibold text-text-primary dark:text-text-dark-primary">
                                    ৳ <?= number_format((float) ($s['price'] ?? 0), 2) ?>
                                </td>
                                <td class="px-4 py-3 text-right text-sm text-text-secondary dark:text-text-dark-secondary hidden md:table-cell">
                                    <?php if ($s['cost_price']): ?>
                                        ৳ <?= number_format((float) $s['cost_price'], 2) ?>
                                    <?php else: ?>
                                        <span class="text-text-tertiary">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <a href="<?= url('services/' . $s['id'] . '/toggle-status') ?>"
                                       class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium transition"
                                       onclick="event.preventDefault(); fetch(this.href, {method:'POST', headers:{'X-CSRF-Token': '<?= csrf_token() ?>'}}).then(r=>r.json()).then(d=>{if(d.status){this.closest('td').querySelector('.status-text').textContent=d.status==='active'?'সক্রিয়':'নিষ্ক্রিয়';this.closest('td').querySelector('.status-dot').className='w-2 h-2 rounded-full '+(d.status==='active'?'bg-green-500':'bg-gray-400')}})">
                                        <span class="status-dot w-2 h-2 rounded-full <?= $s['status'] === 'active' ? 'bg-green-500' : 'bg-gray-400' ?>"></span>
                                        <span class="status-text"><?= $s['status'] === 'active' ? 'সক্রিয়' : 'নিষ্ক্রিয়' ?></span>
                                    </a>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="<?= url('services/' . $s['id'] . '/edit') ?>" class="btn-ghost btn-sm font-bengali">সম্পাদনা</a>
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
                        'base_url' => url('services'),
                        'query' => array_filter(['search' => $search, 'category_id' => $category_id, 'status' => $status]),
                    ]); ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>