<?php
$currentPage = $current_page ?? 1;
$totalPages = $total_pages ?? 1;
$baseUrl = $base_url ?? '/';
$query = $query ?? [];
unset($query['page']);
$queryStr = http_build_query($query);
$queryPrefix = $queryStr ? '&' : '';
?>

<?php if ($totalPages > 1): ?>
    <div class="flex items-center justify-between">
        <p class="text-sm text-text-tertiary dark:text-text-dark-tertiary">
            পৃষ্ঠা <?= e($currentPage) ?> / <?= e($totalPages) ?>
        </p>
        <div class="flex items-center gap-1">
            <?php if ($has_prev): ?>
                <a href="<?= e($baseUrl) ?>?page=1<?= e($queryPrefix . $queryStr) ?>"
                   class="px-3 py-1.5 text-sm rounded-lg border border-border dark:border-border-dark text-text-secondary dark:text-text-dark-secondary hover:bg-surface-secondary dark:hover:bg-surface-dark-secondary transition">
                    প্রথম
                </a>
                <a href="<?= e($baseUrl) ?>?page=<?= e($currentPage - 1) . e($queryPrefix . $queryStr) ?>"
                   class="px-3 py-1.5 text-sm rounded-lg border border-border dark:border-border-dark text-text-secondary dark:text-text-dark-secondary hover:bg-surface-secondary dark:hover:bg-surface-dark-secondary transition">
                    ←
                </a>
            <?php endif; ?>

            <?php
            $start = max(1, $currentPage - 2);
            $end = min($totalPages, $currentPage + 2);
            for ($i = $start; $i <= $end; $i++):
            ?>
                <a href="<?= e($baseUrl) ?>?page=<?= e($i) . e($queryPrefix . $queryStr) ?>"
                   class="px-3 py-1.5 text-sm rounded-lg transition <?= $i === $currentPage ? 'bg-primary-700 dark:bg-primary-600 text-white font-medium' : 'border border-border dark:border-border-dark text-text-secondary dark:text-text-dark-secondary hover:bg-surface-secondary dark:hover:bg-surface-dark-secondary' ?>">
                    <?= e($i) ?>
                </a>
            <?php endfor; ?>

            <?php if ($has_next): ?>
                <a href="<?= e($baseUrl) ?>?page=<?= e($currentPage + 1) . e($queryPrefix . $queryStr) ?>"
                   class="px-3 py-1.5 text-sm rounded-lg border border-border dark:border-border-dark text-text-secondary dark:text-text-dark-secondary hover:bg-surface-secondary dark:hover:bg-surface-dark-secondary transition">
                    →
                </a>
                <a href="<?= e($baseUrl) ?>?page=<?= e($totalPages) . e($queryPrefix . $queryStr) ?>"
                   class="px-3 py-1.5 text-sm rounded-lg border border-border dark:border-border-dark text-text-secondary dark:text-text-dark-secondary hover:bg-surface-secondary dark:hover:bg-surface-dark-secondary transition">
                    শেষ
                </a>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>