<div class="max-w-4xl mx-auto">
  <div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-surface-900 dark:text-white"><?= __('create_certificate_request') ?></h1>
    <a href="<?= url('certificates/requests') ?>" class="text-sm text-primary-600 dark:text-primary-400 hover:underline"><?= __('back_to_requests') ?></a>
  </div>

  <form method="POST" action="<?= url('certificates/requests') ?>" class="space-y-6">
    <?= csrf_field() ?>

    <div class="bg-white dark:bg-surface-800 rounded-xl shadow-sm border border-surface-200 dark:border-surface-700 p-6">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-2"><?= __('certificate_type') ?></label>
          <select name="certificate_type_id" required class="w-full rounded-lg border-surface-300 dark:border-surface-600 bg-white dark:bg-surface-800 text-surface-900 dark:text-white px-3 py-2">
            <option value=""><?= __('select_type') ?></option>
            <?php foreach ($types as $type): ?>
            <option value="<?= $type['id'] ?>"><?= e($type['name_bn']) ?> (<?= e($type['name_en']) ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-surface-700 dark:text-surface-300 mb-2"><?= __('customer') ?></label>
          <select name="customer_id" class="w-full rounded-lg border-surface-300 dark:border-surface-600 bg-white dark:bg-surface-800 text-surface-900 dark:text-white px-3 py-2">
            <option value=""><?= __('no_customer') ?></option>
            <?php foreach ($customers as $cust): ?>
            <option value="<?= $cust['id'] ?>"><?= e($cust['name']) ?> (<?= e($cust['phone'] ?? '') ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
    </div>

    <div class="bg-white dark:bg-surface-800 rounded-xl shadow-sm border border-surface-200 dark:border-surface-700 p-6">
      <h2 class="text-lg font-semibold text-surface-900 dark:text-white mb-4"><?= __('form_fields') ?></h2>
      <div id="dynamic-fields" class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="col-span-full text-center py-8 text-surface-400">
          <?= __('select_type_first') ?>
        </div>
      </div>
    </div>

    <div class="flex justify-end gap-3">
      <a href="<?= url('certificates/requests') ?>" class="px-6 py-2.5 border border-surface-300 dark:border-surface-600 text-surface-700 dark:text-surface-300 rounded-lg hover:bg-surface-50 dark:hover:bg-surface-700 transition-colors">
        <?= __('cancel') ?>
      </a>
      <button type="submit" class="px-6 py-2.5 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
        <?= __('submit_request') ?>
      </button>
    </div>
  </form>
</div>
