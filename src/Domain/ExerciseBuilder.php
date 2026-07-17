<?php

declare(strict_types=1);

namespace Domain;

final class ExerciseBuilder
{
    /**
     * @param list<array{row: int, column: int, hex: string, paletteIndex: int}> $foregroundPixels
     * @return list<array<string, mixed>>
     */
    public function build(array $foregroundPixels, string $questionMode = 'mixed'): array
    {
        $mode = QuestionMode::fromString($questionMode);
        $exercises = [];

        foreach ($foregroundPixels as $index => $pixel) {
            $exercises[] = $this->buildExercise($pixel, $index, $mode)->toArray();
        }

        return $exercises;
    }

    /** @param array{row: int, column: int, hex: string, paletteIndex: int, colorIndex: int} $pixel */
    private function buildExercise(array $pixel, int $index, QuestionMode $mode): Exercise
    {
        $x = (int) $pixel['row'];
        $y = (int) $pixel['column'];
        $a = $x * $y;
        $colorIndex = (int) ($pixel['colorIndex'] ?? $pixel['paletteIndex']);

        $isMultiplication = match ($mode) {
            QuestionMode::Multiplication => true,
            QuestionMode::Division => false,
            QuestionMode::Mixed => $index % 2 === 0,
        };

        if ($isMultiplication) {
            return new Exercise(
                row: $pixel['row'],
                column: $pixel['column'],
                color: $pixel['hex'],
                colorIndex: $colorIndex,
                a: $a,
                x: $x,
                y: $y,
                type: 'multiplication',
                question: sprintf('%d × <span class="var x">□</span> = %d', $x, $a),
                answer: $y,
                display: sprintf('%d × %d = %d', $x, $y, $a),
            );
        }

        return new Exercise(
            row: $pixel['row'],
            column: $pixel['column'],
            color: $pixel['hex'],
            colorIndex: $colorIndex,
            a: $a,
            x: $x,
            y: $y,
            type: 'division',
            question: sprintf('%d ÷ <span class="var x">□</span> = %d', $a, $y),
            answer: $y,
            display: sprintf('%d ÷ %d = %d', $a, $x, $y),
        );
    }
}
