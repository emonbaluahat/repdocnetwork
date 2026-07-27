<!DOCTYPE html>
<html lang="bn" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <title><?= e($title ?? __('auth.login')) ?> — <?= e(APP_NAME) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Noto+Sans+Bengali:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('assets/css/style.css') ?>">
    <link rel="stylesheet" href="<?= asset('assets/css/fonts.css') ?>">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-surface-secondary dark:bg-surface-dark min-h-screen flex items-center justify-center p-4">

<div class="w-full max-w-md">
    <div class="text-center mb-8">
        <h1 class="text-2xl font-bold font-bengali text-text-primary dark:text-text-dark-primary">
            <?= e(APP_NAME) ?>
        </h1>
        <p class="text-text-secondary dark:text-text-dark-secondary mt-1 font-bengali">
            <?= e($subtitle ?? __('auth.welcome')) ?>
        </p>
    </div>

    <?php if (flash('error')): ?>
        <div class="mb-4 p-4 bg-error-50 dark:bg-error-700/20 border border-error-200 dark:border-error-700/30 rounded-lg text-error-700 dark:text-error-400 text-sm font-bengali">
            <?= e(flash('error')) ?>
        </div>
    <?php endif; ?>
    <?php if (flash('success')): ?>
        <div class="mb-4 p-4 bg-success-50 dark:bg-success-700/20 border border-success-200 dark:border-success-700/30 rounded-lg text-success-700 dark:text-success-400 text-sm font-bengali">
            <?= e(flash('success')) ?>
        </div>
    <?php endif; ?>
    <?php if (flash('warning')): ?>
        <div class="mb-4 p-4 bg-warning-50 dark:bg-warning-700/20 border border-warning-200 dark:border-warning-700/30 rounded-lg text-warning-700 dark:text-warning-400 text-sm font-bengali">
            <?= e(flash('warning')) ?>
        </div>
    <?php endif; ?>
    <?php
    $errors = $_SESSION['_errors'] ?? [];
    unset($_SESSION['_errors']);
    if (!empty($errors)):
    ?>
        <div class="mb-4 p-4 bg-error-50 dark:bg-error-700/20 border border-error-200 dark:border-error-700/30 rounded-lg text-error-700 dark:text-error-400 text-sm font-bengali">
            <ul class="list-disc list-inside space-y-0.5">
                <?php foreach ($errors as $field => $fieldErrors): ?>
                    <?php foreach ($fieldErrors as $err): ?>
                        <li><?= e($err) ?></li>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="bg-white dark:bg-card border border-border dark:border-border-dark rounded-xl shadow-sm p-6">
        <?= $content ?? '' ?>
    </div>
</div>

</body>
</html>
