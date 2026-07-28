<div class="max-w-4xl mx-auto" x-data="documentCreator()">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-semibold text-text-primary dark:text-text-dark-primary font-bengali">নতুন ডকুমেন্ট</h1>
            <p class="text-sm text-text-secondary dark:text-text-dark-secondary mt-1 font-bengali">গ্রাহক নির্বাচন করে টেমপ্লেট অনুযায়ী ডকুমেন্ট জেনারেট করুন</p>
        </div>
        <a href="<?= url('documents') ?>" class="btn-ghost btn-sm font-bengali">ফিরে যান</a>
    </div>

    <div class="bg-white dark:bg-card border border-border dark:border-border-dark rounded-xl p-6">
        <form method="POST" action="<?= url('documents') ?>" class="space-y-6">
            <?= csrf_field() ?>

            <div>
                <label class="label font-bengali">গ্রাহক নির্বাচন করুন *</label>
                <select name="customer_id" x-model="customerId" class="input" required>
                    <option value="">গ্রাহক নির্বাচন করুন</option>
                    <?php foreach ($customers as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= e($c['name']) ?> (<?= e($c['phone']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="label font-bengali">টেমপ্লেট নির্বাচন করুন *</label>
                <select name="template_id" x-model="templateId" @change="loadVariables()" class="input" required>
                    <option value="">টেমপ্লেট নির্বাচন করুন</option>
                    <?php foreach ($templates as $t): ?>
                        <option value="<?= $t['id'] ?>"><?= e($t['name']) ?> (<?= e($t['category']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <template x-if="templateId">
                <div class="space-y-4 p-4 bg-surface-secondary dark:bg-surface-dark-secondary rounded-lg">
                    <h3 class="text-sm font-semibold text-text-primary dark:text-text-dark-primary font-bengali">ভেরিয়েবল ফিল্ডসমূহ</h3>
                    <template x-for="(field, idx) in variableFields" :key="idx">
                        <div>
                            <label class="label" x-text="field.label"></label>
                            <input :name="'var_' + field.key" type="text" class="input" :placeholder="field.label" x-model="field.value">
                        </div>
                    </template>
                </div>
            </template>

            <div class="flex items-center gap-3 pt-4 border-t border-border dark:border-border-dark">
                <button type="submit" class="btn-primary font-bengali" :disabled="!templateId || !customerId">
                    ডকুমেন্ট জেনারেট করুন
                </button>
                <a href="<?= url('documents') ?>" class="btn-ghost font-bengali">বাতিল</a>
            </div>
        </form>
    </div>
</div>

<script>
function documentCreator() {
    return {
        customerId: '',
        templateId: '',
        variableFields: [],
        templates: <?= json_encode($templates, JSON_UNESCAPED_UNICODE) ?>,
        loadVariables() {
            const tpl = this.templates.find(t => t.id == this.templateId);
            if (!tpl) { this.variableFields = []; return; }
            let vars = [];
            try { vars = JSON.parse(tpl.variables || '[]'); } catch(e) { vars = []; }
            this.variableFields = vars.map(key => ({
                key: key,
                label: key.replace(/_/g, ' '),
                value: ''
            }));
        }
    };
}
</script>
