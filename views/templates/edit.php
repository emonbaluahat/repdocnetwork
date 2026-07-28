<div class="max-w-4xl mx-auto">
  <div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-surface-900 dark:text-white"><?= __('edit_template') ?>: <?= e($template['name']) ?></h1>
    <div class="flex items-center gap-2">
      <a href="<?= url('templates/' . $template['id'] . '/preview') ?>" target="_blank" class="text-sm text-primary-600 dark:text-primary-400 hover:underline"><?= __('preview') ?></a>
      <a href="<?= url('templates') ?>" class="text-sm text-primary-600 dark:text-primary-400 hover:underline"><?= __('back_to_templates') ?></a>
    </div>
  </div>

  <form method="POST" action="<?= url('templates/' . $template['id']) ?>" class="space-y-6">
    <?= csrf_field() ?>

    <div class="bg-white dark:bg-surface-800 rounded-xl shadow-sm border border-surface-200 dark:border-surface-700 p-6">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-2"><?= __('template_name') ?></label>
          <input type="text" name="name" value="<?= e($template['name']) ?>" required class="w-full rounded-lg border-surface-300 dark:border-surface-600 bg-white dark:bg-surface-800 text-surface-900 dark:text-white px-3 py-2">
        </div>
        <div>
          <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-2"><?= __('category') ?></label>
          <select name="category" class="w-full rounded-lg border-surface-300 dark:border-surface-600 bg-white dark:bg-surface-800 text-surface-900 dark:text-white px-3 py-2">
            <?php foreach ($categories as $key => $label): ?>
            <option value="<?= $key ?>" <?= $template['category'] === $key ? 'selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-2"><?= __('paper_size') ?></label>
          <select name="paper_size" class="w-full rounded-lg border-surface-300 dark:border-surface-600 bg-white dark:bg-surface-800 text-surface-900 dark:text-white px-3 py-2">
            <?php foreach ($paper_sizes as $key => $label): ?>
            <option value="<?= $key ?>" <?= $template['paper_size'] === $key ? 'selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="mt-4">
        <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-2"><?= __('status') ?></label>
        <select name="status" class="w-full md:w-1/3 rounded-lg border-surface-300 dark:border-surface-600 bg-white dark:bg-surface-800 text-surface-900 dark:text-white px-3 py-2">
          <option value="draft" <?= $template['status'] === 'draft' ? 'selected' : '' ?>><?= __('draft') ?></option>
          <option value="active" <?= $template['status'] === 'active' ? 'selected' : '' ?>><?= __('active') ?></option>
          <option value="inactive" <?= $template['status'] === 'inactive' ? 'selected' : '' ?>><?= __('inactive') ?></option>
        </select>
      </div>
    </div>

    <div class="bg-white dark:bg-surface-800 rounded-xl shadow-sm border border-surface-200 dark:border-surface-700 p-6">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold text-surface-900 dark:text-white"><?= __('template_content') ?></h2>
      </div>
      <textarea name="content" rows="20" class="w-full rounded-lg border-surface-300 dark:border-surface-600 bg-white dark:bg-surface-800 text-surface-900 dark:text-white px-3 py-2 font-mono text-sm"><?= e($template['content']) ?></textarea>
    </div>

    <div class="bg-white dark:bg-surface-800 rounded-xl shadow-sm border border-surface-200 dark:border-surface-700 p-6">
      <h2 class="text-lg font-semibold text-surface-900 dark:text-white mb-4"><?= __('variables') ?></h2>
      <textarea name="variables_json" rows="6" class="w-full rounded-lg border-surface-300 dark:border-surface-600 bg-white dark:bg-surface-800 text-surface-900 dark:text-white px-3 py-2 font-mono text-sm"><?= e($template['variables'] ?? '') ?></textarea>
    </div>

    <div class="flex justify-end gap-3">
      <a href="<?= url('templates') ?>" class="px-6 py-2.5 border border-surface-300 dark:border-surface-600 text-surface-700 dark:text-surface-300 rounded-lg hover:bg-surface-50 dark:hover:bg-surface-700 transition-colors">
        <?= __('cancel') ?>
      </a>
      <button type="submit" class="px-6 py-2.5 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
        <?= __('update_template') ?>
      </button>
    </div>
  </form>
</div>
