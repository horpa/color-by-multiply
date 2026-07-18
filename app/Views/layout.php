<!DOCTYPE html>
<html lang="<?= e($lang) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(t('page_title', $lang)) ?></title>
    <link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
</head>
<body>
    <div class="app-shell">
        <header class="app-header no-print">
            <div class="app-header__brand">
                <h1 class="app-header__title"><?= e(t('page_heading', $lang)) ?></h1>
                <p class="app-header__intro"><?= e(t('page_intro', $lang)) ?></p>
            </div>
            <div class="app-header__actions">
                <?php if (($wizardStep ?? null) !== 'home'): ?>
                    <a class="app-header__library-link" href="<?= e(library_url($lang)) ?>">← <?= e(t('library_nav_link', $lang)) ?></a>
                <?php endif; ?>
                <?php view('partials/language-form', compact('lang', 'wizardStep')); ?>
            </div>
        </header>

        <main class="app-main">
            <?= $content ?>
        </main>
    </div>
</body>
</html>
