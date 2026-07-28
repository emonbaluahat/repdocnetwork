<div class="max-w-7xl mx-auto">
  <div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-surface-900 dark:text-white"><?= __('certificate_requests') ?></h1>
    <a href="<?= url('certificates/requests/create') ?>" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
      <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
      <?= __('new_request') ?>
    </a>
  </div>

  <div class="bg-white dark:bg-surface-800 rounded-xl shadow-sm border border-surface-200 dark:border-surface-700 overflow-hidden">
    <div class="p-4 border-b border-surface-200 dark:border-surface-700">
      <form method="GET" class="flex gap-4">
        <select name="status" class="rounded-lg border-surface-300 dark:border-surface-600 bg-white dark:bg-surface-800 text-surface-900 dark:text-white px-3 py-2 text-sm">
          <option value=""><?= __('all_statuses') ?></option>
          <option value="draft" <?= ($status ?? '') === 'draft' ? 'selected' : '' ?>><?= __('draft') ?></option>
          <option value="submitted" <?= ($status ?? '') === 'submitted' ? 'selected' : '' ?>><?= __('submitted') ?></option>
          <option value="completed" <?= ($status ?? '') === 'completed' ? 'selected' : '' ?>><?= __('completed') ?></option>
          <option value="cancelled" <?= ($status ?? '') === 'cancelled' ? 'selected' : '' ?>><?= __('cancelled') ?></option>
        </select>
        <button type="submit" class="px-4 py-2 bg-surface-100 dark:bg-surface-700 text-surface-700 dark:text-surface-200 rounded-lg hover:bg-surface-200 dark:hover:bg-surface-600 transition-colors text-sm">
          <?= __('filter') ?>
        </button>
      </form>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full">
        <thead>
          <tr class="bg-surface-50 dark:bg-surface-800/50">
            <th class="text-left px-6 py-3 text-xs font-medium text-surface-500 uppercase tracking-wider"><?= __('id') ?></th>
            <th class="text-left px-6 py-3 text-xs font-medium text-surface-500 uppercase tracking-wider"><?= __('certificate_type') ?></th>
            <th class="text-left px-6 py-3 text-xs font-medium text-surface-500 uppercase tracking-wider"><?= __('customer') ?></th>
            <th class="text-left px-6 py-3 text-xs font-medium text-surface-500 uppercase tracking-wider"><?= __('status') ?></th>
            <th class="text-left px-6 py-3 text-xs font-medium text-surface-500 uppercase tracking-wider"><?= __('created') ?></th>
            <th class="text-right px-6 py-3 text-xs font-medium text-surface-500 uppercase tracking-wider"><?= __('actions') ?></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-surface-200 dark:divide-surface-700">
          <?php if (empty($requests)): ?>
          <tr>
            <td colspan="6" class="px-6 py-12 text-center text-surface-500"><?= __('no_requests_found') ?></td>
          </tr>
          <?php else: ?>
          <?php foreach ($requests as $req): ?>
          <tr class="hover:bg-surface-50 dark:hover:bg-surface-700/50 transition-colors">
            <td class="px-6 py-4 text-sm font-mono text-surface-600 dark:text-surface-400">#<?= $req['id'] ?></td>
            <td class="px-6 py-4">
              <span class="font-medium text-surface-900 dark:text-white">
                <?= e($req['type']['name_bn'] ?? '') ?>
              </span>
            </td>
            <td class="px-6 py-4 text-sm text-surface-600 dark:text-surface-400">
              <?= e($req['customer']['name'] ?? '—') ?>
            </td>
            <td class="px-6 py-4">
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                <?= $req['status'] === 'completed' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : '' ?>
                <?= $req['status'] === 'submitted' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400' : '' ?>
                <?= $req['status'] === 'draft' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400' : '' ?>
                <?= $req['status'] === 'cancelled' ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' : '' ?>">
                <?= __($req['status']) ?>
              </span>
            </td>
            <td class="px-6 py-4 text-sm text-surface-600 dark:text-surface-400">
              <?= format_datetime($req['created_at']) ?>
            </td>
            <td class="px-6 py-4 text-right">
              <div class="flex items-center justify-end gap-2">
                <?php if ($req['status'] === 'submitted'): ?>
                <a href="<?= url('certificates/requests/' . $req['id'] . '/generate') ?>" class="px-3 py-1.5 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors text-xs">
                  <?= __('generate') ?>
                </a>
                <?php endif; ?>
                <a href="<?= url('certificates/requests/' . $req['id']) ?>" class="p-2 text-surface-400 hover:text-primary-600 transition-colors" title="<?= __('view') ?>">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                </a>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
