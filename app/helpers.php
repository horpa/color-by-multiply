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
