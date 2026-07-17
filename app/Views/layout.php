<!DOCTYPE html>
<html lang="<?= e($lang) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(t('page_title', $lang)) ?></title>
    <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
</head>
<body>
    <h1><?= e(t('page_heading', $lang)) ?></h1>
    <p><?= e(t('page_intro', $lang)) ?></p>

    <?= $content ?>
</body>
</html>
