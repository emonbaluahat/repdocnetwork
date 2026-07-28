<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-semibold text-text-primary dark:text-text-dark-primary font-bengali">টেমপ্লেট সম্পাদনা</h1>
            <p class="text-sm text-text-secondary dark:text-text-dark-secondary mt-1"><?= e($template['name']) ?></p>
        </div>
        <a href="<?= url('templates') ?>" class="btn-ghost btn-sm font-bengali">ফিরে যান</a>
    </div>

    <div class="bg-white dark:bg-card border border-border dark:border-border-dark rounded-xl p-6">
        <form method="POST" action="<?= url('templates/' . $template['id']) ?>" class="space-y-4">
            <?= csrf_field() ?>

            <div>
                <label class="label font-bengali">নাম *</label>
                <input type="text" name="name" value="<?= e(old('name', $template['name'])) ?>" class="input" required>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="label font-bengali">টেমপ্লেট টাইপ *</label>
                    <select name="template_type" class="input" required>
                        <?php foreach ($types as $k => $v): ?>
                            <option value="<?= $k ?>" <?= $template['template_type'] === $k ? 'selected' : '' ?>><?= e($v) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="label font-bengali">ক্যাটাগরি</label>
                    <input type="text" name="category" value="<?= e(old('category', $template['category'])) ?>" class="input">
                </div>
                <div>
                    <label class="label font-bengali">পেপার সাইজ</label>
                    <select name="paper_size" class="input">
                        <?php foreach ($paper_sizes as $s): ?>
                            <option value="<?= $s ?>" <?= ($template['paper_size'] ?? 'A4') === $s ? 'selected' : '' ?>><?= $s ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div>
                <label class="label font-bengali">ভেরিয়েবল</label>
                <?php
                $vars = json_decode($template['variables'] ?? '[]', true);
                $varsStr = is_array($vars) ? implode(', ', $vars) : '';
                ?>
                <input type="text" name="variables" value="<?= e(old('variables', $varsStr)) ?>" class="input">
            </div>

            <div>
                <label class="label font-bengali">কন্টেন্ট *</label>
                <textarea name="content" rows="15" class="input font-mono text-sm" required><?= e(old('content', $template['content'])) ?></textarea>
            </div>

            <div class="flex items-center gap-3 pt-4 border-t border-border dark:border-border-dark">
                <button type="submit" class="btn-primary font-bengali">আপডেট করুন</button>
                <a href="<?= url('templates') ?>" class="btn-ghost font-bengali">বাতিল</a>
            </div>
        </form>
    </div>
</div>
