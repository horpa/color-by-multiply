<?php

use Domain\Palette;

$palettePresets = Palette::presets();
$defaultPresetId = Palette::DEFAULT_PRESET_ID;
$defaultPresetColors = $palettePresets[$defaultPresetId];

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
        <li>
            <label>
                <input type="hidden" name="map_to_pencil_set" value="0">
                <input type="checkbox" name="map_to_pencil_set" value="1" checked>
                <?= e(t('map_to_pencil_set', $lang)) ?>
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

    <form method="post" class="blank-canvas-form" id="blank-canvas-form">
        <input type="hidden" name="lang" value="<?= e($lang) ?>">
        <input type="hidden" name="start_blank_canvas" value="1">
        <input
            type="hidden"
            name="palette_preset"
            id="palette-preset-input"
            value="<?= e($defaultPresetId) ?>"
        >

        <details class="palette-picker" id="palette-picker">
            <summary class="palette-picker__summary">
                <span class="palette-picker__summary-text">
                    <span class="palette-picker__summary-label"><?= e(t('palette_picker_label', $lang)) ?></span>
                    <span class="palette-picker__summary-name" data-palette-summary-name>
                        <?= e(t('palette_preset_' . $defaultPresetId, $lang)) ?>
                    </span>
                </span>
                <span class="palette-strip palette-picker__summary-strip" data-palette-summary-strip aria-hidden="true">
                    <?php foreach ($defaultPresetColors as $color): ?>
                        <span class="palette-strip__swatch" style="background-color: <?= e($color) ?>"></span>
                    <?php endforeach; ?>
                </span>
            </summary>

            <ul class="palette-picker__list" role="listbox" aria-label="<?= e(t('palette_picker_label', $lang)) ?>">
                <?php foreach ($palettePresets as $presetId => $colors): ?>
                    <?php
                        $isSelected = $presetId === $defaultPresetId;
                        $presetLabel = t('palette_preset_' . $presetId, $lang);
                    ?>
                    <li class="palette-picker__item" role="presentation">
                        <button
                            type="button"
                            class="palette-option<?= $isSelected ? ' palette-option--selected' : '' ?>"
                            role="option"
                            aria-selected="<?= $isSelected ? 'true' : 'false' ?>"
                            data-preset-id="<?= e($presetId) ?>"
                            data-preset-label="<?= e($presetLabel) ?>"
                        >
                            <span class="palette-option__name"><?= e($presetLabel) ?></span>
                            <span class="palette-strip palette-option__strip" aria-hidden="true">
                                <?php foreach ($colors as $color): ?>
                                    <span
                                        class="palette-strip__swatch"
                                        style="background-color: <?= e($color) ?>"
                                        data-color="<?= e($color) ?>"
                                    ></span>
                                <?php endforeach; ?>
                            </span>
                        </button>
                    </li>
                <?php endforeach; ?>
            </ul>
        </details>

        <p class="palette-picker__hint"><?= e(t('palette_picker_hint', $lang)) ?></p>

        <div class="form-actions">
            <button type="submit" class="btn btn--secondary"><?= e(t('blank_canvas_button', $lang)) ?></button>
        </div>
    </form>
</div>
<script><?php readfile(APP_ROOT . '/public/js/palette-picker.js'); ?></script>
