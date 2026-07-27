<header class="h-12 border-b border-border dark:border-border-dark bg-white dark:bg-surface-dark-secondary flex items-center px-4 gap-3 flex-shrink-0">
    <div class="flex-1 flex items-center gap-2">
        <button @click=""
                class="flex items-center gap-2 px-3 py-1.5 bg-surface-secondary dark:bg-gray-800 border border-border dark:border-border-dark rounded-lg text-sm text-text-secondary dark:text-text-dark-secondary hover:text-text-primary dark:hover:text-text-dark-primary transition w-full max-w-md">
            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <span class="font-bengali">সার্চ করুন...</span>
            <span class="ml-auto text-xs text-text-tertiary dark:text-text-dark-tertiary hidden sm:inline border border-border dark:border-border-dark rounded px-1.5 py-0.5">⌘K</span>
        </button>
    </div>

    <div class="flex items-center gap-2">
        <button @click="darkMode = !darkMode; localStorage.setItem('theme', darkMode ? 'dark' : 'light')"
                class="p-2 rounded-lg text-text-secondary dark:text-text-dark-secondary hover:bg-gray-100 dark:hover:bg-gray-800 transition"
                title="Toggle theme">
            <svg x-show="!darkMode" class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
            </svg>
            <svg x-show="darkMode" class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
            </svg>
        </button>

        <div class="flex items-center gap-2 pl-2 border-l border-border dark:border-border-dark">
            <div class="w-7 h-7 bg-primary-100 dark:bg-primary-900/30 rounded-full flex items-center justify-center text-xs font-semibold text-primary-700 dark:text-primary-400">
                <?= e(mb_substr(session('user.name', 'U'), 0, 1, 'UTF-8')) ?>
            </div>
            <span class="text-sm text-text-primary dark:text-text-dark-primary hidden sm:inline font-bengali">
                <?= e(session('user.name', '')) ?>
            </span>
            <form method="POST" action="<?= url('/logout') ?>" class="inline">
                <input type="hidden" name="_csrf_token" value="<?= csrf_token() ?>">
                <button type="submit"
                        class="ml-2 text-xs text-text-tertiary dark:text-text-dark-tertiary hover:text-red-600 dark:hover:text-red-400 transition font-bengali">
                    লগআউট
                </button>
            </form>
        </div>
    </div>
</header>
