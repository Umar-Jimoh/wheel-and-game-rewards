<?php 

declare(strict_types=1);

namespace App\Domain\Model;

use App\Domain\Exception\InsufficientPointsException;
use InvalidArgumentException;

final class Point {
    public function __construct(
        private readonly int $amount
    ){
        if ($amount < 0) {
            throw new InvalidArgumentException('Point amount must be non-negative');
        }
    }

    public function isGreaterThanOrEqualTo(Point $other): bool
    {
        $difference = $this->amount - $other->amount;

        return $difference < 0 ? false : true;
    }

    public function add(Point $other): Point 
    {
        $sum = $this->amount + $other->amount;
        return new Point($sum);
    }

    public function subtract(Point $other): Point 
    {
        $difference = $this->amount - $other->amount;

        if ($difference < 0) {
            throw new InsufficientPointsException();
        }

        return new Point($difference);
    }
}
