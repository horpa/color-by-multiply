<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = [];

if (version_compare(PHP_VERSION, '8.1.0', '<')) {
    $errors[] = 'PHP 8.1 or newer is required (current: ' . PHP_VERSION . ').';
}

foreach (['gd', 'fileinfo'] as $extension) {
    if (!extension_loaded($extension)) {
        $errors[] = 'Missing required PHP extension: ' . $extension;
    }
}

foreach ([
    $root . '/uploads' => 'uploads/',
    $root . '/storage/worksheets' => 'storage/worksheets/',
] as $path => $label) {
    if (!is_dir($path) && !mkdir($path, 0775, true) && !is_dir($path)) {
        $errors[] = 'Could not create directory: ' . $label;

        continue;
    }

    if (!is_writable($path)) {
        $errors[] = 'Directory is not writable: ' . $label;
    }
}

if (!is_file($root . '/public/index.php')) {
    $errors[] = 'public/index.php is missing.';
}

if ($errors === []) {
    echo "Environment OK\n";
    echo 'PHP ' . PHP_VERSION . "\n";
    echo "Extensions: gd, fileinfo\n";
    echo "Writable: uploads/, storage/worksheets/\n";
    exit(0);
}

fwrite(STDERR, "Environment check failed:\n");

foreach ($errors as $error) {
    fwrite(STDERR, '  - ' . $error . "\n");
}

exit(1);
