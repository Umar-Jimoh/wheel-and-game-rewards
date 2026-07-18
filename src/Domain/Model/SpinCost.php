<?php

declare(strict_types=1);

namespace App\Domain\Model;

use App\Domain\Exception\SpinCostException;

final class SpinCost
{
    private const array ALLOWED_SPIN_COSTS = [ 500, 1000, 2000 ];

    public function __construct(
        private readonly int $cost,
    ){
        if (!in_array($cost, self::ALLOWED_SPIN_COSTS)) {
            throw new SpinCostException();
        }
    }

    public function toPoint(): Point
    {
        return new Point($this->cost);
    }
}