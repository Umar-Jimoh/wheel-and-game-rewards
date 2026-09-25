<?php

declare(strict_types=1);

namespace App\Domain\Model;

enum Parity: string
{
    case EVEN = 'even';
    case ODD = 'odd';

    public function label(): string
    {
        return match ($this) {
            self::EVEN => 'Even',
            self::ODD => 'Odd',
        };
    }

    public function matches(int $number): bool
    {
        return match ($this) {
            self::EVEN => $number % 2 === 0,
            self::ODD => $number % 2 !== 0,
        };
    }
}