<div class="panel library-panel">
    <div class="library-panel__header">
        <div>
            <h2 class="panel__title"><?= e(t('library_title', $lang)) ?></h2>
            <p class="panel__lead"><?= e(t('library_lead', $lang)) ?></p>
        </div>
        <a class="btn btn--primary" href="?step=upload&amp;reset=1&amp;lang=<?= e($lang) ?>"><?= e(t('library_create_button', $lang)) ?></a>
    </div>

    <?php if (empty($libraryItems)): ?>
        <p class="library-empty"><?= e(t('library_empty', $lang)) ?></p>
    <?php else: ?>
        <ul class="library-list">
            <?php foreach ($libraryItems as $item): ?>
                <li class="library-item">
                    <a class="library-item__link" href="<?= e(worksheet_url($item['id'], $lang)) ?>">
                        <span class="library-item__thumb-wrap">
                            <?php if (worksheet_repository()->previewExists($item['id'])): ?>
                                <img class="library-item__thumb" src="<?= e(preview_url($item['id'])) ?>" alt="">
                            <?php else: ?>
                                <span class="library-item__thumb library-item__thumb--empty" aria-hidden="true"></span>
                            <?php endif; ?>
                        </span>
                        <span class="library-item__meta">
                            <span class="library-item__date"><?= e(format_worksheet_date($item['createdAt'], $lang)) ?></span>
                            <span class="library-item__count"><?= e(sprintf(t('library_exercise_count', $lang), (int) $item['exerciseCount'])) ?></span>
                        </span>
                    </a>
                    <form method="post" class="library-item__delete" onsubmit="return confirm('<?= e(t('delete_confirm', $lang)) ?>')">
                        <input type="hidden" name="lang" value="<?= e($lang) ?>">
                        <input type="hidden" name="worksheet_id" value="<?= e($item['id']) ?>">
                        <button type="submit" name="delete_worksheet" value="1" class="btn btn--danger btn--small"><?= e(t('delete_button', $lang)) ?></button>
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>
