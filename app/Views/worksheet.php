<?php

/** @var bool $isSavedView */
/** @var string|null $savedId */
/** @var string $lang */

$isSavedView = !empty($isSavedView);
$savedId = isset($savedId) && is_string($savedId) ? $savedId : null;
$practiceHref = $savedId !== null
    ? practice_url($lang, $savedId)
    : practice_url($lang);
$printRef = $savedId !== null
    ? strtoupper($savedId)
    : strtoupper(substr(hash('crc32b', serialize([
        $result['exercises'] ?? [],
        $result['palette'] ?? [],
        $result['grid'] ?? [],
    ])), 0, 8));

?>
<div class="panel printable">
    <?php if ($isSavedView): ?>
        <p class="no-print">
            <a href="<?= e(library_url($lang)) ?>">← <?= e(t('back_to_library', $lang)) ?></a>
        </p>
    <?php endif; ?>

    <p class="worksheet-ready no-print"><?= e(t('worksheet_ready', $lang)) ?></p>

    <div class="worksheet-actions no-print">
        <?php if (!$isSavedView && $savedId === null): ?>
            <form method="post" class="worksheet-save-form">
                <input type="hidden" name="lang" value="<?= e($lang) ?>">
                <button type="submit" name="save_worksheet" value="1" class="btn btn--primary"><?= e(t('save_share_button', $lang)) ?></button>
            </form>
        <?php endif; ?>
        <button type="button" class="btn btn--secondary" onclick="window.print()"><?= e(t('print_preview', $lang)) ?></button>
        <a class="btn btn--secondary" href="<?= e($practiceHref) ?>" target="_blank" rel="noopener noreferrer"><?= e(t('practice_button', $lang)) ?></a>
    </div>

    <?php if ($savedId !== null): ?>
        <?php view('partials/share-links', compact('lang', 'savedId')); ?>
    <?php endif; ?>

    <div class="print-page">
        <header class="print-header">
            <div class="print-header__top">
                <h2 class="print-header__title"><?= e(t('print_worksheet_title', $lang)) ?></h2>
                <p class="print-ref"><?= e(t('print_worksheet_ref', $lang)) ?> <?= e($printRef) ?></p>
            </div>
            <p class="print-header__intro"><?= e(t('print_worksheet_intro', $lang)) ?></p>
        </header>
        <div class="worksheet-visuals">
            <div class="print-grid" aria-label="<?= e(t('worksheet', $lang)) ?>">
                <div class="print-grid-header">
                    <div class="print-grid-label"></div>
                    <?php for ($col = 1; $col <= $gridSize; $col++): ?>
                        <div class="print-grid-label print-grid-label-green"><?= $col ?></div>
                    <?php endfor; ?>
                </div>
                <?php foreach ($result['grid'] as $row => $cells): ?>
                    <div class="print-grid-row">
                        <div class="print-grid-label print-grid-label-blue"><?= $row + 1 ?></div>
                        <?php foreach ($cells as $cell): ?>
                            <div class="print-grid-cell" style="background: #ffffff"></div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="print-grid worksheet-preview no-print" aria-label="<?= e(t('generated_image', $lang)) ?>">
                <div class="print-grid-header">
                    <div class="print-grid-label"></div>
                    <?php for ($col = 1; $col <= $gridSize; $col++): ?>
                        <div class="print-grid-label print-grid-label-green"><?= $col ?></div>
                    <?php endfor; ?>
                </div>
                <?php foreach ($result['grid'] as $row => $cells): ?>
                    <div class="print-grid-row">
                        <div class="print-grid-label print-grid-label-blue"><?= $row + 1 ?></div>
                        <?php foreach ($cells as $cell): ?>
                            <div class="print-grid-cell" style="background: <?= e($cell['hex']) ?>"></div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="print-color-legend" aria-label="<?= e(t('colors', $lang)) ?>">
            <?php foreach ($result['palette'] as $index => $color): ?>
                <div class="print-color-chip">
                    <span class="print-color-swatch" style="background: <?= e($color) ?>"></span>
                    <span class="print-color-chip__number"><?= $index + 1 ?></span>
                </div>
            <?php endforeach; ?>
        </div>

        <h3><?= e(t('exercises', $lang)) ?></h3>
        <div class="print-exercises">
            <?php foreach ($result['exercises'] as $index => $exercise): ?>
                <div class="print-exercise-item">
                    <?php render_exercise_formula($exercise, $lang); ?>
                    <span class="exercise-color-chip">
                        <span class="exercise-color-chip__swatch" style="background-color: <?= e($exercise['color']) ?>"></span>
                        <span class="exercise-color-chip__number"><?= e((string) $exercise['colorIndex']) ?></span>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="print-page print-only">
        <header class="print-header">
            <div class="print-header__top">
                <h2 class="print-header__title"><?= e(t('generated_image', $lang)) ?></h2>
                <p class="print-ref"><?= e(t('print_worksheet_ref', $lang)) ?> <?= e($printRef) ?></p>
            </div>
        </header>
        <img class="print-preview-image" src="data:image/png;base64,<?= base64_encode($result['previewImageData']) ?>" alt="<?= e(t('generated_image', $lang)) ?>">
    </div>
</div>
<?php if (!$isSavedView): ?>
    <?php view('partials/wizard-nav', ['wizardStep' => 'worksheet', 'lang' => $lang]); ?>
<?php endif; ?>
