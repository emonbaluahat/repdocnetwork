<form method="POST" action="<?= url('register') ?>" class="space-y-4">
    <?= csrf_field() ?>

    <div>
        <label for="name" class="block text-sm font-medium text-text-primary dark:text-text-dark-primary mb-1 font-bengali">
            নাম
        </label>
        <input type="text" id="name" name="name" value="<?= e(old('name')) ?>"
               class="w-full px-3 py-2 border border-border dark:border-border-dark rounded-lg bg-white dark:bg-card text-text-primary dark:text-text-dark-primary placeholder-text-tertiary focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition text-sm"
               placeholder="<?= __('form.full_name') ?>" required>
    </div>

    <div>
        <label for="email" class="block text-sm font-medium text-text-primary dark:text-text-dark-primary mb-1 font-bengali">
            ইমেইল
        </label>
        <input type="email" id="email" name="email" value="<?= e(old('email')) ?>"
               class="w-full px-3 py-2 border border-border dark:border-border-dark rounded-lg bg-white dark:bg-card text-text-primary dark:text-text-dark-primary placeholder-text-tertiary focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition text-sm"
               placeholder="your@email.com" required>
    </div>

    <div>
        <label for="phone" class="block text-sm font-medium text-text-primary dark:text-text-dark-primary mb-1 font-bengali">
            ফোন নম্বর
        </label>
        <input type="tel" id="phone" name="phone" value="<?= e(old('phone')) ?>"
               class="w-full px-3 py-2 border border-border dark:border-border-dark rounded-lg bg-white dark:bg-card text-text-primary dark:text-text-dark-primary placeholder-text-tertiary focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition text-sm"
               placeholder="017xxxxxxxx" required>
    </div>

    <div>
        <label for="password" class="block text-sm font-medium text-text-primary dark:text-text-dark-primary mb-1 font-bengali">
            পাসওয়ার্ড
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

    <div>
        <label for="shop_name" class="block text-sm font-medium text-text-primary dark:text-text-dark-primary mb-1 font-bengali">
            শপের নাম (ঐচ্ছিক)
        </label>
        <input type="text" id="shop_name" name="shop_name" value="<?= e(old('shop_name')) ?>"
               class="w-full px-3 py-2 border border-border dark:border-border-dark rounded-lg bg-white dark:bg-card text-text-primary dark:text-text-dark-primary placeholder-text-tertiary focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition text-sm"
               placeholder="আপনার ডিজিটাল সেন্টারের নাম">
        <p class="mt-1 text-xs text-text-tertiary dark:text-text-dark-tertiary font-bengali">ঐচ্ছিক। পরে শপ তৈরি করতে পারেন।</p>
    </div>

    <button type="submit"
            class="w-full py-2 px-4 bg-primary-700 dark:bg-primary-600 hover:bg-primary-800 dark:hover:bg-primary-700 text-white rounded-lg font-medium text-sm transition font-bengali">
        অ্যাকাউন্ট তৈরি করুন
    </button>

    <div class="text-center text-sm text-text-secondary dark:text-text-dark-secondary font-bengali">
        ইতিমধ্যে অ্যাকাউন্ট আছে?
        <a href="<?= url('login') ?>" class="text-primary-700 dark:text-primary-400 hover:underline">
            সাইন ইন করুন
        </a>
    </div>
</form>
