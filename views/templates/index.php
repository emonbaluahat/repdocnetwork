<div class="max-w-7xl mx-auto">
  <div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-surface-900 dark:text-white"><?= __('document_templates') ?></h1>
    <a href="<?= url('templates/create') ?>" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
      <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
      <?= __('create_template') ?>
    </a>
  </div>

  <div class="bg-white dark:bg-surface-800 rounded-xl shadow-sm border border-surface-200 dark:border-surface-700 overflow-hidden">
    <div class="p-4 border-b border-surface-200 dark:border-surface-700">
      <form method="GET" class="flex gap-4">
        <select name="category" class="rounded-lg border-surface-300 dark:border-surface-600 bg-white dark:bg-surface-800 text-surface-900 dark:text-white px-3 py-2 text-sm">
          <option value=""><?= __('all_categories') ?></option>
          <option value="general" <?= ($category ?? '') === 'general' ? 'selected' : '' ?>><?= __('general') ?></option>
          <option value="certificate" <?= ($category ?? '') === 'certificate' ? 'selected' : '' ?>><?= __('certificate') ?></option>
          <option value="form" <?= ($category ?? '') === 'form' ? 'selected' : '' ?>><?= __('form') ?></option>
          <option value="report" <?= ($category ?? '') === 'report' ? 'selected' : '' ?>><?= __('report') ?></option>
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
            <th class="text-left px-6 py-3 text-xs font-medium text-surface-500 uppercase tracking-wider"><?= __('name') ?></th>
            <th class="text-left px-6 py-3 text-xs font-medium text-surface-500 uppercase tracking-wider"><?= __('category') ?></th>
            <th class="text-left px-6 py-3 text-xs font-medium text-surface-500 uppercase tracking-wider"><?= __('status') ?></th>
            <th class="text-left px-6 py-3 text-xs font-medium text-surface-500 uppercase tracking-wider"><?= __('paper_size') ?></th>
            <th class="text-right px-6 py-3 text-xs font-medium text-surface-500 uppercase tracking-wider"><?= __('actions') ?></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-surface-200 dark:divide-surface-700">
          <?php if (empty($templates)): ?>
          <tr>
            <td colspan="5" class="px-6 py-12 text-center text-surface-500"><?= __('no_templates_found') ?></td>
          </tr>
          <?php else: ?>
          <?php foreach ($templates as $tpl): ?>
          <tr class="hover:bg-surface-50 dark:hover:bg-surface-700/50 transition-colors">
            <td class="px-6 py-4">
              <span class="font-medium text-surface-900 dark:text-white"><?= e($tpl['name']) ?></span>
            </td>
            <td class="px-6 py-4">
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-surface-100 text-surface-700 dark:bg-surface-700 dark:text-surface-300">
                <?= __($tpl['category']) ?>
              </span>
            </td>
            <td class="px-6 py-4">
              <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                <?= $tpl['status'] === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : '' ?>
                <?= $tpl['status'] === 'draft' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400' : '' ?>
                <?= $tpl['status'] === 'inactive' ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' : '' ?>">
                <?= __($tpl['status']) ?>
              </span>
            </td>
            <td class="px-6 py-4 text-sm text-surface-600 dark:text-surface-400"><?= e($tpl['paper_size']) ?></td>
            <td class="px-6 py-4 text-right">
              <div class="flex items-center justify-end gap-2">
                <a href="<?= url('templates/' . $tpl['id'] . '/preview') ?>" target="_blank" class="p-2 text-surface-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors" title="<?= __('preview') ?>">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                </a>
                <a href="<?= url('templates/' . $tpl['id'] . '/edit') ?>" class="p-2 text-surface-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors" title="<?= __('edit') ?>">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </a>
                <form method="POST" action="<?= url('templates/' . $tpl['id'] . '/duplicate') ?>" class="inline" onsubmit="return confirm('<?= __('confirm_duplicate') ?>')">
                  <?= csrf_field() ?>
                  <button type="submit" class="p-2 text-surface-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors" title="<?= __('duplicate') ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                  </button>
                </form>
                <form method="POST" action="<?= url('templates/' . $tpl['id'] . '/toggle-status') ?>" class="inline">
                  <?= csrf_field() ?>
                  <button type="submit" class="p-2 text-surface-400 hover:text-primary-600 dark:hover:text-primary-400 transition-colors" title="<?= __('toggle_status') ?>">
                    <?php if ($tpl['status'] === 'active'): ?>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <?php else: ?>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <?php endif; ?>
                  </button>
                </form>
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
