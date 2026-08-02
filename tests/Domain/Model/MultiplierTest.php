<?php

declare(strict_types=1);

namespace App\Tests\Domain\Model;

use PHPUnit\Framework\TestCase;
use App\Domain\Model\Multiplier;
use App\Domain\Model\Point;

final class MultiplierTest extends TestCase
{
    public function testApplyToMultiplierNone(): void
    {
        $multipliedPoint = Multiplier::NONE->applyTo(new Point(1000));

        $this->assertObjectEquals(new Point(0), $multipliedPoint);
    }

    public function testApplyToMultiplierX2(): void
    {
        $multipliedPoint = Multiplier::X2->applyTo(new Point(1000));

        $this->assertObjectEquals(new Point(2000), $multipliedPoint);
    }

    public function testApplyToMultiplierX3(): void
    {
        $multipliedPoint = Multiplier::X3->applyTo(new Point(1000));

        $this->assertObjectEquals(new Point(3000), $multipliedPoint);
    }

    public function testApplyToMultiplierX4(): void
    {
        $multipliedPoint = Multiplier::X4->applyTo(new Point(1000));

        $this->assertObjectEquals(new Point(4000), $multipliedPoint);
    }

    public function testLabelReturnsZeroWhenMultiplierIsNone(): void
    {
        $multiplier = Multiplier::NONE->label();

        $this->assertEquals('0', $multiplier);
    }

    public function testLabelReturnsMultiplier(): void
    {
        $multiplier = Multiplier::X2->label();

        $this->assertEquals('2X', $multiplier);
    }
}