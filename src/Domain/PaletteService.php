<?php

declare(strict_types=1);

namespace Domain;

use RuntimeException;

final class PaletteService
{
    private const ALPHA_WHITE_THRESHOLD = 128;
    /** Brightness alone is not enough — light yellow/peach must stay as foreground. */
    private const NEAR_WHITE_BRIGHTNESS = 245;
    private const NEAR_WHITE_MAX_SATURATION = 28;
    private const COLOR_MERGE_DISTANCE = 120;

    /** @return list<string> */
    public function normalizeForegroundPalette(?array $palette): array
    {
        $normalized = [];

        if (is_array($palette)) {
            foreach ($palette as $entry) {
                if (!is_string($entry) || !$this->isHexColor($entry)) {
                    continue;
                }

                $hex = strtolower($entry);
                if ($this->isBackgroundColor($hex)) {
                    continue;
                }

                if ($this->isTooSimilarToExistingColors($hex, $normalized)) {
                    continue;
                }

                $normalized[] = $hex;
            }
        }

        $normalized = array_values(array_unique($normalized));

        if ($normalized === []) {
            $normalized = Palette::defaultForeground();
        }

        return array_slice($normalized, 0, Palette::MAX_FOREGROUND_COLORS);
    }

    /** @deprecated Use normalizeForegroundPalette() */
    public function normalize(?array $palette): array
    {
        return $this->normalizeForegroundPalette($palette);
    }

    /** @return list<string> */
    public function getDefaultPaletteHex(): array
    {
        return Palette::defaultForeground();
    }

    /**
     * @param resource $image
     * @return list<string>
     */
    public function extractFromImage(
        $image,
        int $maxColors = Palette::MAX_FOREGROUND_COLORS,
        bool $mapToPencilSet = false,
    ): array {
        $colorCounts = [];
        $width = imagesx($image);
        $height = imagesy($image);

        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                $rgba = $this->readPixel($image, $x, $y);
                if ($this->isBackgroundPixel($rgba['red'], $rgba['green'], $rgba['blue'], $rgba['alpha'])) {
                    continue;
                }

                $hex = $mapToPencilSet
                    ? $this->nearestPencilHex($rgba['red'], $rgba['green'], $rgba['blue'])
                    : $this->rgbToHex($rgba['red'], $rgba['green'], $rgba['blue']);
                $colorCounts[$hex] = ($colorCounts[$hex] ?? 0) + 1;
            }
        }

        if ($colorCounts === []) {
            return Palette::defaultForeground();
        }

        arsort($colorCounts);
        $palette = [];

        foreach (array_keys($colorCounts) as $hex) {
            if (count($palette) >= $maxColors) {
                break;
            }

            if (!$mapToPencilSet && $this->isTooSimilarToExistingColors($hex, $palette)) {
                continue;
            }

            $palette[] = strtolower($hex);
        }

        if ($palette === []) {
            $palette[] = strtolower((string) array_key_first($colorCounts));
        }

        return $palette;
    }

    public function nearestPencilHex(int $red, int $green, int $blue): string
    {
        $bestHex = Palette::defaultForeground()[0];
        $bestDistance = PHP_INT_MAX;

        foreach ($this->pencilColorCandidates() as $candidate) {
            $distance = $this->colorDistance(
                $red,
                $green,
                $blue,
                $candidate['r'],
                $candidate['g'],
                $candidate['b'],
            );

            if ($distance < $bestDistance) {
                $bestDistance = $distance;
                $bestHex = $candidate['hex'];
            }
        }

        return $bestHex;
    }

    /**
     * @param list<string> $foregroundPalette
     */
    public function quantizeToPencilGridIndex(
        int $red,
        int $green,
        int $blue,
        int $alpha,
        array $foregroundPalette,
    ): int {
        if ($this->isBackgroundPixel($red, $green, $blue, $alpha)) {
            return 0;
        }

        $pencilHex = $this->nearestPencilHex($red, $green, $blue);
        $pencilRgb = $this->hexToRgb($pencilHex);

        return $this->quantizeToGridIndex(
            $pencilRgb['r'],
            $pencilRgb['g'],
            $pencilRgb['b'],
            $alpha,
            $foregroundPalette,
        );
    }

    /**
     * @param list<list<array{paletteIndex: int}>> $grid
     * @param list<string> $foregroundPalette
     * @return array{
     *     palette: list<string>,
     *     remappedGrid: list<list<array{paletteIndex: int}>>,
     *     colorIndexByGridIndex: array<int, int>
     * }
     */
    public function compactUsedColors(array $grid, array $foregroundPalette): array
    {
        $foregroundPalette = $this->normalizeForegroundPalette($foregroundPalette);
        $usedIndices = [];

        foreach ($grid as $row) {
            foreach ($row as $cell) {
                $index = (int) ($cell['paletteIndex'] ?? 0);
                if ($index > 0) {
                    $usedIndices[$index] = true;
                }
            }
        }

        ksort($usedIndices, SORT_NUMERIC);
        $usedIndexList = array_keys($usedIndices);

        if ($usedIndexList === []) {
            throw new RuntimeException('Add at least one colored pixel before generating exercises.');
        }

        $compactPalette = [];
        $remap = [0 => 0];
        $colorIndexByGridIndex = [];

        foreach ($usedIndexList as $position => $oldIndex) {
            $newIndex = $position + 1;
            $remap[$oldIndex] = $newIndex;
            $compactPalette[] = $foregroundPalette[$oldIndex - 1] ?? Palette::defaultForeground()[0];
            $colorIndexByGridIndex[$newIndex] = $newIndex;
        }

        $remappedGrid = [];
        foreach ($grid as $rowIndex => $row) {
            foreach ($row as $colIndex => $cell) {
                $oldIndex = (int) ($cell['paletteIndex'] ?? 0);
                $remappedGrid[$rowIndex][$colIndex] = [
                    'paletteIndex' => $remap[$oldIndex] ?? 0,
                ];
            }
        }

        return [
            'palette' => $compactPalette,
            'remappedGrid' => $remappedGrid,
            'colorIndexByGridIndex' => $colorIndexByGridIndex,
        ];
    }

    public function hexForGridIndex(int $gridIndex, array $foregroundPalette): string
    {
        if ($gridIndex === 0) {
            return Palette::BACKGROUND_HEX;
        }

        return $foregroundPalette[$gridIndex - 1] ?? Palette::defaultForeground()[0];
    }

    /**
     * @param list<string> $foregroundPalette
     */
    public function quantizeToGridIndex(int $red, int $green, int $blue, int $alpha, array $foregroundPalette): int
    {
        if ($this->isBackgroundPixel($red, $green, $blue, $alpha)) {
            return 0;
        }

        $foregroundPalette = $this->normalizeForegroundPalette($foregroundPalette);
        $bestSlot = 0;
        $bestDistance = PHP_INT_MAX;

        foreach ($foregroundPalette as $slot => $candidate) {
            $candidateRgb = $this->hexToRgb($candidate);
            $distance = $this->colorDistance(
                $red,
                $green,
                $blue,
                $candidateRgb['r'],
                $candidateRgb['g'],
                $candidateRgb['b']
            );

            if ($distance < $bestDistance) {
                $bestDistance = $distance;
                $bestSlot = $slot;
            }
        }

        return $bestSlot + 1;
    }

    /** @return array{red: int, green: int, blue: int, alpha: int} */
    public function readPixel($image, int $x, int $y): array
    {
        $pixelColor = imagecolorat($image, $x, $y);
        $rgba = imagecolorsforindex($image, $pixelColor);

        return [
            'red' => (int) $rgba['red'],
            'green' => (int) $rgba['green'],
            'blue' => (int) $rgba['blue'],
            'alpha' => (int) ($rgba['alpha'] ?? 0),
        ];
    }

    /** @return array{r: int, g: int, b: int} */
    public function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        return [
            'r' => hexdec(substr($hex, 0, 2)),
            'g' => hexdec(substr($hex, 2, 2)),
            'b' => hexdec(substr($hex, 4, 2)),
        ];
    }

    public function rgbToHex(int $r, int $g, int $b): string
    {
        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }

    public function isBackgroundColor(string $hex): bool
    {
        $normalized = strtolower(trim($hex));

        if (
            $normalized === 'transparent'
            || $normalized === '#00000000'
            || $normalized === 'rgba(0,0,0,0)'
            || $normalized === 'rgba(0, 0, 0, 0)'
            || $normalized === Palette::BACKGROUND_HEX
        ) {
            return true;
        }

        $rgb = $this->hexToRgb($normalized);

        return $this->isBackgroundPixel($rgb['r'], $rgb['g'], $rgb['b'], 0);
    }

    public function isBackgroundPixel(int $red, int $green, int $blue, int $alpha): bool
    {
        if ($alpha >= self::ALPHA_WHITE_THRESHOLD) {
            return true;
        }

        $brightness = ($red * 0.299) + ($green * 0.587) + ($blue * 0.114);
        if ($brightness < self::NEAR_WHITE_BRIGHTNESS) {
            return false;
        }

        $maxChannel = max($red, $green, $blue);
        $minChannel = min($red, $green, $blue);
        $saturation = $maxChannel - $minChannel;

        return $saturation <= self::NEAR_WHITE_MAX_SATURATION;
    }

    /** @param list<string> $existingColors */
    private function isTooSimilarToExistingColors(string $hex, array $existingColors): bool
    {
        $candidate = $this->hexToRgb($hex);

        foreach ($existingColors as $existingHex) {
            $existing = $this->hexToRgb($existingHex);
            $distance = abs($candidate['r'] - $existing['r'])
                + abs($candidate['g'] - $existing['g'])
                + abs($candidate['b'] - $existing['b']);

            if ($distance < self::COLOR_MERGE_DISTANCE) {
                return true;
            }
        }

        return false;
    }

    private function colorDistance(int $r1, int $g1, int $b1, int $r2, int $g2, int $b2): int
    {
        return abs($r1 - $r2) + abs($g1 - $g2) + abs($b1 - $b2);
    }

    /** @return list<array{hex: string, r: int, g: int, b: int}> */
    private function pencilColorCandidates(): array
    {
        static $candidates = null;

        if ($candidates !== null) {
            return $candidates;
        }

        $candidates = [];

        foreach (array_values(Palette::pencilSet()) as $hex) {
            $rgb = $this->hexToRgb($hex);
            $candidates[] = [
                'hex' => strtolower($hex),
                'r' => $rgb['r'],
                'g' => $rgb['g'],
                'b' => $rgb['b'],
            ];
        }

        return $candidates;
    }

    private function isHexColor(string $value): bool
    {
        return preg_match('/^#([a-f0-9]{3}|[a-f0-9]{6})$/i', $value) === 1;
    }
}
