<?php

declare(strict_types=1);

namespace App\Domain\Model;

use InvalidArgumentException;

final class Game
{
    public function __construct(
        private readonly array $numbers,
    )
    {
        if (count($this->numbers) !== 5) {
            throw new InvalidArgumentException('Numbers array must contain exactly 5 elements.');
        }

        foreach ($this->numbers as $number) {
            if (!is_int($number) || $number < 0) {
                throw new InvalidArgumentException('Numbers array must contain only non-negative integers.');
            }
        }
    }

    public function evaluate(int $position, Parity $parity): bool
    {
        if ($position < 0 || $position >= count($this->numbers)) {
            throw new InvalidArgumentException('Position is out of bounds.');
        }

        $selectedNumber = $this->numbers[$position];

        return $parity->matches($selectedNumber);
    }
}
