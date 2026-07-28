<div class="max-w-4xl mx-auto">
  <div class="flex items-center justify-between mb-6">
    <div>
      <h1 class="text-2xl font-bold text-surface-900 dark:text-white"><?= e($type['name_bn']) ?></h1>
      <p class="text-sm text-surface-500 mt-1"><?= e($type['name_en']) ?> — <?= __('fields') ?></p>
    </div>
    <a href="<?= url('certificates') ?>" class="text-sm text-primary-600 dark:text-primary-400 hover:underline"><?= __('back_to_types') ?></a>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
      <div class="bg-white dark:bg-surface-800 rounded-xl shadow-sm border border-surface-200 dark:border-surface-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-surface-200 dark:border-surface-700">
          <h2 class="text-lg font-semibold text-surface-900 dark:text-white"><?= __('field_list') ?></h2>
        </div>
        <div class="divide-y divide-surface-200 dark:divide-surface-700">
          <?php if (empty($fields)): ?>
          <div class="px-6 py-8 text-center text-surface-500"><?= __('no_fields') ?></div>
          <?php else: ?>
          <?php foreach ($fields as $field): ?>
          <div class="px-6 py-3 flex items-center justify-between hover:bg-surface-50 dark:hover:bg-surface-700/50 transition-colors">
            <div>
              <span class="font-medium text-surface-900 dark:text-white"><?= e($field['label_bn']) ?></span>
              <span class="text-sm text-surface-500 ml-2">(<?= e($field['field_name']) ?>)</span>
              <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-surface-100 text-surface-600 ml-2"><?= e($field['field_type']) ?></span>
              <?php if ($field['required']): ?>
              <span class="text-red-500 text-xs ml-1">*</span>
              <?php endif; ?>
            </div>
            <form method="POST" action="<?= url('certificates/fields/' . $field['id'] . '/delete') ?>" class="inline" onsubmit="return confirm('<?= __('confirm_delete') ?>')">
              <?= csrf_field() ?>
              <button type="submit" class="p-1 text-surface-400 hover:text-red-500 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
              </button>
            </form>
          </div>
          <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div>
      <div class="bg-white dark:bg-surface-800 rounded-xl shadow-sm border border-surface-200 dark:border-surface-700 p-6">
        <h2 class="text-lg font-semibold text-surface-900 dark:text-white mb-4"><?= __('add_field') ?></h2>
        <form method="POST" action="<?= url('certificates/types/' . $type['id'] . '/fields') ?>" class="space-y-4">
          <?= csrf_field() ?>
          <div>
            <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-2"><?= __('field_name') ?></label>
            <input type="text" name="field_name" required class="w-full rounded-lg border-surface-300 dark:border-surface-600 bg-white dark:bg-surface-800 text-surface-900 dark:text-white px-3 py-2 text-sm" placeholder="father_name">
          </div>
          <div>
            <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-2"><?= __('label_bn') ?></label>
            <input type="text" name="label_bn" required class="w-full rounded-lg border-surface-300 dark:border-surface-600 bg-white dark:bg-surface-800 text-surface-900 dark:text-white px-3 py-2 text-sm" placeholder="পিতার নাম">
          </div>
          <div>
            <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-2"><?= __('field_type') ?></label>
            <select name="field_type" class="w-full rounded-lg border-surface-300 dark:border-surface-600 bg-white dark:bg-surface-800 text-surface-900 dark:text-white px-3 py-2 text-sm">
              <option value="text"><?= __('text') ?></option>
              <option value="number"><?= __('number') ?></option>
              <option value="date"><?= __('date') ?></option>
              <option value="select"><?= __('select') ?></option>
              <option value="textarea"><?= __('textarea') ?></option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-2"><?= __('options') ?></label>
            <textarea name="options" rows="3" class="w-full rounded-lg border-surface-300 dark:border-surface-600 bg-white dark:bg-surface-800 text-surface-900 dark:text-white px-3 py-2 text-sm" placeholder="<?= __('one_per_line') ?>"></textarea>
          </div>
          <div class="flex items-center gap-2">
            <input type="checkbox" name="required" id="required" value="1" class="rounded border-surface-300 text-primary-600 focus:ring-primary-500">
            <label for="required" class="text-sm text-surface-700 dark:text-surface-300"><?= __('required') ?></label>
          </div>
          <div>
            <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-2"><?= __('position') ?></label>
            <input type="number" name="position" value="<?= count($fields) + 1 ?>" class="w-full rounded-lg border-surface-300 dark:border-surface-600 bg-white dark:bg-surface-800 text-surface-900 dark:text-white px-3 py-2 text-sm">
          </div>
          <button type="submit" class="w-full px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors text-sm">
            <?= __('add_field') ?>
          </button>
        </form>
      </div>
    </div>
  </div>
</div>
