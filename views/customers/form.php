<div class="max-w-2xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-semibold text-text-primary dark:text-text-dark-primary font-bengali">
                <?= $customer ? 'গ্রাহক সম্পাদনা' : 'নতুন গ্রাহক' ?>
            </h1>
        </div>
        <a href="<?= url('customers') ?>" class="btn-ghost btn-sm font-bengali">ফিরে যান</a>
    </div>

    <div class="bg-white dark:bg-card border border-border dark:border-border-dark rounded-xl p-6">
        <form method="POST" action="<?= $customer ? url('customers/' . $customer['id']) : url('customers') ?>" class="space-y-4">
            <?= csrf_field() ?>

            <div>
                <label for="name" class="label font-bengali">নাম *</label>
                <input type="text" id="name" name="name" value="<?= e(old('name', $customer['name'] ?? '')) ?>"
                       class="input" placeholder="গ্রাহকের নাম" required>
            </div>

            <div>
                <label for="phone" class="label font-bengali">ফোন নম্বর *</label>
                <input type="tel" id="phone" name="phone" value="<?= e(old('phone', $customer['phone'] ?? '')) ?>"
                       class="input" placeholder="017xxxxxxxx" required>
            </div>

            <div>
                <label for="email" class="label font-bengali">ইমেইল</label>
                <input type="email" id="email" name="email" value="<?= e(old('email', $customer['email'] ?? '')) ?>"
                       class="input" placeholder="your@email.com">
            </div>

            <div>
                <label for="nid" class="label font-bengali">এনআইডি নম্বর</label>
                <input type="text" id="nid" name="nid" value="<?= e(old('nid', $customer['nid'] ?? '')) ?>"
                       class="input" placeholder="১০ বা ১৭ ডিজিটের এনআইডি">
            </div>

            <div>
                <label for="address" class="label font-bengali">ঠিকানা</label>
                <textarea id="address" name="address" rows="2"
                          class="input" placeholder="গ্রাম/রাস্তা, থানা, জেলা"><?= e(old('address', $customer['address'] ?? '')) ?></textarea>
            </div>

            <div>
                <label for="tags" class="label font-bengali">ট্যাগ</label>
                <input type="text" id="tags" name="tags"
                       value="<?php
                       $tagStr = '';
                       if ($customer) {
                           $tags = json_decode($customer['tags'] ?? '[]', true);
                           $tagStr = is_array($tags) ? implode(', ', $tags) : '';
                       }
                       echo e(old('tags', $tagStr));
                       ?>"
                       class="input" placeholder="প্রিমিয়াম, রেগুলার, ভিআইপি (কমা দিয়ে আলাদা করুন)">
                <p class="mt-1 text-xs text-text-tertiary dark:text-text-dark-tertiary font-bengali">একাধিক ট্যাগ কমা (,) দিয়ে আলাদা করুন</p>
            </div>

            <div>
                <label for="notes" class="label font-bengali">নোট</label>
                <textarea id="notes" name="notes" rows="3"
                          class="input" placeholder="গ্রাহক সম্পর্কে কোনো বিশেষ নোট"><?= e(old('notes', $customer['notes'] ?? '')) ?></textarea>
            </div>

            <div class="flex items-center gap-3 pt-4 border-t border-border dark:border-border-dark">
                <button type="submit" class="btn-primary font-bengali">
                    <?= $customer ? 'আপডেট করুন' : 'গ্রাহক যোগ করুন' ?>
                </button>
                <a href="<?= url('customers') ?>" class="btn-ghost font-bengali">বাতিল</a>
            </div>
        </form>
    </div>
</div>