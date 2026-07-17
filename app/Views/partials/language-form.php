<form method="get" class="no-print" style="margin-bottom: 1rem;">
    <label>
        <?= e(t('language_label', $lang)) ?>
        <select name="lang" onchange="this.form.submit()">
            <option value="hu"<?= $lang === 'hu' ? ' selected' : '' ?>>Magyar</option>
            <option value="en"<?= $lang === 'en' ? ' selected' : '' ?>>English</option>
        </select>
    </label>
</form>
