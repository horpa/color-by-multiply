<form method="get" class="lang-form no-print">
    <?php if (!empty($wizardStep) && $wizardStep !== 'home'): ?>
        <input type="hidden" name="step" value="<?= e($wizardStep) ?>">
    <?php elseif (!empty($_GET['home'])): ?>
        <input type="hidden" name="home" value="1">
    <?php endif; ?>
    <?php if (!empty($_GET['key'])): ?>
        <input type="hidden" name="key" value="<?= e((string) $_GET['key']) ?>">
    <?php endif; ?>
    <?php if (!empty($_GET['w'])): ?>
        <input type="hidden" name="w" value="<?= e((string) $_GET['w']) ?>">
    <?php endif; ?>
    <label>
        <?= e(t('language_label', $lang)) ?>
        <select name="lang" onchange="this.form.submit()">
            <option value="hu"<?= $lang === 'hu' ? ' selected' : '' ?>>Magyar</option>
            <option value="en"<?= $lang === 'en' ? ' selected' : '' ?>>English</option>
        </select>
    </label>
</form>
