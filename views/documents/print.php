<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <title><?= e($document['document_number']) ?></title>
    <style>
        body { font-family: 'Noto Sans Bengali', sans-serif; font-size: 14px; line-height: 1.6; color: #1a1a1a; padding: 40px; }
        @media print { body { padding: 0; } }
    </style>
</head>
<body>
    <?= $rendered ?>
    <script>window.print();</script>
</body>
</html>
