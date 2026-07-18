<?php

/** @var bool $available */

?>
<div class="card guide-page">
    <?php if (empty($available)): ?>
        <h1><?= e(t('student_guide_title', $lang)) ?></h1>
        <p class="error-message"><?= e(t('student_guide_unavailable', $lang)) ?></p>
    <?php else: ?>
        <article class="guide-content">
            <?php view(student_guide_partial($lang)); ?>
        </article>
    <?php endif; ?>
</div>
