<div class="max-w-2xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="<?= url('staff') ?>" class="text-text-secondary dark:text-text-dark-secondary hover:text-text-primary dark:hover:text-text-dark-primary transition">
            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>
            </svg>
        </a>
        <h1 class="text-xl font-semibold text-text-primary dark:text-text-dark-primary font-bengali">স্টাফ আমন্ত্রণ</h1>
    </div>

    <div class="bg-white dark:bg-card border border-border dark:border-border-dark rounded-xl p-6">
        <form method="POST" action="<?= url('staff/invite') ?>" class="space-y-4">
            <?= csrf_field() ?>

            <div x-data="{ method: 'email' }">
                <div class="flex border-b border-border dark:border-border-dark mb-4">
                    <button @click="method = 'email'" :class="method === 'email' ? 'border-b-2 border-primary-700 dark:border-primary-400 text-primary-700 dark:text-primary-400 font-medium' : 'text-text-secondary dark:text-text-dark-secondary'" class="flex-1 pb-2 text-sm text-center transition font-bengali">
                        ইমেইল দ্বারা
                    </button>
                    <button @click="method = 'phone'" :class="method === 'phone' ? 'border-b-2 border-primary-700 dark:border-primary-400 text-primary-700 dark:text-primary-400 font-medium' : 'text-text-secondary dark:text-text-dark-secondary'" class="flex-1 pb-2 text-sm text-center transition font-bengali">
                        ফোন দ্বারা
                    </button>
                </div>

                <div x-show="method === 'email'">
                    <label for="email" class="block text-sm font-medium text-text-primary dark:text-text-dark-primary mb-1 font-bengali">
                        ইমেইল ঠিকানা
                    </label>
                    <input type="email" id="email" name="email" value="<?= e(old('email')) ?>"
                           class="w-full px-3 py-2 border border-border dark:border-border-dark rounded-lg bg-white dark:bg-card text-text-primary dark:text-text-dark-primary placeholder-text-tertiary focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition text-sm"
                           placeholder="staff@example.com">
                </div>

                <div x-show="method === 'phone'" x-cloak>
                    <label for="phone" class="block text-sm font-medium text-text-primary dark:text-text-dark-primary mb-1 font-bengali">
                        ফোন নম্বর
                    </label>
                    <input type="tel" id="phone" name="phone" value="<?= e(old('phone')) ?>"
                           class="w-full px-3 py-2 border border-border dark:border-border-dark rounded-lg bg-white dark:bg-card text-text-primary dark:text-text-dark-primary placeholder-text-tertiary focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition text-sm"
                           placeholder="017xxxxxxxx">
                </div>
            </div>

            <div>
                <label for="role" class="block text-sm font-medium text-text-primary dark:text-text-dark-primary mb-1 font-bengali">
                    ভূমিকা
                </label>
                <select id="role" name="role"
                        class="w-full px-3 py-2 border border-border dark:border-border-dark rounded-lg bg-white dark:bg-card text-text-primary dark:text-text-dark-primary focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition text-sm">
                    <option value="admin">অ্যাডমিন</option>
                    <option value="operator" selected>অপারেটর</option>
                    <option value="customer">গ্রাহক</option>
                </select>
            </div>

            <div class="flex justify-end pt-4">
                <a href="<?= url('staff') ?>" class="btn-secondary font-bengali mr-2">বাতিল</a>
                <button type="submit" class="btn-primary font-bengali">আমন্ত্রণ পাঠান</button>
            </div>
        </form>
    </div>
</div>
