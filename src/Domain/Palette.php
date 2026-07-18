<?php

declare(strict_types=1);

namespace Domain;

final class Palette
{
    public const BACKGROUND_HEX = '#ffffff';
    public const MAX_FOREGROUND_COLORS = 7;
    public const MIN_FOREGROUND_COLORS = 1;
    private const FALLBACK_FOREGROUND = '#000000';

    /** @return list<string> */
    public static function defaultForeground(): array
    {
        return [self::FALLBACK_FOREGROUND];
    }

    /** @return list<string> */
    public static function blankCanvasForeground(): array
    {
        return [
            '#411947',
            '#8a139c',
            '#9c7813',
            '#139c74',
            '#20332d',
        ];
    }
}
