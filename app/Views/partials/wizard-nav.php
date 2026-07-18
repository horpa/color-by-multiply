<?php if ($wizardStep === 'edit'): ?>
    <nav class="wizard-nav no-print">
        <a class="wizard-nav__link" href="?step=upload&amp;reset=1&amp;lang=<?= e($lang) ?>">← <?= e(t('wizard_back_upload', $lang)) ?></a>
        <?php if (!empty($hasWorksheet)): ?>
            <a class="wizard-nav__link" href="?step=worksheet&amp;lang=<?= e($lang) ?>"><?= e(t('wizard_forward_worksheet', $lang)) ?> →</a>
        <?php endif; ?>
    </nav>
<?php elseif ($wizardStep === 'worksheet'): ?>
    <nav class="wizard-nav no-print">
        <a class="wizard-nav__link" href="?step=edit&amp;lang=<?= e($lang) ?>">← <?= e(t('wizard_back_edit', $lang)) ?></a>
        <a class="wizard-nav__link" href="?step=upload&amp;reset=1&amp;lang=<?= e($lang) ?>"><?= e(t('wizard_back_upload', $lang)) ?></a>
    </nav>
<?php endif; ?>
