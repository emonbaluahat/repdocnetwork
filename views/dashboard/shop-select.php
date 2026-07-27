<div class="max-w-lg mx-auto mt-12">
    <h1 class="text-xl font-semibold text-text-primary dark:text-text-dark-primary mb-2 font-bengali">
        একটি শপ নির্বাচন করুন
    </h1>
    <p class="text-sm text-text-secondary dark:text-text-dark-secondary mb-6 font-bengali">
        আপনি একাধিক শপের সাথে যুক্ত। কাজ শুরু করতে একটি শপ নির্বাচন করুন।
    </p>

    <?php if (!empty($shops)): ?>
        <div class="space-y-3">
            <?php foreach ($shops as $shop): ?>
                <a href="<?= url('shop/switch/' . $shop['id']) ?>"
                   class="block bg-white dark:bg-card border border-border dark:border-border-dark rounded-xl p-4 hover:border-primary-300 dark:hover:border-primary-700 transition">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="font-medium text-text-primary dark:text-text-dark-primary font-bengali">
                                <?= e($shop['name']) ?>
                            </h3>
                            <p class="text-sm text-text-secondary dark:text-text-dark-secondary mt-1 font-bengali">
                                <?= e(__('role.' . $shop['role'])) ?>
                            </p>
                        </div>
                        <svg class="w-5 h-5 text-text-tertiary dark:text-text-dark-tertiary" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="9 18 15 12 9 6"/>
                        </svg>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-8 text-text-tertiary dark:text-text-dark-tertiary border-2 border-dashed border-border dark:border-border-dark rounded-xl">
            <p class="text-sm font-bengali">আপনি কোনো শপের সাথে যুক্ত নন</p>
        </div>
    <?php endif; ?>
</div>
