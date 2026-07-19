<?php

declare(strict_types=1);

namespace App\Domain\Model;

enum Multiplier: int
{
    case NONE = 0;
    case X2 = 2;
    case X3 = 3;
    case X4 = 4;

    public function applyTo(Point $cost): Point
    {
        return $cost->multiplyBy($this->value);
    }
}
