<?php
$title = $title ?? __('empty.default_title');
$description = $description ?? '';
$actionUrl = $action_url ?? null;
$actionLabel = $action_label ?? null;
?>

<div class="text-center py-12">
    <div class="w-16 h-16 mx-auto mb-4 bg-surface-secondary dark:bg-surface-dark-secondary rounded-full flex items-center justify-center">
        <svg class="w-8 h-8 text-text-tertiary dark:text-text-dark-tertiary" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
            <circle cx="9" cy="7" r="4"/>
            <line x1="19" y1="8" x2="19" y2="14"/>
            <line x1="22" y1="11" x2="16" y2="11"/>
        </svg>
    </div>
    <h3 class="text-base font-semibold text-text-primary dark:text-text-dark-primary mb-1 font-bengali"><?= e($title) ?></h3>
    <?php if ($description): ?>
        <p class="text-sm text-text-secondary dark:text-text-dark-secondary mb-6 font-bengali"><?= e($description) ?></p>
    <?php endif; ?>
    <?php if ($actionUrl && $actionLabel): ?>
        <a href="<?= e($actionUrl) ?>" class="btn-primary font-bengali"><?= e($actionLabel) ?></a>
    <?php endif; ?>
</div>