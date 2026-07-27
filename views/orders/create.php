<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-semibold text-text-primary dark:text-text-dark-primary font-bengali">নতুন অর্ডার</h1>
        </div>
        <a href="<?= url('orders') ?>" class="btn-ghost btn-sm font-bengali">ফিরে যান</a>
    </div>

    <form method="POST" action="<?= url('orders') ?>" class="space-y-6" x-data="orderForm()">
        <?= csrf_field() ?>

        <div class="bg-white dark:bg-card border border-border dark:border-border-dark rounded-xl p-6">
            <h2 class="text-sm font-semibold text-text-primary dark:text-text-dark-primary mb-4 font-bengali">গ্রাহক</h2>
            <div class="flex items-center gap-3">
                <div class="flex-1">
                    <input type="text" id="customer_search" class="input"
                           placeholder="গ্রাহকের নাম বা ফোন নম্বর লিখুন..."
                           x-on:input.debounce="searchCustomer($event.target.value)"
                           x-on:keydown.down.prevent="highlightNext()"
                           x-on:keydown.up.prevent="highlightPrev()"
                           x-on:keydown.enter.prevent="selectHighlighted()"
                           autocomplete="off">
                    <input type="hidden" name="customer_id" x-model="customerId">
                </div>
                <a href="<?= url('customers/create') ?>" class="btn-secondary btn-sm font-bengali" target="_blank">+ নতুন</a>
            </div>

            <div x-show="showResults && results.length > 0" class="relative mt-2">
                <div class="absolute z-10 w-full bg-white dark:bg-card border border-border dark:border-border-dark rounded-lg shadow-lg max-h-48 overflow-y-auto">
                    <template x-for="(c, idx) in results" :key="c.id">
                        <div class="px-4 py-2 cursor-pointer hover:bg-surface-secondary dark:hover:bg-surface-dark-secondary"
                             :class="{'bg-primary-50 dark:bg-primary-900/20': idx === highlightIdx}"
                             x-on:click="selectCustomer(c)"
                             x-on:mouseenter="highlightIdx = idx">
                            <p class="text-sm" x-text="c.name"></p>
                            <p class="text-xs text-text-tertiary" x-text="c.phone"></p>
                        </div>
                    </template>
                </div>
            </div>

            <div x-show="customerId" class="mt-3 p-3 bg-surface-secondary dark:bg-surface-dark-secondary rounded-lg flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium" x-text="selectedName"></p>
                    <p class="text-xs text-text-tertiary" x-text="selectedPhone"></p>
                </div>
                <button type="button" class="btn-ghost btn-sm text-error-600" x-on:click="clearCustomer()">পরিবর্তন</button>
            </div>
        </div>

        <div class="bg-white dark:bg-card border border-border dark:border-border-dark rounded-xl p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-semibold text-text-primary dark:text-text-dark-primary font-bengali">সার্ভিস</h2>
                <button type="button" class="btn-secondary btn-sm font-bengali" x-on:click="addItem()">+ আইটেম যোগ করুন</button>
            </div>

            <div class="space-y-3">
                <template x-for="(item, idx) in items" :key="idx">
                    <div class="p-3 bg-surface-secondary dark:bg-surface-dark-secondary rounded-lg">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-medium text-text-secondary font-bengali" x-text="'আইটেম #' + (idx + 1)"></span>
                            <button type="button" class="text-error-600 text-xs hover:underline font-bengali"
                                    x-on:click="removeItem(idx)" x-show="items.length > 1">সরান</button>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-2">
                            <div class="md:col-span-2">
                                <input type="text" :name="'item_name[' + idx + ']'"
                                       class="input input-sm" placeholder="সার্ভিসের নাম"
                                       x-model="item.name" required>
                            </div>
                            <select :name="'item_service[' + idx + ']'" class="input input-sm"
                                    x-on:change="fillService(idx, $event.target.value)">
                                <option value="">সার্ভিস নির্বাচন</option>
                                <?php foreach ($services as $s): ?>
                                    <option value="<?= $s['id'] ?>" data-price="<?= e($s['price']) ?>"><?= e($s['name']) ?> (৳<?= number_format((float) ($s['price'] ?? 0), 2) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                            <div>
                                <input type="number" :name="'item_qty[' + idx + ']'"
                                       class="input input-sm" placeholder="পরিমাণ"
                                       x-model="item.qty" min="1" x-on:input="updateTotal(idx)">
                            </div>
                            <div>
                                <input type="number" step="0.01" :name="'item_price[' + idx + ']'"
                                       class="input input-sm" placeholder="মূল্য"
                                       x-model="item.price" x-on:input="updateTotal(idx)">
                            </div>
                            <div class="flex items-center justify-end text-sm font-semibold">
                                ৳<span x-text="item.total.toFixed(2)"></span>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <div class="mt-4 pt-4 border-t border-border dark:border-border-dark text-right">
                <p class="text-sm font-bengali">সাবটোটাল: ৳<span x-text="subtotal.toFixed(2)"></span></p>
            </div>
        </div>

        <div class="bg-white dark:bg-card border border-border dark:border-border-dark rounded-xl p-6">
            <h2 class="text-sm font-semibold text-text-primary dark:text-text-dark-primary mb-4 font-bengali">অর্ডার বিস্তারিত</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="priority" class="label font-bengali">প্রায়োরিটি</label>
                    <select id="priority" name="priority" class="input">
                        <option value="normal">নরমাল</option>
                        <option value="urgent">জরুরি</option>
                        <option value="express">এক্সপ্রেস</option>
                    </select>
                </div>
                <div>
                    <label for="estimated_ready_at" class="label font-bengali">প্রস্তুত হওয়ার সময়</label>
                    <input type="datetime-local" id="estimated_ready_at" name="estimated_ready_at" class="input">
                </div>
                <div class="md:col-span-2">
                    <label for="notes" class="label font-bengali">নোট (গ্রাহক দেখতে পাবে)</label>
                    <textarea id="notes" name="notes" rows="2" class="input" placeholder="অর্ডার সম্পর্কে নোট..."></textarea>
                </div>
                <div class="md:col-span-2">
                    <label for="internal_notes" class="label font-bengali">অভ্যন্তরীণ নোট</label>
                    <textarea id="internal_notes" name="internal_notes" rows="2" class="input" placeholder="শুধুমাত্র স্টাফদের জন্য নোট..."></textarea>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-card border border-border dark:border-border-dark rounded-xl p-6">
            <h2 class="text-sm font-semibold text-text-primary dark:text-text-dark-primary mb-4 font-bengali">পেমেন্ট</h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="label font-bengali">পেমেন্ট নিন (ঐচ্ছিক)</label>
                    <select name="payment_method" class="input">
                        <option value="">পেমেন্ট নেবেন না</option>
                        <option value="cash">নগদ</option>
                        <option value="bkash">বিকাশ</option>
                        <option value="nagad">নগদ</option>
                        <option value="rocket">রকেট</option>
                        <option value="bank">ব্যাংক</option>
                        <option value="card">কার্ড</option>
                    </select>
                </div>
                <div>
                    <label class="label font-bengali">পরিমাণ</label>
                    <input type="number" name="payment_amount" step="0.01" min="0" value="0" class="input" placeholder="০.০০">
                </div>
                <div>
                    <label class="label font-bengali">নোট</label>
                    <input type="text" name="payment_notes" class="input" placeholder="পেমেন্ট নোট...">
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="btn-primary font-bengali">অর্ডার তৈরি করুন</button>
            <a href="<?= url('orders') ?>" class="btn-ghost font-bengali">বাতিল</a>
        </div>
    </form>
</div>

<script>
function orderForm() {
    return {
        customerId: '',
        selectedName: '',
        selectedPhone: '',
        showResults: false,
        results: [],
        highlightIdx: -1,
        items: [{ name: '', qty: 1, price: 0, total: 0 }],
        subtotal: 0,
        clearCustomer() {
            this.customerId = '';
            this.selectedName = '';
            this.selectedPhone = '';
        },
        searchCustomer(q) {
            if (q.length < 1) { this.results = []; this.showResults = false; return; }
            fetch('<?= url('orders/search-customer') ?>?q=' + encodeURIComponent(q) + '&limit=10')
                .then(r => r.json()).then(data => {
                    this.results = data;
                    this.showResults = data.length > 0;
                    this.highlightIdx = -1;
                });
        },
        selectCustomer(c) {
            this.customerId = c.id;
            this.selectedName = c.name;
            this.selectedPhone = c.phone;
            this.showResults = false;
            this.results = [];
        },
        selectHighlighted() {
            if (this.highlightIdx >= 0 && this.results[this.highlightIdx]) {
                this.selectCustomer(this.results[this.highlightIdx]);
            }
        },
        highlightNext() {
            if (this.highlightIdx < this.results.length - 1) this.highlightIdx++;
        },
        highlightPrev() {
            if (this.highlightIdx > 0) this.highlightIdx--;
        },
        addItem() {
            this.items.push({ name: '', qty: 1, price: 0, total: 0 });
        },
        removeItem(idx) {
            this.items.splice(idx, 1);
            this.calcSubtotal();
        },
        fillService(idx, serviceId) {
            const sel = document.querySelector('select[name="item_service[' + idx + ']"] option[value="' + serviceId + '"]');
            if (sel && sel.dataset.price) {
                this.items[idx].price = parseFloat(sel.dataset.price);
                this.items[idx].name = sel.textContent.split(' (')[0];
                this.updateTotal(idx);
            }
        },
        updateTotal(idx) {
            const item = this.items[idx];
            item.total = (parseFloat(item.qty) || 0) * (parseFloat(item.price) || 0);
            this.calcSubtotal();
        },
        calcSubtotal() {
            this.subtotal = this.items.reduce((sum, item) => sum + item.total, 0);
        },
    };
}
</script>