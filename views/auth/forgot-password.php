<form method="POST" action="<?= url('forgot-password') ?>" class="space-y-4">
    <?= csrf_field() ?>

    <div>
        <label for="login" class="block text-sm font-medium text-text-primary dark:text-text-dark-primary mb-1 font-bengali">
            ইমেইল বা ফোন নম্বর
        </label>
        <input type="text" id="login" name="login" value="<?= e(old('login')) ?>"
               class="w-full px-3 py-2 border border-border dark:border-border-dark rounded-lg bg-white dark:bg-card text-text-primary dark:text-text-dark-primary placeholder-text-tertiary focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition text-sm"
               placeholder="your@email.com বা 017xxxxxxxx" required autofocus>
    </div>

    <button type="submit"
            class="w-full py-2 px-4 bg-primary-700 dark:bg-primary-600 hover:bg-primary-800 dark:hover:bg-primary-700 text-white rounded-lg font-medium text-sm transition font-bengali">
        রিসেট লিংক পাঠান
    </button>

    <div class="text-center text-sm text-text-secondary dark:text-text-dark-secondary font-bengali">
        <a href="<?= url('login') ?>" class="text-primary-700 dark:text-primary-400 hover:underline">
            সাইন ইন এ ফিরে যান
        </a>
    </div>
</form>
