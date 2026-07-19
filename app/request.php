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
    $savedWorksheetId = $_SESSION['saved_worksheet_id'] ?? null;
    $libraryItems = worksheet_repository()->listSummaries();
    $canReprocessUpload = false;
    $uploadOptions = default_image_process_options();

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        apply_wizard_reset_if_requested();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['save_worksheet'])) {
            handle_save_worksheet_post($lang);
        } elseif (isset($_POST['grid']) && is_array($_POST['grid'])) {
            unset($_SESSION['saved_worksheet_id']);
            $palette = ImageExerciseGenerator::normalizePalette($_POST['palette'] ?? []);
            $editorGrid = ImageExerciseGenerator::normalizeGridFromPost($_POST['grid'], $palette);
            $questionMode = QuestionMode::fromString($_POST['question_mode'] ?? 'mixed')->value;
            $_SESSION['editor_grid'] = $editorGrid;
            $_SESSION['editor_palette'] = $palette;
            $_SESSION['show_editor'] = true;
            $_SESSION['question_mode'] = $questionMode;

            try {
                $result = ImageExerciseGenerator::processGrid($editorGrid, $palette, $questionMode);
                $_SESSION['worksheet_result'] = [
                    'grid' => $result['grid'],
                    'exercises' => $result['exercises'],
                    'palette' => $result['palette'],
                    'previewImageData' => $result['previewImageData'],
                ];
                redirect_to_wizard_step('worksheet', $lang);
            } catch (RuntimeException $exception) {
                if (str_contains($exception->getMessage(), 'colored pixel')) {
                    $error = t('error_no_colored_pixels', $lang);
                } else {
                    $error = $exception->getMessage();
                }
                $showEditor = true;
            } catch (Throwable $exception) {
                $error = $exception->getMessage();
                $showEditor = true;
            }
        } elseif (isset($_POST['start_blank_canvas'])) {
            unset($_SESSION['saved_worksheet_id'], $_SESSION['worksheet_result']);
            clear_upload_source_session();
            $presetId = (string) ($_POST['palette_preset'] ?? Palette::DEFAULT_PRESET_ID);
            if (!in_array($presetId, Palette::presetIds(), true)) {
                $presetId = Palette::DEFAULT_PRESET_ID;
            }
            $editorData = ImageExerciseGenerator::buildBlankEditor($presetId);
            $_SESSION['editor_grid'] = $editorData['grid'];
            $_SESSION['editor_palette'] = $editorData['palette'];
            $_SESSION['show_editor'] = true;
            redirect_to_wizard_step('edit', $lang);
        } elseif (isset($_POST['reprocess_upload'])) {
            try {
                rebuild_editor_from_stored_upload(read_image_process_options_from_post());
                redirect_to_wizard_step('edit', $lang);
            } catch (Throwable) {
                $error = t('error_reprocess_upload', $lang);
                $showEditor = !empty($_SESSION['show_editor']);
            }
        } elseif (isset($_FILES['image']) && is_uploaded_file($_FILES['image']['tmp_name'])) {
            unset($_SESSION['saved_worksheet_id']);
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
                $processOptions = read_image_process_options_from_post();

                try {
                    store_upload_source_in_session($targetPath, (string) $mimeType, $processOptions);
                    rebuild_editor_from_stored_upload($processOptions);
                    redirect_to_wizard_step('edit', $lang);
                } catch (Throwable $exception) {
                    clear_upload_source_session();
                    $error = t('error_invalid_upload', $lang);
                }
            }
        } elseif (!isset($_POST['delete_worksheet'])) {
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

    if (isset($_SESSION['question_mode'])) {
        $questionMode = QuestionMode::fromString((string) $_SESSION['question_mode'])->value;
    }

    if ($result === null && !empty($_SESSION['worksheet_result'])) {
        $result = $_SESSION['worksheet_result'];
    }

    if (isset($_SESSION['saved_worksheet_id'])) {
        $savedWorksheetId = (string) $_SESSION['saved_worksheet_id'];
    }

    $canReprocessUpload = has_reprocessable_upload();
    $uploadOptions = stored_image_process_options();

    $requestedStep = resolve_wizard_requested_step();
    $wizardStep = resolve_wizard_step($showEditor, $result, $requestedStep);
    $libraryItems = worksheet_repository()->listSummaries();

    return compact(
        'lang',
        'error',
        'result',
        'editorGrid',
        'palette',
        'showEditor',
        'questionMode',
        'gridSize',
        'maxPaletteColors',
        'wizardStep',
        'savedWorksheetId',
        'libraryItems',
        'canReprocessUpload',
        'uploadOptions',
    );
}

function handle_save_worksheet_post(string $lang): never
{
    $worksheet = $_SESSION['worksheet_result'] ?? null;

    if (!is_array($worksheet) || empty($worksheet['exercises'])) {
        redirect_to_wizard_step('worksheet', $lang);
    }

    $worksheet['questionMode'] = $_SESSION['question_mode'] ?? QuestionMode::Mixed->value;

    try {
        $id = worksheet_repository()->save($worksheet);
        $_SESSION['saved_worksheet_id'] = $id;
    } catch (Throwable) {
        $_SESSION['save_worksheet_error'] = true;
    }

    redirect_to_wizard_step('worksheet', $lang);
}

function handle_delete_worksheet_post(): never
{
    $lang = resolve_lang();
    $worksheetId = (string) ($_POST['worksheet_id'] ?? '');

    if ($worksheetId !== '') {
        worksheet_repository()->delete($worksheetId);
    }

    header('Location: ' . library_url($lang), true, 303);
    exit;
}

function handle_saved_worksheet_request(): array
{
    $lang = resolve_lang();
    $gridSize = Grid::SIZE;
    $id = (string) ($_GET['w'] ?? '');
    $worksheet = worksheet_repository()->find($id);
    $error = '';
    $available = $worksheet !== null;

    if (!$available) {
        $error = t('error_worksheet_not_found', $lang);
    }

    return compact(
        'lang',
        'gridSize',
        'id',
        'worksheet',
        'error',
        'available',
    );
}

function render_saved_worksheet_page(array $state): void
{
    extract($state, EXTR_SKIP);
    $wizardStep = null;

    if (!$available) {
        $content = render('partials/error', compact('error'));
        $content .= '<p class="no-print"><a href="' . e(library_url($lang)) . '">' . e(t('back_to_library', $lang)) . '</a></p>';
        view('layout', compact('lang', 'content', 'wizardStep'));

        return;
    }

    $result = $worksheet;
    $savedId = $id;
    $isSavedView = true;
    $content = render('worksheet', compact('lang', 'result', 'gridSize', 'savedId', 'isSavedView'));
    view('layout', compact('lang', 'content', 'wizardStep'));
}

function render_worksheet_preview(string $id): void
{
    $repository = worksheet_repository();
    $preview = $repository->readPreview($id);

    if ($preview === false) {
        http_response_code(404);
        exit;
    }

    header('Content-Type: image/png');
    header('Cache-Control: public, max-age=86400');
    echo $preview;
}

function clear_wizard_session(): void
{
    unset(
        $_SESSION['editor_grid'],
        $_SESSION['editor_palette'],
        $_SESSION['show_editor'],
        $_SESSION['worksheet_result'],
        $_SESSION['question_mode'],
        $_SESSION['saved_worksheet_id'],
    );
    clear_upload_source_session();
}

/** @return array{boost_contrast: bool, sharpen_edges: bool, map_to_pencil_set: bool} */
function default_image_process_options(): array
{
    return [
        'boost_contrast' => true,
        'sharpen_edges' => true,
        'map_to_pencil_set' => true,
    ];
}

/** @return array{boost_contrast: bool, sharpen_edges: bool, map_to_pencil_set: bool} */
function read_image_process_options_from_post(): array
{
    return [
        'boost_contrast' => ($_POST['boost_contrast'] ?? '0') === '1',
        'sharpen_edges' => ($_POST['sharpen_edges'] ?? '0') === '1',
        'map_to_pencil_set' => ($_POST['map_to_pencil_set'] ?? '0') === '1',
    ];
}

/** @return array{boost_contrast: bool, sharpen_edges: bool, map_to_pencil_set: bool} */
function stored_image_process_options(): array
{
    $stored = $_SESSION['upload_options'] ?? null;
    if (!is_array($stored)) {
        return default_image_process_options();
    }

    return [
        'boost_contrast' => !empty($stored['boost_contrast']),
        'sharpen_edges' => !empty($stored['sharpen_edges']),
        'map_to_pencil_set' => !empty($stored['map_to_pencil_set']),
    ];
}

function clear_upload_source_session(): void
{
    unset(
        $_SESSION['upload_source_path'],
        $_SESSION['upload_source_mime'],
        $_SESSION['upload_options'],
    );
}

/**
 * @param array{boost_contrast: bool, sharpen_edges: bool, map_to_pencil_set: bool} $options
 */
function store_upload_source_in_session(string $path, string $mimeType, array $options): void
{
    $_SESSION['upload_source_path'] = $path;
    $_SESSION['upload_source_mime'] = $mimeType;
    $_SESSION['upload_options'] = $options;
}

function is_safe_upload_path(string $path): bool
{
    $uploadsRoot = realpath(APP_ROOT . '/uploads');
    $resolved = realpath($path);

    if ($uploadsRoot === false || $resolved === false) {
        return false;
    }

    $uploadsRoot = rtrim(str_replace('\\', '/', $uploadsRoot), '/') . '/';
    $resolved = str_replace('\\', '/', $resolved);

    return str_starts_with($resolved, $uploadsRoot);
}

function has_reprocessable_upload(): bool
{
    $path = (string) ($_SESSION['upload_source_path'] ?? '');

    return $path !== '' && is_safe_upload_path($path) && is_file($path);
}

/**
 * @param array{boost_contrast: bool, sharpen_edges: bool, map_to_pencil_set: bool} $options
 */
function rebuild_editor_from_stored_upload(array $options): void
{
    $sourcePath = (string) ($_SESSION['upload_source_path'] ?? '');
    $mimeType = (string) ($_SESSION['upload_source_mime'] ?? '');

    if ($sourcePath === '' || !is_safe_upload_path($sourcePath) || !is_file($sourcePath)) {
        throw new RuntimeException('Stored upload is not available.');
    }

    $uploadDir = APP_ROOT . '/uploads';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $baseName = basename($sourcePath);
    $preparedPath = $uploadDir . '/modified-' . time() . '-' . $baseName;
    $prepareOptions = [
        'boost_contrast' => !empty($options['boost_contrast']),
        'sharpen_edges' => !empty($options['sharpen_edges']),
    ];
    $mapToPencilSet = !empty($options['map_to_pencil_set']);

    $preparedPath = ImageExerciseGenerator::prepareImage(
        $sourcePath,
        $prepareOptions,
        $preparedPath,
        $mimeType !== '' ? $mimeType : null,
    );
    $editorData = ImageExerciseGenerator::buildEditorFromFile(
        $preparedPath,
        'image/png',
        null,
        $mapToPencilSet,
    );

    unset($_SESSION['worksheet_result'], $_SESSION['saved_worksheet_id']);
    $_SESSION['editor_grid'] = $editorData['grid'];
    $_SESSION['editor_palette'] = $editorData['palette'];
    $_SESSION['show_editor'] = true;
    $_SESSION['upload_options'] = [
        'boost_contrast' => $prepareOptions['boost_contrast'],
        'sharpen_edges' => $prepareOptions['sharpen_edges'],
        'map_to_pencil_set' => $mapToPencilSet,
    ];
}

function apply_wizard_reset_if_requested(): void
{
    if (
        isset($_GET['step'], $_GET['reset'])
        && $_GET['step'] === 'upload'
        && $_GET['reset'] === '1'
    ) {
        clear_wizard_session();
    }
}

function resolve_wizard_requested_step(): ?string
{
    if (!isset($_GET['step'])) {
        return null;
    }

    $step = (string) $_GET['step'];

    if (!in_array($step, ['upload', 'edit', 'worksheet'], true)) {
        return null;
    }

    return $step;
}

function resolve_wizard_step(bool $showEditor, ?array $result, ?string $requestedStep): string
{
    if (isset($_GET['home']) && (string) $_GET['home'] === '1') {
        return 'home';
    }

    if ($requestedStep === 'upload') {
        return 'upload';
    }

    if ($requestedStep === 'edit') {
        return $showEditor ? 'edit' : 'upload';
    }

    if ($requestedStep === 'worksheet') {
        if ($result !== null) {
            return 'worksheet';
        }

        return $showEditor ? 'edit' : 'upload';
    }

    return 'home';
}

function redirect_to_wizard_step(string $step, string $lang): never
{
    $query = http_build_query([
        'step' => $step,
        'lang' => $lang,
    ]);

    header('Location: ?' . $query, true, 303);
    exit;
}

function render_app_page(array $state): void
{
    extract($state, EXTR_SKIP);

    $hasWorksheet = $result !== null;
    $content = '';

    if ($wizardStep !== 'home') {
        $content .= render('partials/wizard-stepper', compact('lang', 'wizardStep', 'showEditor', 'hasWorksheet'));
    }

    if (!empty($_SESSION['save_worksheet_error'])) {
        $error = t('error_save_worksheet', $lang);
        unset($_SESSION['save_worksheet_error']);
    }

    $content .= render('partials/error', compact('error'));

    if ($wizardStep === 'home') {
        $content .= render('library', compact('lang', 'libraryItems'));
    } elseif ($wizardStep === 'upload') {
        $content .= render('upload', compact('lang'));
    } elseif ($wizardStep === 'edit') {
        $content .= render('editor', compact(
            'lang',
            'palette',
            'editorGrid',
            'questionMode',
            'gridSize',
            'maxPaletteColors',
            'hasWorksheet',
            'canReprocessUpload',
            'uploadOptions',
        ));
    } elseif ($wizardStep === 'worksheet' && $result !== null) {
        $isSavedView = false;
        $savedId = is_string($savedWorksheetId ?? null) ? $savedWorksheetId : null;
        $content .= render('worksheet', compact('lang', 'result', 'gridSize', 'savedId', 'isSavedView'));
    }

    $wizardStep = $wizardStep ?? null;
    view('layout', compact('lang', 'content', 'wizardStep'));
}

function load_practice_worksheet(): ?array
{
    if (isset($_GET['w'])) {
        $worksheet = worksheet_repository()->find((string) $_GET['w']);

        return is_array($worksheet) ? $worksheet : null;
    }

    $sessionWorksheet = $_SESSION['worksheet_result'] ?? null;

    return is_array($sessionWorksheet) ? $sessionWorksheet : null;
}

function handle_practice_request(): array
{
    $lang = resolve_lang();
    $gridSize = Grid::SIZE;
    $worksheetId = isset($_GET['w']) ? (string) $_GET['w'] : null;
    $result = load_practice_worksheet();
    $available = is_array($result) && !empty($result['exercises']);

    return compact('lang', 'gridSize', 'result', 'available', 'worksheetId');
}

function render_practice_page(array $state): void
{
    extract($state, EXTR_SKIP);

    if (!$available) {
        $content = render('partials/practice-unavailable', compact('lang'));
        view('layout-practice', compact('lang', 'content'));

        return;
    }

    $pageTitle = t('practice_title', $lang);
    $content = render('practice', compact('lang', 'result', 'gridSize', 'worksheetId'));
    view('layout-practice', compact('lang', 'content', 'pageTitle'));
}

function handle_student_guide_request(): array
{
    $lang = resolve_lang();
    $partial = student_guide_partial($lang);
    $available = is_file(APP_ROOT . '/app/Views/' . $partial . '.php');

    return compact('lang', 'available');
}

function render_student_guide_page(array $state): void
{
    extract($state, EXTR_SKIP);
    $pageTitle = t('student_guide_title', $lang);
    $content = render('student-guide', compact('lang', 'available'));
    view('layout-practice', compact('lang', 'content', 'pageTitle'));
}
