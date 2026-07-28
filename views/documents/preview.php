<div class="max-w-4xl mx-auto py-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-lg font-semibold text-text-primary dark:text-text-dark-primary font-bengali">প্রিভিউ: <?= e($template['name']) ?></h1>
        <button onclick="window.print()" class="btn-primary btn-sm font-bengali">প্রিন্ট</button>
    </div>
    <div class="bg-white dark:bg-card border border-border dark:border-border-dark rounded-xl p-8 shadow-sm">
        <?= $rendered ?>
    </div>
</div>
