<?php

if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}

function asset(string $path): string
{
    static $prefix = null;

    if ($prefix === null) {
        $publicDir = realpath(APP_ROOT . DIRECTORY_SEPARATOR . 'public');
        $docRoot = isset($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : false;
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';

        if ($publicDir !== false && $docRoot !== false && $publicDir === $docRoot) {
            $prefix = '';
        } elseif (str_contains($scriptName, '/public/')) {
            $prefix = '';
        } else {
            $prefix = 'public/';
        }
    }

    return $prefix . ltrim(str_replace('\\', '/', $path), '/');
}

function t(string $key, string $lang): string
{
    static $translations = [];

    if (!isset($translations[$lang])) {
        $file = APP_ROOT . '/app/lang/' . $lang . '.php';
        $translations[$lang] = is_file($file) ? require $file : [];
    }

    if (!isset($translations['hu'])) {
        $file = APP_ROOT . '/app/lang/hu.php';
        $translations['hu'] = is_file($file) ? require $file : [];
    }

    return $translations[$lang][$key] ?? $translations['hu'][$key] ?? $key;
}

function resolve_lang(): string
{
    $lang = 'hu';

    if (isset($_POST['lang']) && in_array($_POST['lang'], ['hu', 'en'], true)) {
        $lang = $_POST['lang'];
    } elseif (isset($_GET['lang']) && in_array($_GET['lang'], ['hu', 'en'], true)) {
        $lang = $_GET['lang'];
    } elseif (isset($_SESSION['lang']) && in_array($_SESSION['lang'], ['hu', 'en'], true)) {
        $lang = $_SESSION['lang'];
    }

    $_SESSION['lang'] = $lang;

    return $lang;
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

/** @param array<string, mixed> $exercise */
function render_exercise_formula(array $exercise, string $lang): void
{
    $isMultiplication = $exercise['type'] === 'multiplication';
    $solveForRow = ($exercise['solveFor'] ?? 'row') === 'row';
    $rowBlankLabel = e(t('color_label', $lang) . ' ' . ($exercise['row'] ?? ''));
    $columnBlankLabel = e(t('color_label', $lang) . ' ' . ($exercise['column'] ?? ''));

    if ($isMultiplication) {
        if ($solveForRow) {
            echo '<span class="exercise-blank exercise-blank--row" aria-label="' . $rowBlankLabel . '"></span>';
            echo '<span>×</span>';
            echo '<span class="exercise-green">' . e((string) $exercise['y']) . '</span>';
        } else {
            echo '<span class="exercise-blue">' . e((string) $exercise['x']) . '</span>';
            echo '<span>×</span>';
            echo '<span class="exercise-blank exercise-blank--column" aria-label="' . $columnBlankLabel . '"></span>';
        }

        echo '<span>=</span>';
        echo '<span>' . e((string) $exercise['a']) . '</span>';

        return;
    }

    echo '<span>' . e((string) $exercise['a']) . '</span>';
    echo '<span>÷</span>';

    if ($solveForRow) {
        echo '<span class="exercise-blank exercise-blank--row" aria-label="' . $rowBlankLabel . '"></span>';
        echo '<span>=</span>';
        echo '<span class="exercise-green">' . e((string) $exercise['y']) . '</span>';
    } else {
        echo '<span class="exercise-blue">' . e((string) $exercise['x']) . '</span>';
        echo '<span>=</span>';
        echo '<span class="exercise-blank exercise-blank--column" aria-label="' . $columnBlankLabel . '"></span>';
    }
}

/** @param array<string, mixed> $exercise */
function render_practice_exercise_formula(array $exercise, string $lang, int $index): void
{
    $isMultiplication = $exercise['type'] === 'multiplication';
    $solveForRow = ($exercise['solveFor'] ?? 'row') === 'row';
    $rowBlankLabel = e(t('color_label', $lang) . ' ' . ($exercise['row'] ?? ''));
    $columnBlankLabel = e(t('color_label', $lang) . ' ' . ($exercise['column'] ?? ''));

    $renderInput = static function (string $variant, string $label) use ($index): void {
        echo '<input type="number" min="1" max="10" inputmode="numeric" autocomplete="off"'
            . ' class="practice-answer practice-answer--' . $variant . '"'
            . ' data-exercise-index="' . $index . '"'
            . ' aria-label="' . $label . '">';
    };

    echo '<span class="practice-formula">';

    if ($isMultiplication) {
        if ($solveForRow) {
            $renderInput('row', $rowBlankLabel);
            echo '<span class="practice-formula__op">×</span>';
            echo '<span class="exercise-green">' . e((string) $exercise['y']) . '</span>';
        } else {
            echo '<span class="exercise-blue">' . e((string) $exercise['x']) . '</span>';
            echo '<span class="practice-formula__op">×</span>';
            $renderInput('column', $columnBlankLabel);
        }

        echo '<span class="practice-formula__op">=</span>';
        echo '<span>' . e((string) $exercise['a']) . '</span>';
        echo '</span>';

        return;
    }

    echo '<span>' . e((string) $exercise['a']) . '</span>';
    echo '<span class="practice-formula__op">÷</span>';

    if ($solveForRow) {
        $renderInput('row', $rowBlankLabel);
        echo '<span class="practice-formula__op">=</span>';
        echo '<span class="exercise-green">' . e((string) $exercise['y']) . '</span>';
    } else {
        echo '<span class="exercise-blue">' . e((string) $exercise['x']) . '</span>';
        echo '<span class="practice-formula__op">=</span>';
        $renderInput('column', $columnBlankLabel);
    }

    echo '</span>';
}

function student_guide_url(string $lang): string
{
    return '?student_guide=1&lang=' . rawurlencode($lang);
}

function student_guide_partial(string $lang): string
{
    return 'partials/student-guide-' . ($lang === 'en' ? 'en' : 'hu');
}

function app_config(string $key, mixed $default = null): mixed
{
    $config = defined('APP_CONFIG') ? APP_CONFIG : [];

    return $config[$key] ?? $default;
}

function admin_key_ok(?string $provided): bool
{
    $expected = (string) app_config('admin_key', '');
    if ($expected === '' || $provided === null || $provided === '') {
        return false;
    }

    return hash_equals($expected, $provided);
}

function worksheet_repository(): Infrastructure\WorksheetRepository
{
    static $repository = null;

    return $repository ??= new Infrastructure\WorksheetRepository();
}

function current_request_url(array $params = []): string
{
    $query = array_merge($_GET, $params);
    unset($query['key']);

    foreach ($query as $name => $value) {
        if ($value === null || $value === '') {
            unset($query[$name]);
        }
    }

    $queryString = http_build_query($query);

    return $queryString === '' ? '?' : '?' . $queryString;
}

function worksheet_url(string $id, string $lang): string
{
    return '?' . http_build_query([
        'w' => $id,
        'lang' => $lang,
    ]);
}

function practice_url(string $lang, ?string $worksheetId = null): string
{
    $params = [
        'practice' => '1',
        'lang' => $lang,
    ];

    if ($worksheetId !== null) {
        $params['w'] = $worksheetId;
    }

    return '?' . http_build_query($params);
}

function preview_url(string $id): string
{
    return '?preview=' . rawurlencode($id);
}

function library_url(string $lang, ?string $adminKey = null): string
{
    $params = [
        'lang' => $lang,
        'home' => '1',
    ];

    if ($adminKey !== null && $adminKey !== '' && admin_key_ok($adminKey)) {
        $params['key'] = $adminKey;
    }

    return '?' . http_build_query($params);
}

function format_worksheet_date(string $createdAt, string $lang): string
{
    if ($createdAt === '') {
        return '';
    }

    try {
        $date = new DateTimeImmutable($createdAt);

        return $date->format($lang === 'hu' ? 'Y. m. d. H:i' : 'M j, Y g:i A');
    } catch (Exception) {
        return $createdAt;
    }
}

function absolute_url(string $query): string
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
    $path = str_starts_with($query, '?') ? $query : '?' . $query;

    return $scheme . '://' . $host . $script . $path;
}

function absolute_worksheet_url(string $id, string $lang): string
{
    return absolute_url(worksheet_url($id, $lang));
}

function view(string $name, array $data = []): void
{
    extract($data, EXTR_SKIP);
    require APP_ROOT . '/app/Views/' . $name . '.php';
}

function render(string $name, array $data = []): string
{
    ob_start();
    view($name, $data);

    return (string) ob_get_clean();
}
