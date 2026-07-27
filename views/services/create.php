<div class="max-w-2xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-semibold text-text-primary dark:text-text-dark-primary font-bengali">নতুন সার্ভিস</h1>
        </div>
        <a href="<?= url('services') ?>" class="btn-ghost btn-sm font-bengali">ফিরে যান</a>
    </div>

    <div class="bg-white dark:bg-card border border-border dark:border-border-dark rounded-xl p-6">
        <form method="POST" action="<?= url('services') ?>" class="space-y-4">
            <?= csrf_field() ?>

            <div>
                <label for="name" class="label font-bengali">সার্ভিসের নাম *</label>
                <input type="text" id="name" name="name" value="<?= e(old('name')) ?>"
                       class="input" placeholder="যেমন: পাসপোর্ট সাইজ ছবি" required>
            </div>

            <div>
                <label for="category_id" class="label font-bengali">ক্যাটাগরি</label>
                <select id="category_id" name="category_id" class="input">
                    <option value="">ক্যাটাগরি নির্বাচন করুন</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= old('category_id') == $cat['id'] ? 'selected' : '' ?>>
                            <?= e($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="price" class="label font-bengali">মূল্য *</label>
                    <input type="number" id="price" name="price" step="0.01" min="0"
                           value="<?= e(old('price', '0')) ?>"
                           class="input" placeholder="০.০০" required>
                </div>
                <div>
                    <label for="cost_price" class="label font-bengali">খরচ মূল্য</label>
                    <input type="number" id="cost_price" name="cost_price" step="0.01" min="0"
                           value="<?= e(old('cost_price', '0')) ?>"
                           class="input" placeholder="০.০০">
                </div>
                <div>
                    <label for="unit" class="label font-bengali">ইউনিট</label>
                    <input type="text" id="unit" name="unit" value="<?= e(old('unit', 'pcs')) ?>"
                           class="input" placeholder="pcs">
                </div>
            </div>

            <div>
                <label for="description" class="label font-bengali">বিবরণ</label>
                <textarea id="description" name="description" rows="3"
                          class="input" placeholder="সার্ভিস সম্পর্কে বিস্তারিত"><?= e(old('description')) ?></textarea>
            </div>

            <div>
                <label for="sort_order" class="label font-bengali">সাজানোর ক্রম</label>
                <input type="number" id="sort_order" name="sort_order" min="0"
                       value="<?= e(old('sort_order', '0')) ?>"
                       class="input w-24" placeholder="০">
            </div>

            <div class="flex items-center gap-3 pt-4 border-t border-border dark:border-border-dark">
                <button type="submit" class="btn-primary font-bengali">সার্ভিস যোগ করুন</button>
                <a href="<?= url('services') ?>" class="btn-ghost font-bengali">বাতিল</a>
            </div>
        </form>
    </div>
</div>