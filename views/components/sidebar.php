<?php
$currentUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$isSuperAdmin = !empty($_SESSION['user']['is_super_admin']);

$navItems = [
    ['label' => 'ড্যাশবোর্ড', 'icon' => 'LayoutDashboard', 'route' => '/', 'shortcut' => '⌘1'],
    ['label' => 'গ্রাহক', 'icon' => 'Users', 'route' => '/customers', 'shortcut' => '⌘2'],
    ['label' => 'অর্ডার', 'icon' => 'ShoppingCart', 'route' => '/orders', 'shortcut' => '⌘3'],
    ['label' => 'ডকুমেন্ট', 'icon' => 'FileText', 'route' => '/documents', 'shortcut' => '⌘4'],
    ['label' => 'টেমপ্লেট', 'icon' => 'File', 'route' => '/templates', 'shortcut' => '⌘5'],
    ['label' => 'সেটিংস', 'icon' => 'Settings', 'route' => '/settings', 'shortcut' => '⌘6'],
];

$adminItems = [];
if ($isSuperAdmin):
    $adminItems = [
        ['label' => 'অ্যাডমিন', 'icon' => 'Shield', 'route' => '#', 'shortcut' => '', 'is_header' => true],
        ['label' => 'ব্যবহারকারী', 'icon' => 'Users', 'route' => '/admin/users', 'shortcut' => ''],
        ['label' => 'অনুমতি', 'icon' => 'Key', 'route' => '/admin/permissions', 'shortcut' => ''],
        ['label' => 'অডিট লগ', 'icon' => 'FileText', 'route' => '/admin/audit-logs', 'shortcut' => ''],
    ];
endif;
?>

<aside class="bg-surface-secondary dark:bg-surface-dark-secondary border-r border-border dark:border-border-dark flex flex-col transition-all duration-150"
       :class="sidebar ? 'w-sidebar' : 'w-sidebar-collapsed'">
    <div class="h-12 flex items-center px-4 border-b border-border dark:border-border-dark">
        <div class="flex items-center gap-2" :class="sidebar ? '' : 'justify-center w-full'">
            <div class="w-7 h-7 bg-primary-700 dark:bg-primary-600 rounded-lg flex items-center justify-center flex-shrink-0">
                <span class="text-white text-xs font-bold">RD</span>
            </div>
            <span x-show="sidebar" class="text-sm font-semibold text-text-primary dark:text-text-dark-primary truncate">
                <?= e(APP_NAME) ?>
            </span>
        </div>
    </div>

    <nav class="flex-1 py-3 space-y-1 px-2 overflow-y-auto">
        <?php foreach ($navItems as $item): ?>
            <a href="<?= url($item['route']) ?>"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition <?= strpos($currentUri, $item['route']) === 0 && strlen($currentUri) > 1 ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400 font-medium' : ($currentUri === $item['route'] ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400 font-medium' : 'text-text-secondary dark:text-text-dark-secondary hover:bg-gray-100 dark:hover:bg-gray-800') ?>"
               :title="sidebar ? '' : '<?= e($item['label']) ?>'">
                <svg class="w-5 h-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <use href="<?= asset('assets/icons/sprite.svg') ?>#<?= $item['icon'] ?>"/>
                </svg>
                <span x-show="sidebar" class="font-bengali"><?= e($item['label']) ?></span>
                <span x-show="sidebar" class="ml-auto text-xs text-text-tertiary dark:text-text-dark-tertiary hidden lg:inline"><?= $item['shortcut'] ?></span>
            </a>
        <?php endforeach; ?>

        <?php if (!empty($adminItems)): ?>
            <div class="pt-3 mt-3 border-t border-border dark:border-border-dark"></div>
            <?php foreach ($adminItems as $item): ?>
                <a href="<?= url($item['route']) ?>"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition <?= strpos($currentUri, $item['route']) === 0 && $item['route'] !== '#' ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400 font-medium' : 'text-text-secondary dark:text-text-dark-secondary hover:bg-gray-100 dark:hover:bg-gray-800' ?>"
                   :title="sidebar ? '' : '<?= e($item['label']) ?>'">
                    <svg class="w-5 h-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <use href="<?= asset('assets/icons/sprite.svg') ?>#<?= $item['icon'] ?>"/>
                    </svg>
                    <span x-show="sidebar" class="font-bengali"><?= e($item['label']) ?></span>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </nav>

    <div class="p-3 border-t border-border dark:border-border-dark">
        <button @click="sidebar = !sidebar"
                class="w-full flex items-center gap-3 px-3 py-2 text-text-secondary dark:text-text-dark-secondary hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg text-sm transition">
            <svg class="w-5 h-5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                <line x1="9" y1="3" x2="9" y2="21"/>
            </svg>
            <span x-show="sidebar" class="font-bengali">সঙ্কুচিত করুন</span>
        </button>
    </div>
</aside>
