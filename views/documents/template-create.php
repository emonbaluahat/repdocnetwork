<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-semibold text-text-primary dark:text-text-dark-primary font-bengali">নতুন টেমপ্লেট</h1>
        </div>
        <a href="<?= url('templates') ?>" class="btn-ghost btn-sm font-bengali">ফিরে যান</a>
    </div>

    <div class="bg-white dark:bg-card border border-border dark:border-border-dark rounded-xl p-6">
        <form method="POST" action="<?= url('templates') ?>" class="space-y-4">
            <?= csrf_field() ?>

            <div>
                <label class="label font-bengali">নাম *</label>
                <input type="text" name="name" value="<?= e(old('name')) ?>" class="input" placeholder="যেমন: সার্টিফিকেট, ইনভয়েস" required>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="label font-bengali">টেমপ্লেট টাইপ *</label>
                    <select name="template_type" class="input" required>
                        <option value="">নির্বাচন করুন</option>
                        <?php foreach ($types as $k => $v): ?>
                            <option value="<?= $k ?>" <?= old('template_type') === $k ? 'selected' : '' ?>><?= e($v) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="label font-bengali">ক্যাটাগরি</label>
                    <input type="text" name="category" value="<?= e(old('category')) ?>" class="input" placeholder="যেমন: সার্টিফিকেট, আর্থিক">
                </div>
                <div>
                    <label class="label font-bengali">পেপার সাইজ</label>
                    <select name="paper_size" class="input">
                        <?php foreach ($paper_sizes as $s): ?>
                            <option value="<?= $s ?>" <?= old('paper_size', 'A4') === $s ? 'selected' : '' ?>><?= $s ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div>
                <label class="label font-bengali">ভেরিয়েবল (কমা দিয়ে আলাদা করুন)</label>
                <input type="text" name="variables" value="<?= e(old('variables')) ?>" class="input" placeholder="customer_name, father_name, nid_number, amount">
                <p class="mt-1 text-xs text-text-tertiary dark:text-text-dark-tertiary font-bengali">টেমপ্লেট কন্টেন্টে <code class="text-primary-600">{{variable_name}}</code> হিসেবে ব্যবহার করুন। তারিখের জন্য <code>{{date:variable_name}}</code> এবং সংখ্যার জন্য <code>{{number:variable_name}}</code> ব্যবহার করুন।</p>
            </div>

            <div>
                <label class="label font-bengali">কন্টেন্ট *</label>
                <p class="text-xs text-text-tertiary dark:text-text-dark-tertiary mb-2 font-bengali">HTML ট্যাগ ব্যবহার করে টেমপ্লেট ডিজাইন করুন। শর্তসাপেক্ষ অংশের জন্য <code>{{if:variable_name}} ... {{endif}}</code> ব্যবহার করুন।</p>
                <textarea name="content" rows="15" class="input font-mono text-sm" placeholder="<div><h1>সনদপত্র</h1><p>{{customer_name}}</p>..." required><?= e(old('content')) ?></textarea>
            </div>

            <div class="flex items-center gap-3 pt-4 border-t border-border dark:border-border-dark">
                <button type="submit" class="btn-primary font-bengali">টেমপ্লেট তৈরি করুন</button>
                <a href="<?= url('templates') ?>" class="btn-ghost font-bengali">বাতিল</a>
            </div>
        </form>
    </div>
</div>
