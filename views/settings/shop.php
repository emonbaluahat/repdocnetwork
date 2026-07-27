<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-semibold text-text-primary dark:text-text-dark-primary font-bengali">শপ সেটিংস</h1>
            <p class="text-sm text-text-secondary dark:text-text-dark-secondary mt-1 font-bengali">আপনার দোকানের তথ্য এবং সেটিংস পরিচালনা করুন</p>
        </div>
    </div>

    <form method="POST" action="<?= url('settings/shop') ?>" class="space-y-6" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <div class="bg-white dark:bg-card border border-border dark:border-border-dark rounded-xl p-6">
            <h2 class="text-sm font-semibold text-text-primary dark:text-text-dark-primary mb-4 font-bengali">বেসিক ইনফরমেশন</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="name" class="label font-bengali">শপের নাম *</label>
                    <input type="text" id="name" name="name" value="<?= e(old('name', $shop['name'] ?? '')) ?>"
                           class="input" placeholder="আপনার দোকানের নাম" required>
                </div>

                <div>
                    <label for="phone" class="label font-bengali">ফোন নম্বর *</label>
                    <input type="tel" id="phone" name="phone" value="<?= e(old('phone', $shop['phone'] ?? '')) ?>"
                           class="input" placeholder="017xxxxxxxx" required>
                </div>

                <div>
                    <label for="email" class="label font-bengali">ইমেইল</label>
                    <input type="email" id="email" name="email" value="<?= e(old('email', $shop['email'] ?? '')) ?>"
                           class="input" placeholder="shop@example.com">
                </div>

                <div>
                    <label for="address" class="label font-bengali">ঠিকানা</label>
                    <textarea id="address" name="address" rows="2"
                              class="input" placeholder="গ্রাম/রাস্তা, থানা, জেলা"><?= e(old('address', $shop['address'] ?? '')) ?></textarea>
                </div>
            </div>

            <div class="mt-4">
                <label class="label font-bengali">লোগো</label>
                <div class="flex items-center gap-4">
                    <div class="w-20 h-20 bg-surface-secondary dark:bg-surface-dark-secondary rounded-lg border border-border dark:border-border-dark flex items-center justify-center overflow-hidden">
                        <?php if ($shop['logo'] ?? false): ?>
                            <img src="<?= asset('storage/uploads/logos/' . $shop['logo']) ?>" alt="Logo" class="w-full h-full object-cover">
                        <?php else: ?>
                            <svg class="w-8 h-8 text-text-tertiary dark:text-text-dark-tertiary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        <?php endif; ?>
                    </div>
                    <div class="flex flex-col gap-2">
                        <input type="file" id="logo" name="logo" accept="image/jpeg,image/png,image/webp" class="input input-sm">
                        <p class="text-xs text-text-tertiary dark:text-text-dark-tertiary font-bengali">JPG, PNG, WebP — সর্বোচ্চ ২MB</p>
                        <?php if ($shop['logo'] ?? false): ?>
                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="remove_logo" value="1" class="checkbox">
                                <span class="text-sm text-text-secondary dark:text-text-dark-secondary font-bengali">লোগো সরান</span>
                            </label>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-card border border-border dark:border-border-dark rounded-xl p-6">
            <h2 class="text-sm font-semibold text-text-primary dark:text-text-dark-primary mb-4 font-bengali">ব্যবসার ঘন্টার সময়</h2>
            <p class="text-xs text-text-tertiary dark:text-text-dark-tertiary mb-4 font-bengali">প্রতিদিনের খোলা ও বন্ধের সময় সেট করুন। বন্ধ থাকলে 'বন্ধ' চেকবক্সে টিক দিন।</p>

            <div class="space-y-3">
                <?php
                $days = [
                    'saturday' => 'শনিবার',
                    'sunday' => 'রবিবার',
                    'monday' => 'সোমবার',
                    'tuesday' => 'মঙ্গলবার',
                    'wednesday' => 'বুধবার',
                    'thursday' => 'বৃহস্পতিবার',
                    'friday' => 'শুক্রবার',
                ];
                $dayOrder = ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
                foreach ($dayOrder as $day):
                    $hours = $business_hours[$day] ?? ['open' => '09:00', 'close' => '21:00', 'closed' => false];
                ?>
                    <div class="flex items-center gap-3 p-3 bg-surface-secondary dark:bg-surface-dark-secondary rounded-lg">
                        <div class="w-28 font-medium text-sm font-bengali"><?= e($days[$day]) ?></div>
                        <label class="inline-flex items-center gap-2 cursor-pointer flex-shrink-0">
                            <input type="checkbox"
                                   name="business_hours_<?= $day ?>_closed"
                                   value="1"
                                   class="checkbox"
                                   <?= $hours['closed'] ? 'checked' : '' ?>
                                   onchange="toggleDayHours(this, '<?= $day ?>')">
                            <span class="text-sm text-text-secondary dark:text-text-dark-secondary font-bengali">বন্ধ</span>
                        </label>
                        <div class="flex-1 flex items-center gap-2" id="hours_<?= $day ?>" style="<?= $hours['closed'] ? 'display:none;' : '' ?>">
                            <input type="time"
                                   name="business_hours_<?= $day ?>_open"
                                   value="<?= e($hours['open'] ?? '09:00') ?>"
                                   class="input input-sm w-28">
                            <span class="text-text-tertiary">–</span>
                            <input type="time"
                                   name="business_hours_<?= $day ?>_close"
                                   value="<?= e($hours['close'] ?? '21:00') ?>"
                                   class="input input-sm w-28">
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="bg-white dark:bg-card border border-border dark:border-border-dark rounded-xl p-6">
            <h2 class="text-sm font-semibold text-text-primary dark:text-text-dark-primary mb-4 font-bengali">ইনভয়েস সেটিংস</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="invoice_prefix" class="label font-bengali">ইনভয়েস প্রিফিক্স</label>
                    <input type="text" id="invoice_prefix" name="invoice_prefix"
                           value="<?= e($invoice_settings['prefix'] ?? 'INV') ?>"
                           class="input" placeholder="INV" maxlength="10">
                </div>

                <div>
                    <label for="invoice_tax_rate" class="label font-bengali">ট্যাক্স রেট (%)</label>
                    <input type="number" id="invoice_tax_rate" name="invoice_tax_rate" step="0.01" min="0" max="100"
                           value="<?= e($invoice_settings['tax_rate'] ?? 0) ?>"
                           class="input" placeholder="0">
                </div>

                <div>
                    <label for="invoice_tax_label" class="label font-bengali">ট্যাক্স লেবেল</label>
                    <input type="text" id="invoice_tax_label" name="invoice_tax_label"
                           value="<?= e($invoice_settings['tax_label'] ?? 'VAT') ?>"
                           class="input" placeholder="VAT" maxlength="20">
                </div>
            </div>

            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="invoice_show_logo" name="invoice_show_logo" value="1"
                           class="checkbox" <?= ($invoice_settings['show_logo'] ?? true) ? 'checked' : '' ?>>
                    <label for="invoice_show_logo" class="text-sm font-bengali">ইনভয়েসে লোগো দেখান</label>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" id="invoice_show_business_hours" name="invoice_show_business_hours" value="1"
                           class="checkbox" <?= ($invoice_settings['show_business_hours'] ?? true) ? 'checked' : '' ?>>
                    <label for="invoice_show_business_hours" class="text-sm font-bengali">ব্যবসার ঘন্টার সময় দেখান</label>
                </div>
            </div>

            <div class="mt-4">
                <label for="invoice_footer_text" class="label font-bengali">ফুটার টেক্সট</label>
                <textarea id="invoice_footer_text" name="invoice_footer_text" rows="2"
                          class="input" placeholder="ধন্যবাদ আপনার ব্যবসার জন্য!"><?= e($invoice_settings['footer_text'] ?? '') ?></textarea>
            </div>

            <div class="mt-4">
                <label for="invoice_terms_conditions" class="label font-bengali">নियम ও শর্তাবলী</label>
                <textarea id="invoice_terms_conditions" name="invoice_terms_conditions" rows="3"
                          class="input" placeholder="সামান একবার বিক্রি হলে ফেরত বা বিনিময় করা যাবে না।"><?= e($invoice_settings['terms_conditions'] ?? '') ?></textarea>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="btn-primary font-bengali">সংরক্ষণ করুন</button>
            <a href="<?= url('/') ?>" class="btn-ghost font-bengali">বাতিল</a>
        </div>
    </form>
</div>

<script>
function toggleDayHours(checkbox, day) {
    const container = document.getElementById('hours_' + day);
    if (checkbox.checked) {
        container.style.display = 'none';
    } else {
        container.style.display = 'flex';
    }
}
</script>