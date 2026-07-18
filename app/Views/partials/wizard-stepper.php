<?php

$steps = [
    'upload' => t('wizard_step_upload', $lang),
    'edit' => t('wizard_step_edit', $lang),
    'worksheet' => t('wizard_step_worksheet', $lang),
];

$stepOrder = ['upload', 'edit', 'worksheet'];
$activeIndex = array_search($wizardStep, $stepOrder, true);
if ($activeIndex === false) {
    $activeIndex = 0;
}

// Furthest reached step from session (so going back keeps later steps marked done).
$maxReachedIndex = $activeIndex;
if (!empty($hasWorksheet)) {
    $maxReachedIndex = max($maxReachedIndex, 2);
} elseif (!empty($showEditor)) {
    $maxReachedIndex = max($maxReachedIndex, 1);
}

?>
<nav class="panel no-print" aria-label="<?= e(t('wizard_step_upload', $lang)) ?>">
    <ol class="wizard-stepper">
        <?php foreach ($stepOrder as $index => $stepId): ?>
            <?php
                $stateClass = 'wizard-stepper__item';
                $isActive = $index === $activeIndex;
                $isDone = !$isActive && $index <= $maxReachedIndex;

                if ($isActive) {
                    $stateClass .= ' wizard-stepper__item--active';
                } elseif ($isDone) {
                    $stateClass .= ' wizard-stepper__item--done';
                }

                $marker = $isDone ? '✓' : (string) ($index + 1);
            ?>
            <li class="<?= e($stateClass) ?>">
                <span class="wizard-stepper__marker" aria-hidden="true"><?= e($marker) ?></span>
                <span class="wizard-stepper__label"><?= e($steps[$stepId]) ?></span>
            </li>
        <?php endforeach; ?>
    </ol>
</nav>
