<?php

$targetCells = [];
$exerciseCount = count($result['exercises']);

foreach ($result['exercises'] as $exercise) {
    $targetCells[(int) $exercise['row'] . ':' . (int) $exercise['column']] = true;
}

$practicePayload = [
    'exercises' => array_values(array_map(
        static function (array $exercise, int $index): array {
            return [
                'index' => $index,
                'row' => (int) $exercise['row'],
                'column' => (int) $exercise['column'],
                'answer' => (int) $exercise['answer'],
                'color' => (string) $exercise['color'],
            ];
        },
        $result['exercises'],
        array_keys($result['exercises'])
    )),
    'messages' => [
        'correct' => t('practice_correct', $lang),
        'wrong' => t('practice_wrong', $lang),
        'complete' => t('practice_complete', $lang),
        'progress' => t('practice_progress', $lang),
    ],
];

?>
<div class="practice-page">
    <header class="practice-header">
        <div class="practice-header__text">
            <h1 class="practice-header__title"><?= e(t('practice_title', $lang)) ?></h1>
            <p class="practice-header__intro"><?= e(t('practice_intro', $lang)) ?></p>
        </div>
    </header>

    <div
        class="practice-progress"
        id="practice-progress"
        role="status"
        aria-live="polite"
        data-total="<?= (int) $exerciseCount ?>"
    >
        <div class="practice-progress__meta">
            <span class="practice-progress__label"><?= e(t('practice_progress_label', $lang)) ?></span>
            <span class="practice-progress__count" id="practice-progress-count">0 / <?= (int) $exerciseCount ?></span>
        </div>
        <div class="practice-progress__track" aria-hidden="true">
            <div class="practice-progress__fill" id="practice-progress-fill" style="width: 0%"></div>
        </div>
    </div>

    <div class="practice-layout">
        <aside class="practice-grid-section" aria-label="<?= e(t('worksheet', $lang)) ?>">
            <div class="practice-grid-card">
                <div class="print-grid practice-grid" id="practice-grid">
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
                                <?php
                                    $rowNum = (int) $cell['row'];
                                    $colNum = (int) $cell['column'];
                                    $cellKey = $rowNum . ':' . $colNum;
                                    $isTarget = isset($targetCells[$cellKey]);
                                ?>
                                <div
                                    class="print-grid-cell practice-grid-cell<?= $isTarget ? ' practice-grid-cell--target' : '' ?>"
                                    data-row="<?= $rowNum ?>"
                                    data-col="<?= $colNum ?>"
                                    style="background: #ffffff"
                                ></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="print-color-legend practice-color-legend" aria-label="<?= e(t('colors', $lang)) ?>">
                    <?php foreach ($result['palette'] as $index => $color): ?>
                        <div class="print-color-chip">
                            <span class="print-color-swatch" style="background: <?= e($color) ?>"></span>
                            <span class="print-color-chip__number"><?= $index + 1 ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </aside>

        <section class="practice-exercises-section" aria-labelledby="practice-exercises-heading">
            <h2 class="practice-exercises-heading" id="practice-exercises-heading"><?= e(t('exercises', $lang)) ?></h2>
            <ol class="practice-exercises" id="practice-exercises">
                <?php foreach ($result['exercises'] as $index => $exercise): ?>
                    <li class="practice-exercise-item" data-exercise-index="<?= $index ?>">
                        <span class="practice-exercise-item__index" aria-hidden="true"><?= $index + 1 ?></span>
                        <div class="practice-exercise-item__body">
                            <?php render_practice_exercise_formula($exercise, $lang, $index); ?>
                            <span class="exercise-color-chip" title="<?= e(t('color_label', $lang) . ' ' . $exercise['colorIndex']) ?>">
                                <span class="exercise-color-chip__swatch" style="background-color: <?= e($exercise['color']) ?>"></span>
                                <span class="exercise-color-chip__number"><?= e((string) $exercise['colorIndex']) ?></span>
                            </span>
                        </div>
                        <span class="practice-feedback" aria-live="polite"></span>
                    </li>
                <?php endforeach; ?>
            </ol>
        </section>
    </div>

    <p class="practice-complete" id="practice-complete" hidden><?= e(t('practice_complete', $lang)) ?></p>
</div>

<script type="application/json" id="practice-data"><?= json_encode($practicePayload, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
<script><?php readfile(APP_ROOT . '/public/js/practice.js'); ?></script>
