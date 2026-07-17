<form method="post" enctype="multipart/form-data">
    <input type="hidden" name="lang" value="<?= e($lang) ?>">
    <div style="margin-bottom: 1rem;">
        <label><?= e(t('upload_file_label', $lang)) ?></label>
        <input type="file" name="image" accept="image/*" required>
    </div>
    <div style="margin-bottom: 1rem;">
        <label>
            <input type="hidden" name="boost_contrast" value="0">
            <input type="checkbox" name="boost_contrast" value="1" checked>
            <?= e(t('boost_contrast', $lang)) ?>
        </label>
    </div>
    <div style="margin-bottom: 1rem;">
        <label>
            <input type="hidden" name="sharpen_edges" value="0">
            <input type="checkbox" name="sharpen_edges" value="1" checked>
            <?= e(t('sharpen_edges', $lang)) ?>
        </label>
    </div>
    <button type="submit"><?= e(t('load_button', $lang)) ?></button>
</form>
