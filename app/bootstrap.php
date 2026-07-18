<?php

declare(strict_types=1);

if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}

require APP_ROOT . '/app/helpers.php';

$config = require APP_ROOT . '/app/config.php';
$localConfigPath = APP_ROOT . '/app/config.local.php';

if (is_file($localConfigPath)) {
    /** @var array<string, mixed> $localConfig */
    $localConfig = require $localConfigPath;
    $config = array_merge($config, $localConfig);
}

if (!defined('APP_CONFIG')) {
    define('APP_CONFIG', $config);
}

spl_autoload_register(static function (string $class): void {
    $prefixes = [
        'Domain\\' => APP_ROOT . '/src/Domain/',
        'Infrastructure\\' => APP_ROOT . '/src/Infrastructure/',
    ];

    foreach ($prefixes as $prefix => $baseDir) {
        if (!str_starts_with($class, $prefix)) {
            continue;
        }

        $relative = substr($class, strlen($prefix));
        $path = $baseDir . str_replace('\\', '/', $relative) . '.php';

        if (is_file($path)) {
            require $path;
        }

        return;
    }

    if ($class === 'ImageExerciseGenerator') {
        require APP_ROOT . '/src/ImageExerciseGenerator.php';
    }
});

require APP_ROOT . '/src/ImageExerciseGenerator.php';
require APP_ROOT . '/app/request.php';

session_start();
