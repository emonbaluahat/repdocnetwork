<div class="max-w-7xl mx-auto">
  <div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-surface-900 dark:text-white"><?= __('certificate_types') ?></h1>
    <div class="flex gap-2">
      <a href="<?= url('certificates/requests/create') ?>" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
        <?= __('new_request') ?>
      </a>
      <a href="<?= url('certificates/requests') ?>" class="inline-flex items-center px-4 py-2 border border-surface-300 dark:border-surface-600 text-surface-700 dark:text-surface-300 rounded-lg hover:bg-surface-50 dark:hover:bg-surface-700 transition-colors">
        <?= __('view_requests') ?>
      </a>
    </div>
  </div>

  <div class="grid grid-cols-1 gap-6">
    <?php foreach ($categories as $catKey => $catLabel): ?>
    <?php $catTypes = array_filter($types, fn($t) => $t['category'] === $catKey); ?>
    <?php if (empty($catTypes)) continue; ?>

    <div class="bg-white dark:bg-surface-800 rounded-xl shadow-sm border border-surface-200 dark:border-surface-700 overflow-hidden">
      <div class="px-6 py-4 bg-surface-50 dark:bg-surface-700/50 border-b border-surface-200 dark:border-surface-700">
        <h2 class="text-lg font-semibold text-surface-900 dark:text-white"><?= e($catLabel) ?></h2>
      </div>
      <div class="divide-y divide-surface-200 dark:divide-surface-700">
        <?php foreach ($catTypes as $type): ?>
        <div class="px-6 py-4 flex items-center justify-between hover:bg-surface-50 dark:hover:bg-surface-700/50 transition-colors">
          <div>
            <span class="font-medium text-surface-900 dark:text-white"><?= e($type['name_bn']) ?></span>
            <span class="text-sm text-surface-500 ml-2">(<?= e($type['name_en']) ?>)</span>
            <?php if ($type['fee'] > 0): ?>
            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-primary-100 text-primary-700 ml-2">৳<?= number_format($type['fee'], 0) ?></span>
            <?php endif; ?>
          </div>
          <div class="flex items-center gap-2">
            <a href="<?= url('certificates/types/' . $type['id'] . '/fields') ?>" class="text-sm text-primary-600 dark:text-primary-400 hover:underline">
              <?= __('fields') ?> (<?= count(CertificateType::getFields($type['id'])) ?>)
            </a>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <?php if (empty($types)): ?>
  <div class="text-center py-12 text-surface-500">
    <?= __('no_certificate_types') ?>
  </div>
  <?php endif; ?>
</div>
