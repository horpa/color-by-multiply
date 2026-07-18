<?php

/** @var string $savedId */
/** @var string $lang */

?>
<div class="share-panel panel no-print">
    <h3 class="panel__section-title"><?= e(t('share_links_title', $lang)) ?></h3>
    <p class="panel__lead"><?= e(t('share_links_lead', $lang)) ?></p>

    <div class="share-link-row">
        <label class="share-link-row__label" for="share-url-full"><?= e(t('share_full_worksheet', $lang)) ?></label>
        <input
            id="share-url-full"
            class="share-url-input"
            type="text"
            readonly
            value="<?= e(absolute_worksheet_url($savedId, $lang)) ?>"
            onclick="this.select()"
        >
    </div>
</div>
