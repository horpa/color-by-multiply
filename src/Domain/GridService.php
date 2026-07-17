<?php

declare(strict_types=1);

namespace Domain;

final class GridService
{
    /** @return list<list<array{paletteIndex: int}>> */
    public function emptyEditorGrid(): array
    {
        $grid = [];

        for ($row = 0; $row < Grid::SIZE; $row++) {
            for ($col = 0; $col < Grid::SIZE; $col++) {
                $grid[$row][$col] = ['paletteIndex' => 0];
            }
        }

        return $grid;
    }

    /**
     * @param list<list<array{paletteIndex?: int}|int>> $grid
     * @return list<list<array{paletteIndex: int}>>
     */
    public function toEditorFormat(array $grid): array
    {
        $normalized = [];

        for ($row = 0; $row < Grid::SIZE; $row++) {
            for ($col = 0; $col < Grid::SIZE; $col++) {
                $cell = $grid[$row][$col] ?? 0;
                $index = is_array($cell)
                    ? (int) ($cell['paletteIndex'] ?? 0)
                    : (int) $cell;
                $normalized[$row][$col] = ['paletteIndex' => $index];
            }
        }

        return $normalized;
    }

    /**
     * @param list<list<mixed>> $grid
     * @param list<string> $foregroundPalette
     */
    public function normalizeFromPost(array $grid, array $foregroundPalette): array
    {
        $foregroundPalette = (new PaletteService())->normalizeForegroundPalette($foregroundPalette);
        $maxIndex = count($foregroundPalette);
        $normalized = [];

        for ($row = 0; $row < Grid::SIZE; $row++) {
            for ($col = 0; $col < Grid::SIZE; $col++) {
                $value = isset($grid[$row][$col]) ? (int) $grid[$row][$col] : 0;
                $value = max(0, min($maxIndex, $value));
                $normalized[$row][$col] = ['paletteIndex' => $value];
            }
        }

        return $normalized;
    }

    /**
     * @param list<list<array{paletteIndex?: int}|int>> $grid
     * @param list<string> $foregroundPalette
     * @return array{
     *     grid: list<list<array{row: int, column: int, hex: string, paletteIndex: int, isBackground: bool, colorIndex: int}>>,
     *     foregroundPixels: list<array{row: int, column: int, hex: string, paletteIndex: int, colorIndex: int}>,
     *     backgroundIndex: int
     * }
     */
    public function process(array $grid, array $foregroundPalette): array
    {
        $paletteService = new PaletteService();
        $foregroundPalette = $paletteService->normalizeForegroundPalette($foregroundPalette);
        $normalizedGrid = [];
        $foregroundPixels = [];

        for ($row = 0; $row < Grid::SIZE; $row++) {
            for ($col = 0; $col < Grid::SIZE; $col++) {
                $index = isset($grid[$row][$col]['paletteIndex'])
                    ? (int) $grid[$row][$col]['paletteIndex']
                    : (int) ($grid[$row][$col] ?? 0);
                $index = max(0, min(count($foregroundPalette), $index));
                $hex = $paletteService->hexForGridIndex($index, $foregroundPalette);
                $isBackground = $index === 0;

                $normalizedGrid[$row][$col] = [
                    'row' => $row + 1,
                    'column' => $col + 1,
                    'hex' => $hex,
                    'paletteIndex' => $index,
                    'isBackground' => $isBackground,
                    'colorIndex' => $isBackground ? 0 : $index,
                ];

                if (!$isBackground) {
                    $foregroundPixels[] = [
                        'row' => $row + 1,
                        'column' => $col + 1,
                        'hex' => $hex,
                        'paletteIndex' => $index,
                        'colorIndex' => $index,
                    ];
                }
            }
        }

        return [
            'grid' => $normalizedGrid,
            'foregroundPixels' => $foregroundPixels,
            'backgroundIndex' => 0,
        ];
    }
}
