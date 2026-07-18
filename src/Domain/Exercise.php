<?php

declare(strict_types=1);

namespace Domain;

final class Exercise
{
    public function __construct(
        public readonly int $row,
        public readonly int $column,
        public readonly string $color,
        public readonly int $colorIndex,
        public readonly int $a,
        public readonly int $x,
        public readonly int $y,
        public readonly string $type,
        public readonly string $solveFor,
        public readonly string $question,
        public readonly int $answer,
        public readonly string $display,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'row' => $this->row,
            'column' => $this->column,
            'color' => $this->color,
            'colorIndex' => $this->colorIndex,
            'a' => $this->a,
            'x' => $this->x,
            'y' => $this->y,
            'formula' => sprintf('%d × %d = %d', $this->x, $this->y, $this->a),
            'computedValue' => $this->a,
            'matchesFormula' => true,
            'type' => $this->type,
            'solveFor' => $this->solveFor,
            'question' => $this->question,
            'answer' => $this->answer,
            'display' => $this->display,
        ];
    }
}
