<?php

declare(strict_types=1);

namespace App\Tests\Domain\Model;

use PHPUnit\Framework\TestCase;
use App\Domain\Model\SpinCost;
use App\Domain\Model\Point;
use App\Domain\Exception\SpinCostException;

final class SpinCostTest extends TestCase
{
    public function testConstructorThrowsWhenCostIsNotAllowed(): void
    {
        $this->expectException(SpinCostException::class);
        new SpinCost(2);
    }

    public function testConstructorAcceptsAllowedCosts(): void
    {
        $spinCost = new SpinCost(500);

        $this->assertInstanceOf(SpinCost::class, $spinCost);
    }

    public function testToPointReturnsPointWhenCostIsAllowed(): void
    {
        $validCost = 500;

        $spinCost = new SpinCost($validCost);

        $points = new Point($validCost);

        $this->assertObjectEquals($points, $spinCost->toPoint());
    }
}