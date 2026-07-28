<div class="max-w-4xl mx-auto">
  <div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-surface-900 dark:text-white"><?= __('create_template') ?></h1>
    <a href="<?= url('templates') ?>" class="text-sm text-primary-600 dark:text-primary-400 hover:underline"><?= __('back_to_templates') ?></a>
  </div>

  <form method="POST" action="<?= url('templates') ?>" class="space-y-6">
    <?= csrf_field() ?>

    <div class="bg-white dark:bg-surface-800 rounded-xl shadow-sm border border-surface-200 dark:border-surface-700 p-6">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-2"><?= __('template_name') ?></label>
          <input type="text" name="name" value="<?= e(old('name')) ?>" required class="w-full rounded-lg border-surface-300 dark:border-surface-600 bg-white dark:bg-surface-800 text-surface-900 dark:text-white px-3 py-2">
        </div>
        <div>
          <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-2"><?= __('category') ?></label>
          <select name="category" class="w-full rounded-lg border-surface-300 dark:border-surface-600 bg-white dark:bg-surface-800 text-surface-900 dark:text-white px-3 py-2">
            <?php foreach ($categories as $key => $label): ?>
            <option value="<?= $key ?>"><?= e($label) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-2"><?= __('paper_size') ?></label>
          <select name="paper_size" class="w-full rounded-lg border-surface-300 dark:border-surface-600 bg-white dark:bg-surface-800 text-surface-900 dark:text-white px-3 py-2">
            <?php foreach ($paper_sizes as $key => $label): ?>
            <option value="<?= $key ?>"><?= e($label) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="mt-4">
        <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-2"><?= __('status') ?></label>
        <select name="status" class="w-full md:w-1/3 rounded-lg border-surface-300 dark:border-surface-600 bg-white dark:bg-surface-800 text-surface-900 dark:text-white px-3 py-2">
          <option value="draft"><?= __('draft') ?></option>
          <option value="active"><?= __('active') ?></option>
          <option value="inactive"><?= __('inactive') ?></option>
        </select>
      </div>
    </div>

    <div class="bg-white dark:bg-surface-800 rounded-xl shadow-sm border border-surface-200 dark:border-surface-700 p-6">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold text-surface-900 dark:text-white"><?= __('template_content') ?></h2>
        <span class="text-xs text-surface-500"><?= __('use_variables') ?>: {{name_bn}}, {{father_name}}, {{nid}}, etc.</span>
      </div>
      <textarea name="content" rows="20" class="w-full rounded-lg border-surface-300 dark:border-surface-600 bg-white dark:bg-surface-800 text-surface-900 dark:text-white px-3 py-2 font-mono text-sm"><?= e(old('content')) ?></textarea>
    </div>

    <div class="bg-white dark:bg-surface-800 rounded-xl shadow-sm border border-surface-200 dark:border-surface-700 p-6">
      <h2 class="text-lg font-semibold text-surface-900 dark:text-white mb-4"><?= __('variables_definition') ?></h2>
      <p class="text-sm text-surface-500 mb-4"><?= __('variables_json_help') ?></p>
      <textarea name="variables_json" rows="6" class="w-full rounded-lg border-surface-300 dark:border-surface-600 bg-white dark:bg-surface-800 text-surface-900 dark:text-white px-3 py-2 font-mono text-sm" placeholder='{"required":["name_bn","father_name"],"optional":["nid"],"labels":{"name_bn":"Name"}}'><?= e(old('variables_json')) ?></textarea>
    </div>

    <div class="flex justify-end gap-3">
      <a href="<?= url('templates') ?>" class="px-6 py-2.5 border border-surface-300 dark:border-surface-600 text-surface-700 dark:text-surface-300 rounded-lg hover:bg-surface-50 dark:hover:bg-surface-700 transition-colors">
        <?= __('cancel') ?>
      </a>
      <button type="submit" class="px-6 py-2.5 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
        <?= __('save_template') ?>
      </button>
    </div>
  </form>
</div>
