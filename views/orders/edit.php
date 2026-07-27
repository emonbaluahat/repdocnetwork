<div class="max-w-3xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <a href="<?= url('orders') ?>" class="text-sm text-text-secondary dark:text-text-dark-secondary hover:text-text-primary transition font-bengali mb-2 inline-block">← অর্ডার তালিকা</a>
            <h1 class="text-xl font-semibold text-text-primary dark:text-text-dark-primary">অর্ডার সম্পাদনা: <?= e($order['reference']) ?></h1>
        </div>
    </div>

    <form method="POST" action="<?= url('orders/' . $order['id'] . '/update') ?>" class="space-y-6">
        <?= csrf_field() ?>

        <div class="bg-white dark:bg-card border border-border dark:border-border-dark rounded-xl p-6">
            <h2 class="text-sm font-semibold text-text-primary dark:text-text-dark-primary mb-4 font-bengali">অর্ডার তথ্য</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-text-secondary mb-1 font-bengali">গ্রাহক</label>
                    <div x-data="customerSearch(<?= htmlspecialchars(json_encode($order['customer_id']), ENT_QUOTES, 'UTF-8') ?>, '<?= e(addslashes($order['customer_name'] ?? '')) ?>')"
                         class="relative">
                        <input type="hidden" name="customer_id" x-model="selectedId">
                        <input type="text"
                               x-model="query"
                               x-on:input.debounce.300ms="search"
                               x-on:blur="if(!selectedId) { query = selectedName; }"
                               class="input w-full"
                               placeholder="গ্রাহক খুঁজুন..."
                               autocomplete="off">
                        <div x-show="results.length > 0 && !selectedId"
                             class="absolute z-10 mt-1 w-full bg-white dark:bg-card border border-border dark:border-border-dark rounded-lg shadow-lg max-h-48 overflow-y-auto"
                             x-transition>
                            <template x-for="c in results" :key="c.id">
                                <div class="px-3 py-2 cursor-pointer hover:bg-surface dark:hover:bg-surface-dark text-sm"
                                     x-on:mousedown="select(c)"
                                     x-text="c.name + ' (' + (c.phone || '—') + ')'"></div>
                            </template>
                            <div x-show="results.length === 0" class="px-3 py-2 text-sm text-text-tertiary font-bengali">কোন গ্রাহক পাওয়া যায়নি</div>
                        </div>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-text-secondary mb-1">প্রায়োরিটি</label>
                    <select name="priority" class="input">
                        <option value="normal" <?= $order['priority'] === 'normal' ? 'selected' : '' ?>>নরমাল</option>
                        <option value="urgent" <?= $order['priority'] === 'urgent' ? 'selected' : '' ?>>জরুরি</option>
                        <option value="express" <?= $order['priority'] === 'express' ? 'selected' : '' ?>>এক্সপ্রেস</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-card border border-border dark:border-border-dark rounded-xl p-6">
            <h2 class="text-sm font-semibold text-text-primary dark:text-text-dark-primary mb-4 font-bengali">আইটেম</h2>
            <div x-data="itemsManager(<?= htmlspecialchars(json_encode($items), ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars(json_encode($services), ENT_QUOTES, 'UTF-8') ?>)" class="space-y-3">
                <template x-for="(item, idx) in items" :key="idx">
                    <div class="flex items-center gap-2">
                        <input type="hidden" :name="`items[${idx}][id]`" x-model="item.id">
                        <div class="flex-1">
                            <select :name="`items[${idx}][service_id]`" x-model="item.service_id"
                                    x-on:change="fillService(idx)" class="input w-full">
                                <option value="">-- আইটেম বাছাই করুন --</option>
                                <template x-for="svc in services" :key="svc.id">
                                    <option :value="svc.id" x-text="svc.name + ' (৳' + svc.price + ')'" :selected="parseInt(item.service_id) === svc.id"></option>
                                </template>
                            </select>
                        </div>
                        <div class="w-24">
                            <input type="number" :name="`items[${idx}][quantity]`" x-model="item.quantity" min="1"
                                   x-on:input="updateTotal(idx)" class="input w-full text-center" placeholder="পরিমাণ">
                        </div>
                        <div class="w-28">
                            <input type="number" step="0.01" :name="`items[${idx}][unit_price]`" x-model="item.unit_price"
                                   x-on:input="updateTotal(idx)" class="input w-full" placeholder="দাম">
                        </div>
                        <div class="w-28 text-right text-sm font-semibold" x-text="'৳' + parseFloat(item.total || 0).toFixed(2)"></div>
                        <button type="button" class="btn-ghost btn-sm text-error-500 flex-shrink-0"
                                x-on:click="removeItem(idx)" x-show="items.length > 1">×</button>
                    </div>
                </template>
                <button type="button" class="btn-secondary btn-sm font-bengali" x-on:click="addItem()">+ আইটেম যোগ করুন</button>
                <div class="text-right text-sm pt-3 border-t border-border">
                    <span class="font-bengali">মোট:</span>
                    <span class="font-semibold text-lg" x-text="'৳' + totalAmount().toFixed(2)"></span>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-card border border-border dark:border-border-dark rounded-xl p-6">
            <h2 class="text-sm font-semibold text-text-primary dark:text-text-dark-primary mb-4 font-bengali">ছাড় ও ট্যাক্স</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-text-secondary mb-1">ছাড়ের ধরন</label>
                    <select name="discount_type" class="input" x-on:change="toggleDiscountType">
                        <option value="none" <?= empty($order['discount_type']) || $order['discount_type'] === 'none' ? 'selected' : '' ?>>ছাড় নেই</option>
                        <option value="percentage" <?= $order['discount_type'] === 'percentage' ? 'selected' : '' ?>>শতাংশ</option>
                        <option value="fixed" <?= $order['discount_type'] === 'fixed' ? 'selected' : '' ?>>নির্দিষ্ট</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-text-secondary mb-1">ছাড়ের পরিমাণ</label>
                    <input type="number" name="discount_amount" step="0.01" min="0"
                           value="<?= e($order['discount_amount'] ?? '') ?>"
                           class="input" placeholder="0">
                </div>
                <div>
                    <label class="block text-sm font-medium text-text-secondary mb-1">ট্যাক্স</label>
                    <input type="number" name="tax_amount" step="0.01" min="0"
                           value="<?= e($order['tax_amount'] ?? '') ?>"
                           class="input" placeholder="0">
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-card border border-border dark:border-border-dark rounded-xl p-6">
            <h2 class="text-sm font-semibold text-text-primary dark:text-text-dark-primary mb-4 font-bengali">নোট</h2>
            <textarea name="notes" rows="3" class="input w-full" placeholder="নোট লিখুন..."><?= e($order['notes'] ?? '') ?></textarea>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" class="btn-primary font-bengali">আপডেট করুন</button>
            <a href="<?= url('orders/' . $order['id']) ?>" class="btn-ghost font-bengali">বাতিল</a>
        </div>
    </form>
</div>

<script>
function customerSearch(selectedId, selectedName) {
    return {
        query: selectedName || '',
        selectedId: selectedId || '',
        selectedName: selectedName || '',
        results: [],
        search() {
            if (this.query.length < 1) { this.results = []; return; }
            fetch('<?= url('api/customers/search') ?>?q=' + encodeURIComponent(this.query))
                .then(r => r.json()).then(data => { this.results = data; });
        },
        select(c) {
            this.selectedId = c.id;
            this.selectedName = c.name;
            this.query = c.name;
            this.results = [];
        }
    };
}

function itemsManager(items, services) {
    return {
        items: items.length ? items.map(i => ({
            id: i.id || '',
            service_id: i.service_id || '',
            name: i.name || '',
            quantity: parseInt(i.quantity) || 1,
            unit_price: parseFloat(i.unit_price) || 0,
            total: parseFloat(i.total_price) || 0
        })) : [{ service_id: '', quantity: 1, unit_price: 0, total: 0 }],
        services: services,
        addItem() {
            this.items.push({ service_id: '', quantity: 1, unit_price: 0, total: 0 });
        },
        removeItem(idx) {
            this.items.splice(idx, 1);
        },
        fillService(idx) {
            const svc = this.services.find(s => s.id == this.items[idx].service_id);
            if (svc) {
                this.items[idx].unit_price = parseFloat(svc.price) || 0;
                this.updateTotal(idx);
            }
        },
        updateTotal(idx) {
            this.items[idx].total = (parseFloat(this.items[idx].unit_price) || 0) * (parseInt(this.items[idx].quantity) || 1);
        },
        totalAmount() {
            return this.items.reduce((sum, i) => sum + parseFloat(i.total || 0), 0);
        }
    };
}
</script>