<div class="max-w-2xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-semibold text-text-primary dark:text-text-dark-primary font-bengali">সিএসভি ইম্পোর্ট</h1>
            <p class="text-sm text-text-secondary dark:text-text-dark-secondary mt-1 font-bengali">সিএসভি ফাইল থেকে গ্রাহক ইম্পোর্ট করুন</p>
        </div>
        <a href="<?= url('customers') ?>" class="btn-ghost btn-sm font-bengali">ফিরে যান</a>
    </div>

    <div class="bg-white dark:bg-card border border-border dark:border-border-dark rounded-xl p-6 space-y-6">
        <div class="bg-info-50 dark:bg-info-700/20 border border-info-200 dark:border-info-700/30 rounded-lg p-4 text-sm text-info-700 dark:text-info-400">
            <p class="font-medium font-bengali mb-1">সিএসভি ফাইল ফরম্যাট</p>
            <p class="font-bengali">ফাইলটিতে নিচের কলামগুলি থাকতে হবে (প্রথম লাইনে হেডার):</p>
            <code class="block mt-2 p-2 bg-white dark:bg-surface-dark rounded text-xs font-mono">
                নাম, ফোন, ইমেইল, এনআইডি, ঠিকানা, ট্যাগ, নোট
            </code>
            <p class="mt-2 font-bengali"><strong>নাম</strong> এবং <strong>ফোন</strong> বাধ্যতামূলক। বাকি কলাম ঐচ্ছিক।</p>
        </div>

        <form method="POST" action="<?= url('customers/import') ?>" enctype="multipart/form-data" class="space-y-4">
            <?= csrf_field() ?>

            <div>
                <label for="csv_file" class="label font-bengali">সিএসভি ফাইল নির্বাচন করুন</label>
                <input type="file" id="csv_file" name="csv_file" accept=".csv"
                       class="block w-full text-sm text-text-secondary dark:text-text-dark-secondary file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary-50 dark:file:bg-primary-900/20 file:text-primary-700 dark:file:text-primary-400 hover:file:bg-primary-100 dark:hover:file:bg-primary-900/30 transition" required>
            </div>

            <div class="flex items-center gap-3 pt-4 border-t border-border dark:border-border-dark">
                <button type="submit" class="btn-primary font-bengali">ইম্পোর্ট শুরু করুন</button>
                <a href="<?= url('customers') ?>" class="btn-ghost font-bengali">বাতিল</a>
            </div>
        </form>

        <div class="border-t border-border dark:border-border-dark pt-4">
            <p class="text-sm text-text-secondary dark:text-text-dark-secondary font-bengali">
                নমুনা সিএসভি ফাইল ডাউনলোড করুন:
                <a href="#" class="text-primary-700 dark:text-primary-400 hover:underline" onclick="event.preventDefault(); downloadSampleCsv();">ডাউনলোড</a>
            </p>
        </div>
    </div>
</div>

<script>
function downloadSampleCsv() {
    const headers = ['নাম', 'ফোন', 'ইমেইল', 'এনআইডি', 'ঠিকানা', 'ট্যাগ', 'নোট'];
    const rows = [
        ['আব্দুর রহমান', '01712345678', 'rahman@email.com', '1234567890', 'ধানমন্ডি, ঢাকা', 'প্রিমিয়াম', 'নিয়মিত গ্রাহক'],
        ['ফাতিমা বেগম', '01898765432', 'fatima@email.com', '', 'মিরপুর, ঢাকা', 'রেগুলার', ''],
    ];
    let csv = '\uFEFF';
    csv += headers.join(',') + '\n';
    rows.forEach(row => {
        csv += row.map(cell => '"' + cell.replace(/"/g, '""') + '"').join(',') + '\n';
    });
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'sample-customers.csv';
    link.click();
}
</script>