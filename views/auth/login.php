<div x-data="{ tab: 'email' }">
    <div class="flex border-b border-border dark:border-border-dark mb-4">
        <button @click="tab = 'email'" :class="tab === 'email' ? 'border-b-2 border-primary-700 dark:border-primary-400 text-primary-700 dark:text-primary-400 font-medium' : 'text-text-secondary dark:text-text-dark-secondary hover:text-text-primary dark:hover:text-text-dark-primary'" class="flex-1 pb-2 text-sm text-center transition font-bengali">
            ইমেইল
        </button>
        <button @click="tab = 'phone'" :class="tab === 'phone' ? 'border-b-2 border-primary-700 dark:border-primary-400 text-primary-700 dark:text-primary-400 font-medium' : 'text-text-secondary dark:text-text-dark-secondary hover:text-text-primary dark:hover:text-text-dark-primary'" class="flex-1 pb-2 text-sm text-center transition font-bengali">
            ফোন
        </button>
        <button @click="tab = 'username'" :class="tab === 'username' ? 'border-b-2 border-primary-700 dark:border-primary-400 text-primary-700 dark:text-primary-400 font-medium' : 'text-text-secondary dark:text-text-dark-secondary hover:text-text-primary dark:hover:text-text-dark-primary'" class="flex-1 pb-2 text-sm text-center transition font-bengali">
            ইউজারনেম
        </button>
    </div>

    <form method="POST" action="<?= url('login') ?>" class="space-y-4">
        <?= csrf_field() ?>

        <div x-show="tab === 'email'">
            <label for="login-email" class="block text-sm font-medium text-text-primary dark:text-text-dark-primary mb-1 font-bengali">
                ইমেইল
            </label>
            <input type="email" id="login-email" name="login" value="<?= e(old('login')) ?>"
                   :disabled="tab !== 'email'"
                   class="w-full px-3 py-2 border border-border dark:border-border-dark rounded-lg bg-white dark:bg-card text-text-primary dark:text-text-dark-primary placeholder-text-tertiary focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition text-sm"
                   placeholder="your@email.com">
        </div>

        <div x-show="tab === 'phone'" x-cloak>
            <label for="login-phone" class="block text-sm font-medium text-text-primary dark:text-text-dark-primary mb-1 font-bengali">
                ফোন নম্বর
            </label>
            <input type="tel" id="login-phone" name="login" value="<?= e(old('login')) ?>"
                   :disabled="tab !== 'phone'"
                   class="w-full px-3 py-2 border border-border dark:border-border-dark rounded-lg bg-white dark:bg-card text-text-primary dark:text-text-dark-primary placeholder-text-tertiary focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition text-sm"
                   placeholder="017xxxxxxxx">
        </div>

        <div x-show="tab === 'username'" x-cloak>
            <label for="login-username" class="block text-sm font-medium text-text-primary dark:text-text-dark-primary mb-1 font-bengali">
                ইউজারনেম
            </label>
            <input type="text" id="login-username" name="login" value="<?= e(old('login')) ?>"
                   :disabled="tab !== 'username'"
                   class="w-full px-3 py-2 border border-border dark:border-border-dark rounded-lg bg-white dark:bg-card text-text-primary dark:text-text-dark-primary placeholder-text-tertiary focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition text-sm"
                   placeholder="username">
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-text-primary dark:text-text-dark-primary mb-1 font-bengali">
                পাসওয়ার্ড
            </label>
            <input type="password" id="password" name="password"
                   class="w-full px-3 py-2 border border-border dark:border-border-dark rounded-lg bg-white dark:bg-card text-text-primary dark:text-text-dark-primary placeholder-text-tertiary focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition text-sm"
                   placeholder="••••••••" required>
        </div>

        <div class="flex items-center justify-between text-sm">
            <label class="flex items-center gap-2 cursor-pointer text-text-secondary dark:text-text-dark-secondary">
                <input type="checkbox" name="remember" class="rounded border-border dark:border-border-dark text-primary-600 focus:ring-primary-500">
                <span class="font-bengali">মনে রাখুন</span>
            </label>
            <a href="<?= url('forgot-password') ?>" class="text-primary-700 dark:text-primary-400 hover:underline font-bengali">
                পাসওয়ার্ড ভুলে গেছেন?
            </a>
        </div>

        <button type="submit"
                class="w-full py-2 px-4 bg-primary-700 dark:bg-primary-600 hover:bg-primary-800 dark:hover:bg-primary-700 text-white rounded-lg font-medium text-sm transition font-bengali">
            সাইন ইন
        </button>

        <div class="text-center text-sm text-text-secondary dark:text-text-dark-secondary font-bengali">
            অ্যাকাউন্ট নেই?
            <a href="<?= url('register') ?>" class="text-primary-700 dark:text-primary-400 hover:underline">
                রেজিস্টার করুন
            </a>
        </div>
    </form>
</div>
