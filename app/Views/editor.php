<form method="post" class="card" id="pixel-editor-form">
    <input type="hidden" name="lang" value="<?= e($lang) ?>">
    <h2><?= e(t('pixel_editor_title', $lang)) ?></h2>
    <p><?= e(t('pixel_editor_help', $lang)) ?></p>

    <div class="editor-toolbar">
        <div class="background-indicator">
            <span class="background-indicator__label"><?= e(t('background', $lang)) ?>:</span>
            <span class="background-indicator__swatch" aria-hidden="true"></span>
            <span><?= e(t('background_white', $lang)) ?></span>
        </div>

        <button type="button" class="tool-button eraser-button" data-tool="eraser" aria-pressed="false">
            <?= e(t('eraser_tool', $lang)) ?>
        </button>
    </div>

    <div class="palette-row" role="toolbar" aria-label="<?= e(t('color_label', $lang)) ?>">
        <?php foreach ($palette as $index => $color): ?>
            <?php $gridIndex = $index + 1; ?>
            <div class="palette-item" data-palette-index="<?= $gridIndex ?>">
                <div class="palette-swatch-wrap">
                    <button
                        type="button"
                        class="palette-swatch<?= $gridIndex === 1 ? ' active' : '' ?>"
                        data-palette-index="<?= $gridIndex ?>"
                        style="background-color: <?= e($color) ?>"
                        title="<?= e(t('color_label', $lang) . ' ' . $gridIndex) ?>"
                        aria-label="<?= e(t('color_label', $lang) . ' ' . $gridIndex) ?>"
                        aria-pressed="<?= $gridIndex === 1 ? 'true' : 'false' ?>"
                    ></button>
                </div>
                <input type="hidden" name="palette[]" value="<?= e($color) ?>" data-palette-index="<?= $gridIndex ?>">
                <button type="button" class="palette-change-button" data-palette-index="<?= $gridIndex ?>">
                    <?= e(t('change_color', $lang)) ?>
                </button>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="palette-color-inputs" hidden aria-hidden="true">
        <?php foreach ($palette as $index => $color): ?>
            <?php $gridIndex = $index + 1; ?>
            <input type="color" class="palette-color-input" data-palette-index="<?= $gridIndex ?>" value="<?= e($color) ?>" tabindex="-1">
        <?php endforeach; ?>
    </div>

    <div class="pixel-grid" id="pixel-grid">
        <?php for ($row = 0; $row < $gridSize; $row++): ?>
            <?php for ($col = 0; $col < $gridSize; $col++): ?>
                <?php
                    $cellValue = (int) ($editorGrid[$row][$col]['paletteIndex'] ?? 0);
                    $cellHex = $cellValue === 0
                        ? '#ffffff'
                        : ($palette[$cellValue - 1] ?? '#000000');
                ?>
                <div class="pixel-cell-wrap">
                    <button
                        type="button"
                        class="pixel-cell"
                        data-row="<?= $row ?>"
                        data-col="<?= $col ?>"
                        style="background-color: <?= e($cellHex) ?>"
                        aria-label="<?= e(t('pixel_at', $lang)) ?> <?= $row + 1 ?>, <?= $col + 1 ?>"
                    ></button>
                    <input
                        type="hidden"
                        name="grid[<?= $row ?>][<?= $col ?>]"
                        value="<?= e((string) $cellValue) ?>"
                        data-grid-row="<?= $row ?>"
                        data-grid-col="<?= $col ?>"
                    >
                </div>
            <?php endfor; ?>
        <?php endfor; ?>
    </div>

    <div style="margin-top: 1rem;">
        <label>
            <?= e(t('question_type', $lang)) ?>
            <select name="question_mode">
                <option value="mixed"<?= $questionMode === 'mixed' ? ' selected' : '' ?>><?= e(t('question_mixed', $lang)) ?></option>
                <option value="multiplication"<?= $questionMode === 'multiplication' ? ' selected' : '' ?>><?= e(t('question_multiplication', $lang)) ?></option>
                <option value="division"<?= $questionMode === 'division' ? ' selected' : '' ?>><?= e(t('question_division', $lang)) ?></option>
            </select>
        </label>
        <button type="submit"><?= e(t('generate_button', $lang)) ?></button>
    </div>
</form>
<script><?php readfile(APP_ROOT . '/public/js/pixel-editor.js'); ?></script>
