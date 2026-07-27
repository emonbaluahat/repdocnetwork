<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 0; size: 80mm auto; }
        body { font-family: 'Courier New', monospace; font-size: 12px; margin: 0; padding: 8px; width: 80mm; color: #000; background: #fff; }
        .header { text-align: center; margin-bottom: 8px; }
        .header h1 { font-size: 16px; margin: 0 0 2px; }
        .header p { margin: 1px 0; font-size: 11px; }
        .divider { border-top: 1px dashed #000; margin: 8px 0; }
        .info-row { display: flex; justify-content: space-between; font-size: 11px; margin: 2px 0; }
        .items-table { width: 100%; font-size: 11px; }
        .items-table th { text-align: left; padding: 2px 0; border-bottom: 1px dashed #000; }
        .items-table td { padding: 3px 0; }
        .items-table .qty { text-align: center; }
        .items-table .price { text-align: right; }
        .totals { text-align: right; font-size: 11px; margin-top: 6px; }
        .totals .total-line { font-size: 14px; font-weight: bold; margin-top: 4px; }
        .footer { text-align: center; font-size: 10px; margin-top: 12px; border-top: 1px dashed #000; padding-top: 6px; }
        .badge { display: inline-block; font-size: 10px; padding: 1px 4px; border: 1px solid #000; }
        @media print { html, body { width: 80mm; } }
    </style>
</head>
<body onload="window.print()">
    <div class="header">
        <h1><?= e($shop['name'] ?? '') ?></h1>
        <p><?= e($shop['address'] ?? '') ?></p>
        <p><?= e($shop['phone'] ?? '') ?> | <?= e($shop['email'] ?? '') ?></p>
        <p><?= $shop['invoice_footer'] ?? '' ?></p>
    </div>

    <div class="divider"></div>

    <div class="info-row">
        <span>ক্রমিক: <?= e($order['reference']) ?></span>
        <span>তারিখ: <?= format_date($order['created_at']) ?></span>
    </div>
    <div class="info-row">
        <span>গ্রাহক: <?= e($order['customer_name'] ?? '') ?></span>
        <span><?= e($order['customer_phone'] ?? '') ?></span>
    </div>

    <div class="divider"></div>

    <table class="items-table">
        <thead>
            <tr>
                <th>আইটেম</th>
                <th class="qty">পরি.</th>
                <th class="price">মূল্য</th>
                <th class="price">মোট</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= e($item['name']) ?></td>
                    <td class="qty"><?= (int) $item['quantity'] ?></td>
                    <td class="price"><?= number_format((float) $item['unit_price'], 2) ?></td>
                    <td class="price"><?= number_format((float) $item['total_price'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="divider"></div>

    <div class="totals">
        <?php if ((float) $order['discount_amount'] > 0): ?>
            <div>ছাড়: -৳<?= number_format((float) $order['discount_amount'], 2) ?></div>
        <?php endif; ?>
        <?php if ((float) $order['tax_amount'] > 0): ?>
            <div>ট্যাক্স: ৳<?= number_format((float) $order['tax_amount'], 2) ?></div>
        <?php endif; ?>
        <div class="total-line">মোট: ৳<?= number_format((float) $order['amount'], 2) ?></div>
        <?php if ((float) $order['due_amount'] > 0): ?>
            <div class="info-row"><span>বকেয়া:</span><span>৳<?= number_format((float) $order['due_amount'], 2) ?></span></div>
        <?php else: ?>
            <div>পরিশোধিত ✓</div>
        <?php endif; ?>
        <?php if (!empty($transactions)): ?>
            <div class="divider"></div>
            <?php foreach ($transactions as $txn): ?>
                <div class="info-row"><span>পেমেন্ট (<?= e($txn['method']) ?>):</span><span>৳<?= number_format((float) $txn['amount'], 2) ?></span></div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="footer">
        <?php if ($shop['invoice_footer'] ?? ''): ?>
            <p><?= nl2br(e($shop['invoice_footer'])) ?></p>
        <?php endif; ?>
        <p>ধন্যবাদ</p>
    </div>
</body>
</html>