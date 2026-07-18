<?php

use Domain\Palette;

$blankPalette = Palette::blankCanvasForeground();

?>
<form method="post" enctype="multipart/form-data" class="panel">
    <input type="hidden" name="lang" value="<?= e($lang) ?>">
    <h2 class="panel__title"><?= e(t('wizard_step_upload', $lang)) ?></h2>
    <p class="panel__lead"><?= e(t('upload_panel_lead', $lang)) ?></p>

    <div class="upload-zone">
        <span class="upload-zone__icon" aria-hidden="true">↑</span>
        <label class="upload-zone__label" for="upload-image"><?= e(t('upload_file_label', $lang)) ?></label>
        <input type="file" id="upload-image" name="image" accept="image/*" required>
    </div>

    <p class="panel__section-title"><?= e(t('upload_options_title', $lang)) ?></p>
    <ul class="option-list">
        <li>
            <label>
                <input type="hidden" name="boost_contrast" value="0">
                <input type="checkbox" name="boost_contrast" value="1" checked>
                <?= e(t('boost_contrast', $lang)) ?>
            </label>
        </li>
        <li>
            <label>
                <input type="hidden" name="sharpen_edges" value="0">
                <input type="checkbox" name="sharpen_edges" value="1" checked>
                <?= e(t('sharpen_edges', $lang)) ?>
            </label>
        </li>
    </ul>

    <div class="form-actions">
        <button type="submit" class="btn btn--primary"><?= e(t('load_button', $lang)) ?></button>
    </div>
</form>

<div class="panel upload-blank-panel">
    <h2 class="panel__title"><?= e(t('blank_canvas_title', $lang)) ?></h2>
    <p class="panel__lead"><?= e(t('blank_canvas_lead', $lang)) ?></p>

    <div class="blank-palette-preview" aria-label="<?= e(t('color_label', $lang)) ?>">
        <?php foreach ($blankPalette as $color): ?>
            <span class="blank-palette-preview__swatch" style="background-color: <?= e($color) ?>"></span>
        <?php endforeach; ?>
    </div>

    <form method="post" class="form-actions">
        <input type="hidden" name="lang" value="<?= e($lang) ?>">
        <input type="hidden" name="start_blank_canvas" value="1">
        <button type="submit" class="btn btn--secondary"><?= e(t('blank_canvas_button', $lang)) ?></button>
    </form>
</div>
