<?php

declare(strict_types=1);

use Domain\Grid;
use Domain\Palette;
use Domain\QuestionMode;

function handle_app_request(): array
{
    $lang = resolve_lang();
    $error = '';
    $result = null;
    $editorGrid = null;
    $palette = ImageExerciseGenerator::getDefaultPaletteHex();
    $showEditor = false;
    $questionMode = QuestionMode::Mixed->value;
    $gridSize = Grid::SIZE;
    $maxPaletteColors = Palette::MAX_FOREGROUND_COLORS;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['grid']) && is_array($_POST['grid'])) {
            $palette = ImageExerciseGenerator::normalizePalette($_POST['palette'] ?? []);
            $editorGrid = ImageExerciseGenerator::normalizeGridFromPost($_POST['grid'], $palette);
            $questionMode = QuestionMode::fromString($_POST['question_mode'] ?? 'mixed')->value;
            $_SESSION['editor_grid'] = $editorGrid;
            $_SESSION['editor_palette'] = $palette;
            $_SESSION['show_editor'] = true;

            try {
                $result = ImageExerciseGenerator::processGrid($editorGrid, $palette, $questionMode);
                $_SESSION['worksheet_result'] = [
                    'grid' => $result['grid'],
                    'exercises' => $result['exercises'],
                    'palette' => $result['palette'],
                    'previewImageData' => $result['previewImageData'],
                ];
                $showEditor = true;
            } catch (RuntimeException $exception) {
                if (str_contains($exception->getMessage(), 'colored pixel')) {
                    $error = t('error_no_colored_pixels', $lang);
                } else {
                    $error = $exception->getMessage();
                }
            } catch (Throwable $exception) {
                $error = $exception->getMessage();
            }
        } elseif (isset($_FILES['image']) && is_uploaded_file($_FILES['image']['tmp_name'])) {
            $uploadDir = APP_ROOT . '/uploads';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '-', basename($_FILES['image']['name']));
            $targetPath = $uploadDir . '/' . time() . '-' . $safeName;

            if (!move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                $error = t('error_store_failed', $lang);
            } else {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($finfo, $targetPath);
                finfo_close($finfo);

                $preparedPath = $uploadDir . '/modified-' . time() . '-' . $safeName;
                $options = [
                    'boost_contrast' => ($_POST['boost_contrast'] ?? '1') === '1',
                    'sharpen_edges' => ($_POST['sharpen_edges'] ?? '1') === '1',
                ];

                try {
                    $preparedPath = ImageExerciseGenerator::prepareImage(
                        $targetPath,
                        $options,
                        $preparedPath,
                        $mimeType
                    );
                    $editorData = ImageExerciseGenerator::buildEditorFromFile($preparedPath, 'image/png', null);
                    $editorGrid = $editorData['grid'];
                    $palette = $editorData['palette'];
                    $result = null;
                    $_SESSION['editor_grid'] = $editorGrid;
                    $_SESSION['editor_palette'] = $palette;
                    $_SESSION['show_editor'] = true;
                    $showEditor = true;
                } catch (Throwable $exception) {
                    $error = t('error_invalid_upload', $lang);
                }
            }
        } else {
            $error = t('error_invalid_image', $lang);
        }
    }

    if ($editorGrid === null && isset($_SESSION['editor_grid'])) {
        $editorGrid = $_SESSION['editor_grid'];
        $palette = $_SESSION['editor_palette'] ?? $palette;
        $showEditor = !empty($_SESSION['show_editor']);
    }

    if ($editorGrid === null) {
        $editorGrid = ImageExerciseGenerator::normalizeGridFromPost([], $palette);
    }

    if ($result === null && !empty($_SESSION['worksheet_result'])) {
        $result = $_SESSION['worksheet_result'];
    }

    return compact('lang', 'error', 'result', 'editorGrid', 'palette', 'showEditor', 'questionMode', 'gridSize', 'maxPaletteColors');
}

function render_app_page(array $state): void
{
    extract($state, EXTR_SKIP);

    $content = render('partials/language-form', compact('lang'));
    $content .= render('upload', compact('lang'));
    $content .= render('partials/error', compact('error'));

    if ($showEditor) {
        $content .= render('editor', compact('lang', 'palette', 'editorGrid', 'questionMode', 'gridSize', 'maxPaletteColors'));
    }

    if ($result !== null) {
        $content .= render('worksheet', compact('lang', 'result', 'gridSize'));
    }

    view('layout', compact('lang', 'content'));
}

function handle_practice_request(): array
{
    $lang = resolve_lang();
    $gridSize = Grid::SIZE;
    $result = $_SESSION['worksheet_result'] ?? null;
    $available = is_array($result) && !empty($result['exercises']);

    return compact('lang', 'gridSize', 'result', 'available');
}

function render_practice_page(array $state): void
{
    extract($state, EXTR_SKIP);

    if (!$available) {
        $content = render('partials/practice-unavailable', compact('lang'));
        view('layout-practice', compact('lang', 'content'));

        return;
    }

    $content = render('practice', compact('lang', 'result', 'gridSize'));
    view('layout-practice', compact('lang', 'content'));
}
