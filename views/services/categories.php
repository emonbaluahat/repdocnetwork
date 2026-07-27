<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-semibold text-text-primary dark:text-text-dark-primary font-bengali">সার্ভিস ক্যাটাগরি</h1>
            <p class="text-sm text-text-secondary dark:text-text-dark-secondary mt-1 font-bengali">মোট <?= count($categories) ?> টি ক্যাটাগরি</p>
        </div>
        <a href="<?= url('services') ?>" class="btn-ghost btn-sm font-bengali">সার্ভিস তালিকা</a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="bg-white dark:bg-card border border-border dark:border-border-dark rounded-xl p-6">
            <h2 class="text-sm font-semibold text-text-primary dark:text-text-dark-primary mb-4 font-bengali">নতুন ক্যাটাগরি</h2>
            <form method="POST" action="<?= url('services/categories') ?>" class="space-y-3">
                <?= csrf_field() ?>
                <div>
                    <input type="text" name="name" class="input" placeholder="ক্যাটাগরির নাম" required>
                </div>
                <div>
                    <textarea name="description" rows="2" class="input" placeholder="বিবরণ (ঐচ্ছিক)"></textarea>
                </div>
                <button type="submit" class="btn-primary w-full font-bengali">যোগ করুন</button>
            </form>
        </div>

        <div class="lg:col-span-2 space-y-3">
            <?php if (empty($categories)): ?>
                <div class="bg-white dark:bg-card border border-border dark:border-border-dark rounded-xl p-8 text-center text-text-tertiary font-bengali">
                    কোনো ক্যাটাগরি নেই। নতুন ক্যাটাগরি তৈরি করুন।
                </div>
            <?php else: ?>
                <?php foreach ($categories as $cat): ?>
                    <div class="bg-white dark:bg-card border border-border dark:border-border-dark rounded-xl p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-semibold text-text-primary dark:text-text-dark-primary font-bengali"><?= e($cat['name']) ?></h3>
                                <?php if ($cat['description']): ?>
                                    <p class="text-xs text-text-secondary dark:text-text-dark-secondary mt-0.5"><?= e($cat['description']) ?></p>
                                <?php endif; ?>
                                <p class="text-xs text-text-tertiary dark:text-text-dark-tertiary mt-1 font-bengali">
                                    <?= (int) ($cat['service_count'] ?? 0) ?> টি সার্ভিস
                                </p>
                            </div>
                            <div class="flex items-center gap-2">
                                <button onclick="editCategory(<?= $cat['id'] ?>, '<?= e($cat['name'], ENT_QUOTES) ?>', '<?= e($cat['description'] ?? '', ENT_QUOTES) ?>')"
                                        class="btn-ghost btn-sm font-bengali">সম্পাদনা</button>
                                <form method="POST" action="<?= url('services/categories/' . $cat['id'] . '/delete') ?>"
                                      onsubmit="return confirm('এই ক্যাটাগরি মুছে ফেললে সব সার্ভিস ক্যাটাগরিহীন হয়ে যাবে। নিশ্চিত?')">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn-ghost btn-sm text-error-600 font-bengali">মুছুন</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<div id="editModal" class="fixed inset-0 z-50 hidden" x-data>
    <div class="absolute inset-0 bg-black/50" @click="document.getElementById('editModal').classList.add('hidden')"></div>
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-card rounded-xl p-6 w-full max-w-md shadow-xl">
            <h3 class="text-sm font-semibold text-text-primary dark:text-text-dark-primary mb-4 font-bengali">ক্যাটাগরি সম্পাদনা</h3>
            <form method="POST" action="" id="editForm" class="space-y-3">
                <?= csrf_field() ?>
                <div>
                    <input type="text" name="name" id="editName" class="input" placeholder="ক্যাটাগরির নাম" required>
                </div>
                <div>
                    <textarea name="description" id="editDescription" rows="2" class="input" placeholder="বিবরণ (ঐচ্ছিক)"></textarea>
                </div>
                <div class="flex items-center gap-2">
                    <button type="submit" class="btn-primary font-bengali">আপডেট করুন</button>
                    <button type="button" class="btn-ghost font-bengali" onclick="document.getElementById('editModal').classList.add('hidden')">বাতিল</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editCategory(id, name, description) {
    document.getElementById('editForm').action = '/services/categories/' + id;
    document.getElementById('editName').value = name;
    document.getElementById('editDescription').value = description;
    document.getElementById('editModal').classList.remove('hidden');
}
</script>