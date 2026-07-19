<?php

declare(strict_types=1);

namespace Domain;

final class Palette
{
    public const BACKGROUND_HEX = '#ffffff';
    public const MAX_FOREGROUND_COLORS = 7;
    public const MIN_FOREGROUND_COLORS = 1;
    private const FALLBACK_FOREGROUND = '#000000';
    public const DEFAULT_PRESET_ID = 'classic';

    /**
     * Standard 12-color pencil set (no white; worksheet background is white).
     *
     * @return array<string, string>
     */
    public static function pencilSet(): array
    {
        return [
            'black' => '#1a1a1a',
            'brown' => '#5d4037',
            'red' => '#b71c1c',
            'pink' => '#d81b60',
            'orange' => '#f57c00',
            'peach' => '#e8905c',
            'yellow' => '#fdd835',
            'lime' => '#7cb342',
            'sky_blue' => '#0288d1',
            'forest_green' => '#2e7d32',
            'navy' => '#0d47a1',
            'purple' => '#6a1b9a',
        ];
    }

    /** @return list<string> */
    public static function presetIds(): array
    {
        return array_keys(self::presets());
    }

    /** @return array<string, list<string>> */
    public static function presets(): array
    {
        return [
            'classic' => self::pencilColors('red', 'yellow', 'forest_green', 'navy'),
            'harvest' => self::pencilColors('red', 'orange', 'yellow', 'brown'),
            'forest' => self::pencilColors('lime', 'forest_green', 'yellow', 'black'),
            'terracotta' => self::pencilColors('orange', 'red', 'peach', 'brown'),
            'berry_dusk' => self::pencilColors('pink', 'purple', 'sky_blue', 'navy'),
        ];
    }

    /** @return list<string> */
    public static function presetColors(string $presetId): array
    {
        $presets = self::presets();

        return $presets[$presetId] ?? $presets[self::DEFAULT_PRESET_ID];
    }

    /** @return list<string> */
    public static function defaultForeground(): array
    {
        return [self::FALLBACK_FOREGROUND];
    }

    /** @return list<string> */
    public static function blankCanvasForeground(): array
    {
        return self::presetColors(self::DEFAULT_PRESET_ID);
    }

    /** @return list<string> */
    private static function pencilColors(string ...$keys): array
    {
        $set = self::pencilSet();
        $colors = [];

        foreach ($keys as $key) {
            if (!isset($set[$key])) {
                continue;
            }

            $colors[] = $set[$key];
        }

        return $colors;
    }
}
