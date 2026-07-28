<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-semibold text-text-primary dark:text-text-dark-primary font-mono"><?= e($document['document_number']) ?></h1>
            <p class="text-sm text-text-secondary dark:text-text-dark-secondary mt-1 font-bengali"><?= e($document['template_name'] ?? '') ?></p>
        </div>
        <div class="flex items-center gap-2">
            <?php if ($document['generated_file']): ?>
                <a href="<?= url('documents/' . $document['id'] . '/pdf') ?>" target="_blank" class="btn-primary btn-sm font-bengali">
                    পিডিএফ ডাউনলোড
                </a>
                <a href="<?= url('documents/' . $document['id'] . '/print') ?>" target="_blank" class="btn-secondary btn-sm font-bengali">
                    প্রিন্ট
                </a>
            <?php endif; ?>
            <?php if ($document['status'] === 'generated'): ?>
                <form method="POST" action="<?= url('documents/' . $document['id'] . '/void') ?>" class="inline"
                      onsubmit="return confirm('ডকুমেন্টটি বাতিল করবেন?')">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn-danger btn-sm font-bengali">বাতিল</button>
                </form>
            <?php endif; ?>
            <a href="<?= url('documents') ?>" class="btn-ghost btn-sm font-bengali">ফিরে যান</a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-card border border-border dark:border-border-dark rounded-xl p-6">
                <?php
                $data = json_decode($document['data'] ?? '{}', true) ?: [];
                $rendered = \App\Services\DocumentGenerator::renderTemplate($document['template_content'] ?? '', $data);
                ?>
                <div class="prose dark:prose-invert max-w-none">
                    <?= $rendered ?>
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <div class="bg-white dark:bg-card border border-border dark:border-border-dark rounded-xl p-4">
                <h3 class="text-sm font-semibold text-text-primary dark:text-text-dark-primary mb-3 font-bengali">ডকুমেন্ট তথ্য</h3>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-text-tertiary dark:text-text-dark-tertiary font-bengali">স্ট্যাটাস</dt>
                        <dd>
                            <span class="badge <?= $document['status'] === 'generated' ? 'badge-success' : ($document['status'] === 'voided' ? 'badge-error' : 'badge-warning') ?> text-xs">
                                <?= e($document['status']) ?>
                            </span>
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-text-tertiary dark:text-text-dark-tertiary font-bengali">গ্রাহক</dt>
                        <dd class="text-text-primary dark:text-text-dark-primary"><?= e($document['customer_name'] ?? '—') ?></dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-text-tertiary dark:text-text-dark-tertiary font-bengali">ফোন</dt>
                        <dd class="text-text-primary dark:text-text-dark-primary"><?= e($document['customer_phone'] ?? '—') ?></dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-text-tertiary dark:text-text-dark-tertiary font-bengali">টেমপ্লেট</dt>
                        <dd class="text-text-primary dark:text-text-dark-primary"><?= e($document['template_name'] ?? '—') ?></dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-text-tertiary dark:text-text-dark-tertiary font-bengali">তারিখ</dt>
                        <dd class="text-text-primary dark:text-text-dark-primary"><?= e(format_datetime($document['created_at'])) ?></dd>
                    </div>
                </dl>
            </div>

            <?php if (!empty($data)): ?>
                <div class="bg-white dark:bg-card border border-border dark:border-border-dark rounded-xl p-4">
                    <h3 class="text-sm font-semibold text-text-primary dark:text-text-dark-primary mb-3 font-bengali">ডেটা</h3>
                    <dl class="space-y-1 text-sm">
                        <?php foreach ($data as $key => $value): ?>
                            <?php if (!in_array($key, ['document_number', 'created_at', 'customer_name', 'phone'])): ?>
                                <div class="flex justify-between">
                                    <dt class="text-text-tertiary dark:text-text-dark-tertiary"><?= e($key) ?></dt>
                                    <dd class="text-text-primary dark:text-text-dark-primary"><?= e(is_string($value) ? $value : json_encode($value)) ?></dd>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </dl>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
