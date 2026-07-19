<!DOCTYPE html>
<html lang="<?= e($lang) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? t('practice_title', $lang)) ?> — <?= e(t('page_title', $lang)) ?></title>
    <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
</head>
<body class="practice-body">
    <div class="practice-shell">
        <?= $content ?>
    </div>
</body>
</html>
