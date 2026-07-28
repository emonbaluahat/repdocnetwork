<!DOCTYPE html>
<html lang="bn">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= __('certificate_verification') ?> — <?= e(APP_NAME) ?></title>
  <link href="https://fonts.maateen.me/solaiman-lipi/font.css" rel="stylesheet">
  <style>
    :root {
      --primary: #006a4e;
      --primary-light: #008a66;
      --accent-red: #d62828;
      --success: #28a745;
      --danger: #dc3545;
      --gray: #6c757d;
      --light: #f8f9fa;
      --border: #e9ecef;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'SolaimanLipi', Arial, sans-serif;
      background: linear-gradient(135deg, #e8ecf1 0%, #f5f7fa 100%);
      min-height: 100vh;
      padding: 30px 16px;
    }
    .verify-wrap { max-width: 700px; margin: 0 auto; }
    .govt-header { text-align: center; margin-bottom: 24px; }
    .govt-header h1 { font-size: 20px; color: var(--primary); font-weight: 700; letter-spacing: 1px; }
    .govt-header p { font-size: 13px; color: var(--gray); }
    .card {
      background: #fff;
      border-radius: 16px;
      box-shadow: 0 8px 40px rgba(0,0,0,0.1);
      overflow: hidden;
    }
    .card-header {
      background: var(--primary);
      color: #fff;
      padding: 28px 24px;
      text-align: center;
    }
    .card-header h2 { font-size: 24px; font-weight: 700; }
    .card-header .subtitle { font-size: 13px; opacity: 0.8; margin-top: 4px; }
    .card-body { padding: 28px 24px; }
    .status-badge {
      display: inline-flex; align-items: center; gap: 8px;
      padding: 10px 24px; border-radius: 30px;
      font-size: 15px; font-weight: 700; margin-bottom: 24px;
    }
    .status-valid { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .status-invalid { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    .info-grid {
      display: grid;
      grid-template-columns: 140px 1fr;
      gap: 2px 12px;
      font-size: 14px;
    }
    .info-grid .label {
      color: var(--gray);
      font-weight: 600;
      padding: 7px 8px;
      background: var(--light);
      border-radius: 4px 0 0 4px;
    }
    .info-grid .value {
      color: var(--primary);
      font-weight: 500;
      padding: 7px 8px;
      background: var(--light);
      border-radius: 0 4px 4px 0;
    }
    .info-grid .section-divider {
      grid-column: 1 / -1;
      border-bottom: 1px dashed #ddd;
      margin: 8px 0;
    }
    .card-footer {
      background: var(--light);
      padding: 24px;
      text-align: center;
      border-top: 1px solid var(--border);
    }
    .search-card {
      background: #fff;
      border-radius: 16px;
      box-shadow: 0 8px 40px rgba(0,0,0,0.1);
      padding: 40px 32px;
      text-align: center;
    }
    .search-card h3 { font-size: 22px; color: var(--primary); font-weight: 700; margin-bottom: 6px; }
    .search-card p { color: var(--gray); font-size: 14px; margin-bottom: 20px; }
    .search-input-wrap { display: flex; gap: 10px; max-width: 480px; margin: 0 auto; }
    .search-input-wrap input {
      flex: 1; padding: 12px 16px;
      border: 2px solid #ddd; border-radius: 8px;
      font-size: 15px; font-family: inherit;
      outline: none; transition: border-color 0.3s;
    }
    .search-input-wrap input:focus { border-color: var(--primary); }
    .search-input-wrap button {
      padding: 12px 28px;
      background: var(--accent-red);
      color: #fff; border: none; border-radius: 8px;
      font-size: 14px; font-weight: 600; cursor: pointer;
      font-family: inherit; transition: all 0.2s;
      white-space: nowrap;
    }
    .search-input-wrap button:hover { background: #b01e1e; }
    .error-box {
      margin-top: 20px; padding: 16px 20px;
      background: #f8d7da; border: 1px solid #f5c6cb;
      border-radius: 10px; color: #721c24;
      display: flex; align-items: center; gap: 10px; font-size: 14px;
    }
    .btn-group { display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; margin-top: 16px; }
    .btn {
      display: inline-flex; align-items: center; gap: 6px;
      padding: 10px 22px; border-radius: 8px;
      font-size: 13px; font-weight: 600; cursor: pointer;
      border: none; text-decoration: none; font-family: inherit;
      transition: all 0.2s;
    }
    .btn-primary { background: var(--primary); color: #fff; }
    .btn-primary:hover { background: var(--primary-light); }
    .btn-outline { background: transparent; color: var(--primary); border: 1.5px solid var(--primary); }
    .btn-outline:hover { background: var(--primary); color: #fff; }
    .govt-seal { text-align: center; color: var(--gray); font-size: 12px; margin-top: 20px; opacity: 0.7; }
    .fade-in { animation: fadeInUp 0.5s ease; }
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
    @media (max-width: 600px) {
      body { padding: 16px 10px; }
      .info-grid { grid-template-columns: 110px 1fr; font-size: 13px; }
      .search-input-wrap { flex-direction: column; }
      .search-card { padding: 28px 20px; }
    }
  </style>
</head>
<body>
<div class="verify-wrap">
  <div class="govt-header">
    <h1><?= __('govt_bangladesh') ?></h1>
    <p><?= __('certificate_verification_portal') ?></p>
  </div>

  <?php if ($verified ?? false): ?>
  <div class="card fade-in">
    <div class="card-header">
      <h2><?= __('valid_certificate') ?></h2>
      <div style="font-size:15px;font-weight:600;margin-top:4px;opacity:0.9;">
        <?= e($document['template_name'] ?? '') ?>
      </div>
      <div class="subtitle"><?= e(APP_NAME) ?></div>
    </div>
    <div class="card-body">
      <div style="display:flex;justify-content:center;">
        <span class="status-badge status-valid">✓ <?= __('verified') ?></span>
      </div>

      <div class="info-grid">
        <span class="label"><?= __('certificate_no') ?></span>
        <span class="value"><strong><?= e($document['document_number'] ?? '') ?></strong></span>

        <div class="section-divider"></div>

        <span class="label"><?= __('name') ?></span>
        <span class="value"><strong><?= e($document['customer_name'] ?? '') ?></strong></span>

        <span class="label"><?= __('phone') ?></span>
        <span class="value"><?= e($document['customer_phone'] ?? '—') ?></span>

        <span class="label"><?= __('nid') ?></span>
        <span class="value"><?= e($document['customer_nid'] ?? '—') ?></span>

        <div class="section-divider"></div>

        <span class="label"><?= __('template') ?></span>
        <span class="value"><?= e($document['template_name'] ?? '') ?></span>

        <span class="label"><?= __('status') ?></span>
        <span class="value"><?= __($document['status'] ?? '') ?></span>
      </div>
    </div>
    <div class="card-footer">
      <div style="font-size:13px;color:var(--gray);font-weight:600;"><?= __('scan_qr_reverify') ?></div>
      <div class="btn-group">
        <button class="btn btn-primary" onclick="window.print()">🖨 <?= __('print') ?></button>
        <a href="<?= url('verify/' . urlencode($code)) ?>" class="btn btn-outline"><?= __('refresh') ?></a>
      </div>
    </div>
  </div>

  <?php else: ?>
  <div class="search-card fade-in">
    <div style="font-size:48px;color:var(--accent-red);margin-bottom:16px;">
      <?php if ($code): ?>
      <div style="width:72px;height:72px;border-radius:50%;background:linear-gradient(135deg,#ffe0e0,#ffcccc);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:30px;">✕</div>
      <?php else: ?>
      <div style="width:72px;height:72px;border-radius:50%;background:linear-gradient(135deg,#ffe0e0,#ffcccc);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:30px;">🔍</div>
      <?php endif; ?>
    </div>
    <h3><?= __('verify_certificate') ?></h3>
    <p><?= __('verify_by_code') ?></p>

    <?php if ($code): ?>
    <div class="error-box">
      <span><?= e($message ?? __('certificate_not_found')) ?></span>
    </div>
    <?php endif; ?>

    <form method="GET" action="<?= url('verify') ?>" style="margin-top:20px;">
      <div class="search-input-wrap">
        <input type="text" name="code" placeholder="<?= __('enter_verification_code') ?>" value="<?= e($code ?? '') ?>" required>
        <button type="submit"><?= __('verify') ?></button>
      </div>
    </form>
  </div>
  <?php endif; ?>

  <div class="govt-seal">
    <?= __('govt_verification_footer') ?>
  </div>
</div>
</body>
</html>
