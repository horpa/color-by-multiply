<?php

declare(strict_types=1);

use Domain\ExerciseBuilder;
use Domain\GridService;
use Domain\Palette;
use Domain\PaletteService;
use Infrastructure\GdImageProcessor;

class ImageExerciseGenerator
{
    private static ?PaletteService $paletteService = null;
    private static ?GridService $gridService = null;
    private static ?ExerciseBuilder $exerciseBuilder = null;
    private static ?GdImageProcessor $imageProcessor = null;

    public static function buildEditorFromFile(string $path, ?string $mimeType = null, ?array $palette = null): array
    {
        if (!is_file($path)) {
            throw new RuntimeException('The uploaded file could not be found.');
        }

        $resolvedPalette = self::imageProcessor()->resolvePalette($path, $mimeType, $palette);
        $grid = self::imageProcessor()->buildGridFromImage($path, $mimeType, $resolvedPalette);

        return [
            'grid' => self::gridService()->toEditorFormat($grid),
            'palette' => $resolvedPalette,
        ];
    }

    public static function processGrid(array $grid, array $palette, string $questionMode = 'mixed'): array
    {
        $palette = self::paletteService()->normalizeForegroundPalette($palette);
        $compacted = self::paletteService()->compactUsedColors($grid, $palette);
        $palette = $compacted['palette'];
        $grid = $compacted['remappedGrid'];
        $processed = self::gridService()->process($grid, $palette);

        return [
            'grid' => $processed['grid'],
            'foregroundPixels' => $processed['foregroundPixels'],
            'backgroundIndex' => $processed['backgroundIndex'],
            'backgroundHex' => Palette::BACKGROUND_HEX,
            'previewImageData' => self::imageProcessor()->renderPreview($processed['grid']),
            'exercises' => self::exerciseBuilder()->build($processed['foregroundPixels'], $questionMode),
            'palette' => $palette,
        ];
    }

    public static function prepareImage(string $sourcePath, array $options, string $outputPath, ?string $mimeType = null): string
    {
        return self::imageProcessor()->prepareImage($sourcePath, $options, $outputPath, $mimeType);
    }

    public static function normalizePalette(?array $palette): array
    {
        return self::paletteService()->normalizeForegroundPalette($palette);
    }

    public static function normalizeGridFromPost(array $grid, array $palette = []): array
    {
        return self::gridService()->normalizeFromPost($grid, $palette);
    }

    /** @return list<string> */
    public static function getDefaultPaletteHex(): array
    {
        return self::paletteService()->getDefaultPaletteHex();
    }

    private static function paletteService(): PaletteService
    {
        return self::$paletteService ??= new PaletteService();
    }

    private static function gridService(): GridService
    {
        return self::$gridService ??= new GridService();
    }

    private static function exerciseBuilder(): ExerciseBuilder
    {
        return self::$exerciseBuilder ??= new ExerciseBuilder();
    }

    private static function imageProcessor(): GdImageProcessor
    {
        return self::$imageProcessor ??= new GdImageProcessor(self::paletteService());
    }
}
