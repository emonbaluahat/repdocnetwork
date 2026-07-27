<form method="POST" action="<?= url('reset-password') ?>" class="space-y-4">
    <?= csrf_field() ?>

    <input type="hidden" name="token" value="<?= e($token ?? '') ?>">
    <input type="hidden" name="email" value="<?= e($email ?? '') ?>">
    <input type="hidden" name="phone" value="<?= e($phone ?? '') ?>">

    <div>
        <label for="password" class="block text-sm font-medium text-text-primary dark:text-text-dark-primary mb-1 font-bengali">
            নতুন পাসওয়ার্ড
        </label>
        <input type="password" id="password" name="password"
               class="w-full px-3 py-2 border border-border dark:border-border-dark rounded-lg bg-white dark:bg-card text-text-primary dark:text-text-dark-primary placeholder-text-tertiary focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition text-sm"
               placeholder="মিনিমাম ৮ ক্যারেক্টার" required minlength="8">
    </div>

    <div>
        <label for="password_confirmation" class="block text-sm font-medium text-text-primary dark:text-text-dark-primary mb-1 font-bengali">
            পাসওয়ার্ড নিশ্চিত করুন
        </label>
        <input type="password" id="password_confirmation" name="password_confirmation"
               class="w-full px-3 py-2 border border-border dark:border-border-dark rounded-lg bg-white dark:bg-card text-text-primary dark:text-text-dark-primary placeholder-text-tertiary focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition text-sm"
               placeholder="আবার পাসওয়ার্ড দিন" required>
    </div>

    <button type="submit"
            class="w-full py-2 px-4 bg-primary-700 dark:bg-primary-600 hover:bg-primary-800 dark:hover:bg-primary-700 text-white rounded-lg font-medium text-sm transition font-bengali">
        পাসওয়ার্ড রিসেট করুন
    </button>
</form>
