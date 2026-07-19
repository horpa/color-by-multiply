<?php

declare(strict_types=1);

namespace Infrastructure;

use Domain\Grid;
use Domain\Palette;
use Domain\PaletteService;
use RuntimeException;

final class GdImageProcessor
{
    /** Minimum share of non-background pixels in a block to keep a colored cell. */
    private const CELL_FOREGROUND_COVERAGE = 0.35;

    public function __construct(
        private readonly PaletteService $paletteService = new PaletteService(),
    ) {
    }

    public function prepareImage(string $sourcePath, array $options, string $outputPath, ?string $mimeType = null): string
    {
        if (!is_file($sourcePath)) {
            throw new RuntimeException('The source image could not be found.');
        }

        $image = $this->loadImage($sourcePath, $mimeType);
        if ($image === false) {
            throw new RuntimeException('The uploaded file is not a valid image.');
        }

        $image = $this->flattenAlphaToWhite($image);
        $cropped = $this->centerCropSquare($image);
        imagedestroy($image);

        $resized = $this->resizeAreaAverage($cropped, Grid::SIZE, Grid::SIZE);
        imagedestroy($cropped);

        if (!empty($options['boost_contrast'])) {
            $resized = $this->boostContrast($resized);
        }

        if (!empty($options['sharpen_edges'])) {
            $resized = $this->sharpenImage($resized);
        }

        $directory = dirname($outputPath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        imagepng($resized, $outputPath);
        imagedestroy($resized);

        return $outputPath;
    }

    /** @return list<string> */
    public function resolvePalette(
        string $path,
        ?string $mimeType = null,
        ?array $palette = null,
        bool $mapToPencilSet = false,
    ): array {
        if (is_array($palette) && $palette !== []) {
            return $this->paletteService->normalizeForegroundPalette($palette);
        }

        $gridImage = $this->loadGridImage($path, $mimeType);
        if ($gridImage === false) {
            return $this->paletteService->getDefaultPaletteHex();
        }

        $resolvedPalette = $this->paletteService->extractFromImage(
            $gridImage,
            Palette::MAX_FOREGROUND_COLORS,
            $mapToPencilSet,
        );
        imagedestroy($gridImage);

        return $this->paletteService->normalizeForegroundPalette($resolvedPalette);
    }

    /**
     * @param list<string> $foregroundPalette
     * @return list<list<array{paletteIndex: int}>>
     */
    public function buildGridFromImage(
        string $path,
        ?string $mimeType,
        array $foregroundPalette,
        bool $mapToPencilSet = false,
    ): array {
        $foregroundPalette = $this->paletteService->normalizeForegroundPalette($foregroundPalette);
        $gridImage = $this->createPreparedGridImage($path, $mimeType);

        $grid = [];

        for ($row = 0; $row < Grid::SIZE; $row++) {
            for ($col = 0; $col < Grid::SIZE; $col++) {
                $rgba = $this->paletteService->readPixel($gridImage, $col, $row);
                $paletteIndex = $mapToPencilSet
                    ? $this->paletteService->quantizeToPencilGridIndex(
                        $rgba['red'],
                        $rgba['green'],
                        $rgba['blue'],
                        $rgba['alpha'],
                        $foregroundPalette,
                    )
                    : $this->paletteService->quantizeToGridIndex(
                        $rgba['red'],
                        $rgba['green'],
                        $rgba['blue'],
                        $rgba['alpha'],
                        $foregroundPalette,
                    );

                $grid[$row][$col] = ['paletteIndex' => $paletteIndex];
            }
        }

        imagedestroy($gridImage);

        return $grid;
    }

    /**
     * @param list<list<array{hex: string}>> $normalizedGrid
     */
    public function renderPreview(array $normalizedGrid): string
    {
        $scale = 32;
        $previewWidth = ($scale * Grid::SIZE) + ($scale * 2);
        $previewHeight = ($scale * Grid::SIZE) + ($scale * 2);
        $previewImage = imagecreatetruecolor($previewWidth, $previewHeight);
        imagealphablending($previewImage, false);
        imagesavealpha($previewImage, true);
        $transparent = imagecolorallocatealpha($previewImage, 255, 255, 255, 127);
        imagefill($previewImage, 0, 0, $transparent);

        $rowLabelColor = imagecolorallocate($previewImage, 11, 95, 255);
        $columnLabelColor = imagecolorallocate($previewImage, 25, 135, 84);
        $gridLineColor = imagecolorallocate($previewImage, 80, 80, 80);

        foreach ($normalizedGrid as $row => $cells) {
            foreach ($cells as $col => $cell) {
                $rgb = $this->paletteService->hexToRgb($cell['hex']);
                $pixelColor = imagecolorallocate($previewImage, $rgb['r'], $rgb['g'], $rgb['b']);
                imagefilledrectangle(
                    $previewImage,
                    ($col + 1) * $scale,
                    ($row + 1) * $scale,
                    ($col + 2) * $scale - 1,
                    ($row + 2) * $scale - 1,
                    $pixelColor
                );
            }
        }

        for ($line = 0; $line <= Grid::SIZE; $line++) {
            $x = $line * $scale + $scale;
            imageline($previewImage, $x, $scale, $x, $previewHeight - $scale, $gridLineColor);
            $y = $line * $scale + $scale;
            imageline($previewImage, $scale, $y, $previewWidth - $scale, $y, $gridLineColor);
        }

        for ($row = 0; $row < Grid::SIZE; $row++) {
            imagestring($previewImage, 5, 8, (($row + 1) * $scale) + 6, (string) ($row + 1), $rowLabelColor);
        }

        for ($col = 0; $col < Grid::SIZE; $col++) {
            imagestring($previewImage, 5, (($col + 1) * $scale) + 6, 8, (string) ($col + 1), $columnLabelColor);
        }

        ob_start();
        imagepng($previewImage);
        $previewImageData = ob_get_clean();
        imagedestroy($previewImage);

        return $previewImageData;
    }

    /**
     * Load an image already prepared as Grid::SIZE×Grid::SIZE, or downscale as a fallback.
     *
     * @return resource|false
     */
    private function loadGridImage(string $path, ?string $mimeType)
    {
        $image = $this->loadImage($path, $mimeType);
        if ($image === false) {
            return false;
        }

        $width = imagesx($image);
        $height = imagesy($image);

        if ($width === Grid::SIZE && $height === Grid::SIZE) {
            return $image;
        }

        $image = $this->flattenAlphaToWhite($image);
        $cropped = $this->centerCropSquare($image);
        imagedestroy($image);

        $resized = $this->resizeAreaAverage($cropped, Grid::SIZE, Grid::SIZE);
        imagedestroy($cropped);

        return $resized;
    }

    /** @return resource */
    private function createPreparedGridImage(string $path, ?string $mimeType)
    {
        $gridImage = $this->loadGridImage($path, $mimeType);
        if ($gridImage === false) {
            throw new RuntimeException('The uploaded file is not a valid image.');
        }

        return $gridImage;
    }

    /** @return resource|false */
    public function loadImage(string $path, ?string $mimeType = null)
    {
        if ($mimeType === null) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $path);
            finfo_close($finfo);
        }

        return match ($mimeType) {
            'image/jpeg', 'image/jpg' => imagecreatefromjpeg($path),
            'image/png' => imagecreatefrompng($path),
            'image/webp' => imagecreatefromwebp($path),
            'image/gif' => imagecreatefromgif($path),
            default => false,
        };
    }

    /** @param resource $image @return resource */
    private function flattenAlphaToWhite($image)
    {
        imagealphablending($image, false);
        imagesavealpha($image, true);

        $width = imagesx($image);
        $height = imagesy($image);
        $flattened = imagecreatetruecolor($width, $height);
        $white = imagecolorallocate($flattened, 255, 255, 255);
        imagefill($flattened, 0, 0, $white);

        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                $rgba = $this->paletteService->readPixel($image, $x, $y);
                if ($this->paletteService->isBackgroundPixel($rgba['red'], $rgba['green'], $rgba['blue'], $rgba['alpha'])) {
                    continue;
                }

                $color = imagecolorallocate($flattened, $rgba['red'], $rgba['green'], $rgba['blue']);
                imagesetpixel($flattened, $x, $y, $color);
            }
        }

        imagedestroy($image);

        return $flattened;
    }

    /** @param resource $image @return resource */
    private function centerCropSquare($image)
    {
        $width = imagesx($image);
        $height = imagesy($image);
        $side = min($width, $height);
        $offsetX = (int) floor(($width - $side) / 2);
        $offsetY = (int) floor(($height - $side) / 2);

        $cropped = imagecreatetruecolor($side, $side);
        $white = imagecolorallocate($cropped, 255, 255, 255);
        imagefill($cropped, 0, 0, $white);
        imagecopy($cropped, $image, 0, 0, $offsetX, $offsetY, $side, $side);

        return $cropped;
    }

    /**
     * Downscale by averaging each destination cell over its source rectangle.
     * Cells that are mostly background stay white; otherwise use the mean of
     * non-background pixels.
     *
     * @param resource $image
     * @return resource
     */
    private function resizeAreaAverage($image, int $targetWidth, int $targetHeight)
    {
        $sourceWidth = imagesx($image);
        $sourceHeight = imagesy($image);
        $resized = imagecreatetruecolor($targetWidth, $targetHeight);
        $white = imagecolorallocate($resized, 255, 255, 255);
        imagefill($resized, 0, 0, $white);

        for ($targetX = 0; $targetX < $targetWidth; $targetX++) {
            for ($targetY = 0; $targetY < $targetHeight; $targetY++) {
                $x0 = (int) floor($targetX * $sourceWidth / $targetWidth);
                $y0 = (int) floor($targetY * $sourceHeight / $targetHeight);
                $x1 = (int) floor(($targetX + 1) * $sourceWidth / $targetWidth);
                $y1 = (int) floor(($targetY + 1) * $sourceHeight / $targetHeight);
                $x1 = max($x1, $x0 + 1);
                $y1 = max($y1, $y0 + 1);

                $sumR = 0;
                $sumG = 0;
                $sumB = 0;
                $foregroundCount = 0;
                $totalCount = 0;

                for ($x = $x0; $x < $x1; $x++) {
                    for ($y = $y0; $y < $y1; $y++) {
                        $rgba = $this->paletteService->readPixel($image, $x, $y);
                        $totalCount++;

                        if ($this->paletteService->isBackgroundPixel(
                            $rgba['red'],
                            $rgba['green'],
                            $rgba['blue'],
                            $rgba['alpha'],
                        )) {
                            continue;
                        }

                        $sumR += $rgba['red'];
                        $sumG += $rgba['green'];
                        $sumB += $rgba['blue'];
                        $foregroundCount++;
                    }
                }

                if ($totalCount === 0 || $foregroundCount / $totalCount < self::CELL_FOREGROUND_COVERAGE) {
                    continue;
                }

                $red = (int) round($sumR / $foregroundCount);
                $green = (int) round($sumG / $foregroundCount);
                $blue = (int) round($sumB / $foregroundCount);
                $color = imagecolorallocate($resized, $red, $green, $blue);
                imagesetpixel($resized, $targetX, $targetY, $color);
            }
        }

        return $resized;
    }

    /** @param resource $image @return resource */
    private function boostContrast($image)
    {
        $width = imagesx($image);
        $height = imagesy($image);
        $min = 255;
        $max = 0;
        $hasForeground = false;

        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                $rgba = $this->paletteService->readPixel($image, $x, $y);
                if ($this->paletteService->isBackgroundPixel($rgba['red'], $rgba['green'], $rgba['blue'], $rgba['alpha'])) {
                    continue;
                }

                $hasForeground = true;
                $luminance = (int) ((0.299 * $rgba['red']) + (0.587 * $rgba['green']) + (0.114 * $rgba['blue']));
                $min = min($min, $luminance);
                $max = max($max, $luminance);
            }
        }

        if (!$hasForeground || $max <= $min) {
            return $image;
        }

        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                $rgba = $this->paletteService->readPixel($image, $x, $y);
                if ($this->paletteService->isBackgroundPixel($rgba['red'], $rgba['green'], $rgba['blue'], $rgba['alpha'])) {
                    continue;
                }

                $red = $this->stretchChannel($rgba['red'], $min, $max);
                $green = $this->stretchChannel($rgba['green'], $min, $max);
                $blue = $this->stretchChannel($rgba['blue'], $min, $max);
                $color = imagecolorallocate($image, $red, $green, $blue);
                imagesetpixel($image, $x, $y, $color);
            }
        }

        return $image;
    }

    /** @param resource $image @return resource */
    private function sharpenImage($image)
    {
        $matrix = [
            [0, -1, 0],
            [-1, 5, -1],
            [0, -1, 0],
        ];
        $divisor = 1;
        $offset = 0;

        if (function_exists('imageconvolution')) {
            imageconvolution($image, $matrix, $divisor, $offset);
        }

        return $image;
    }

    private function stretchChannel(int $channel, int $min, int $max): int
    {
        $stretched = (int) round((($channel - $min) / ($max - $min)) * 255);

        return max(0, min(255, $stretched));
    }
}
