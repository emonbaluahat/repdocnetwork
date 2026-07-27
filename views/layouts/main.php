<!DOCTYPE html>
<html lang="bn" dir="ltr" x-data="{ sidebar: true, darkMode: localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches) }" :class="{ 'dark': darkMode }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <title><?= e($title ?? APP_NAME) ?> — <?= e(APP_NAME) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Noto+Sans+Bengali:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('assets/css/style.css') ?>">
    <link rel="stylesheet" href="<?= asset('assets/css/fonts.css') ?>">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-white dark:bg-surface-dark text-text-primary dark:text-text-dark-primary antialiased">

<div class="flex h-screen overflow-hidden">
    <?php \App\Core\View::component('sidebar'); ?>

    <div class="flex-1 flex flex-col min-w-0">
        <?php \App\Core\View::component('topbar'); ?>

        <main class="flex-1 overflow-y-auto p-8">
            <?php if (flash('error')): ?>
                <?php \App\Core\View::component('toast', ['type' => 'error', 'message' => flash('error')]); ?>
            <?php endif; ?>
            <?php if (flash('success')): ?>
                <?php \App\Core\View::component('toast', ['type' => 'success', 'message' => flash('success')]); ?>
            <?php endif; ?>
            <?php if (flash('warning')): ?>
                <?php \App\Core\View::component('toast', ['type' => 'warning', 'message' => flash('warning')]); ?>
            <?php endif; ?>
            <?php if (flash('info')): ?>
                <?php \App\Core\View::component('toast', ['type' => 'info', 'message' => flash('info')]); ?>
            <?php endif; ?>

            <?= $content ?? '' ?>
        </main>
    </div>
</div>

<script src="<?= asset('assets/js/utils.js') ?>"></script>
<script src="<?= asset('assets/js/app.js') ?>"></script>
</body>
</html>
