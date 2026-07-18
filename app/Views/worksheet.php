<div class="card printable">
    <div class="no-print worksheet-actions" style="margin-bottom: 1rem;">
        <button type="button" onclick="window.print()"><?= e(t('print_preview', $lang)) ?></button>
        <button type="button" onclick="window.open('?practice=1&amp;lang=<?= e($lang) ?>', '_blank', 'noopener,noreferrer')"><?= e(t('practice_button', $lang)) ?></button>
    </div>

    <div class="print-page">
        <h2><?= e(t('worksheet', $lang)) ?></h2>
        <div class="print-grid">
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

        <div class="print-color-legend" aria-label="<?= e(t('colors', $lang)) ?>">
            <?php foreach ($result['palette'] as $index => $color): ?>
                <div class="print-color-chip">
                    <span class="print-color-swatch" style="background: <?= e($color) ?>"></span>
                    <span class="print-color-chip__number"><?= $index + 1 ?></span>
                </div>
            <?php endforeach; ?>
        </div>

        <h3 style="margin-top: 1rem;"><?= e(t('exercises', $lang)) ?></h3>
        <div class="print-exercises">
            <?php foreach ($result['exercises'] as $exercise): ?>
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
        <img src="data:image/png;base64,<?= base64_encode($result['previewImageData']) ?>" alt="<?= e(t('generated_image', $lang)) ?>" style="max-width: 100%; height: auto; width: 420px;">
        <h3 style="margin-top: 1rem;"><?= e(t('solutions', $lang)) ?></h3>
        <div class="print-solution-list">
            <?php foreach ($result['exercises'] as $exercise): ?>
                <div class="print-solution-item">
                    <?php if ($exercise['type'] === 'multiplication'): ?>
                        <?= e((string) $exercise['x']) ?> × <?= e((string) $exercise['y']) ?> = <?= e((string) $exercise['a']) ?>
                    <?php else: ?>
                        <?= e((string) $exercise['a']) ?> ÷ <?= e((string) $exercise['x']) ?> = <?= e((string) $exercise['y']) ?>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
