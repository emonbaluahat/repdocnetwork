<?php
$type = $type ?? 'info';
$message = $message ?? '';
$styles = [
    'success' => 'bg-success-50 dark:bg-success-700/20 border-success-200 dark:border-success-700/30 text-success-700 dark:text-success-400',
    'error' => 'bg-error-50 dark:bg-error-700/20 border-error-200 dark:border-error-700/30 text-error-700 dark:text-error-400',
    'warning' => 'bg-warning-50 dark:bg-warning-700/20 border-warning-200 dark:border-warning-700/30 text-warning-700 dark:text-warning-400',
    'info' => 'bg-info-50 dark:bg-info-700/20 border-info-200 dark:border-info-700/30 text-info-700 dark:text-info-400',
];
$style = $styles[$type] ?? $styles['info'];
?>

<div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
     class="mb-4 p-4 border rounded-lg text-sm font-bengali flex items-center justify-between <?= $style ?>"
     role="alert">
    <span><?= e($message) ?></span>
    <button @click="show = false" class="ml-4 flex-shrink-0 opacity-60 hover:opacity-100 transition">
        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
        </svg>
    </button>
</div>
