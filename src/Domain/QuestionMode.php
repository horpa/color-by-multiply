<?php

declare(strict_types=1);

namespace Domain;

enum QuestionMode: string
{
    case Mixed = 'mixed';
    case Multiplication = 'multiplication';
    case Division = 'division';

    public static function fromString(string $value): self
    {
        return match ($value) {
            'multiplication' => self::Multiplication,
            'division' => self::Division,
            default => self::Mixed,
        };
    }
}
