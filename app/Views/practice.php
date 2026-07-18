<?php

$targetCells = [];

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
    ],
];

?>
<div class="card practice-page">
    <h1><?= e(t('practice_title', $lang)) ?></h1>
    <p>
        <?= e(t('practice_intro', $lang)) ?>
        <a class="practice-guide-link" href="<?= e(student_guide_url($lang)) ?>" target="_blank" rel="noopener noreferrer"><?= e(t('practice_guide_link', $lang)) ?></a>
    </p>

    <div class="practice-layout">
        <div class="practice-grid-section">
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

            <div class="print-color-legend" aria-label="<?= e(t('colors', $lang)) ?>">
                <?php foreach ($result['palette'] as $index => $color): ?>
                    <div class="print-color-chip">
                        <span class="print-color-swatch" style="background: <?= e($color) ?>"></span>
                        <span class="print-color-chip__number"><?= $index + 1 ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="practice-exercises-section">
            <h2><?= e(t('exercises', $lang)) ?></h2>
            <div class="print-exercises practice-exercises" id="practice-exercises">
                <?php foreach ($result['exercises'] as $index => $exercise): ?>
                    <div class="print-exercise-item practice-exercise-item" data-exercise-index="<?= $index ?>">
                        <?php render_practice_exercise_formula($exercise, $lang, $index); ?>
                        <span class="exercise-color-chip">
                            <span class="exercise-color-chip__swatch" style="background-color: <?= e($exercise['color']) ?>"></span>
                            <span class="exercise-color-chip__number"><?= e((string) $exercise['colorIndex']) ?></span>
                        </span>
                        <span class="practice-feedback" aria-live="polite"></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <p class="practice-complete" id="practice-complete" hidden><?= e(t('practice_complete', $lang)) ?></p>
</div>

<script type="application/json" id="practice-data"><?= json_encode($practicePayload, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
<script><?php readfile(APP_ROOT . '/public/js/practice.js'); ?></script>
