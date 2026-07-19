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

        if (count($exercises) > 1) {
            shuffle($exercises);
        }

        return array_values($exercises);
    }

    /** @param array{row: int, column: int, hex: string, paletteIndex: int, colorIndex: int} $pixel */
    private function buildExercise(array $pixel, int $index, QuestionMode $mode): Exercise
    {
        $x = (int) $pixel['row'];
        $y = (int) $pixel['column'];
        $a = $x * $y;
        $colorIndex = (int) ($pixel['colorIndex'] ?? $pixel['paletteIndex']);
        $solveFor = $index % 2 === 0 ? 'row' : 'column';
        $answer = $solveFor === 'row' ? $x : $y;

        $isMultiplication = match ($mode) {
            QuestionMode::Multiplication => true,
            QuestionMode::Division => false,
            QuestionMode::Mixed => $index % 2 === 0,
        };

        if ($isMultiplication) {
            $question = $solveFor === 'row'
                ? sprintf('□ × %d = %d', $y, $a)
                : sprintf('%d × □ = %d', $x, $a);

            return new Exercise(
                row: $pixel['row'],
                column: $pixel['column'],
                color: $pixel['hex'],
                colorIndex: $colorIndex,
                a: $a,
                x: $x,
                y: $y,
                type: 'multiplication',
                solveFor: $solveFor,
                question: $question,
                answer: $answer,
                display: sprintf('%d × %d = %d', $x, $y, $a),
            );
        }

        $question = $solveFor === 'row'
            ? sprintf('%d ÷ □ = %d', $a, $y)
            : sprintf('%d ÷ %d = □', $a, $x);

        return new Exercise(
            row: $pixel['row'],
            column: $pixel['column'],
            color: $pixel['hex'],
            colorIndex: $colorIndex,
            a: $a,
            x: $x,
            y: $y,
            type: 'division',
            solveFor: $solveFor,
            question: $question,
            answer: $answer,
            display: sprintf('%d ÷ %d = %d', $a, $x, $y),
        );
    }
}
