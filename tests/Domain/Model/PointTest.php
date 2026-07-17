<?php

declare(strict_types=1);

namespace App\Tests\Domain\Model;

use PHPUnit\Framework\TestCase;
use App\Domain\Model\Point;
use InvalidArgumentException;
use App\Domain\Exception\InsufficientPointsException;

final class PointTest extends TestCase
{
    public function testConstructorThrowsWhenAmountIsNegative(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Point(-2);
    }

    public function testIsGreaterThanOrEqualToReturnTrueWhenGreater(): void
    {
        $point = new Point(5);
        $point2 = new Point(2);
        $this->assertTrue($point->isGreaterThanOrEqualTo($point2));
    }

    public function testIsGreaterThwnOrEqualToReturnFalseWhenLess(): void
    {
        $point = new Point(5);
        $point2 = new Point(2);

        $this->assertFalse($point2->isGreaterThanOrEqualTo($point));
    }

    public function testIsGreaterThanOrEqualToReturnTrueWhenEqual(): void
    {
        $point = new Point(2);
        $point2 = new Point(2);

        $this->assertTrue($point2->isGreaterThanOrEqualTo($point));
    }

    public function testAddReturnSumOfBothAmounts(): void
    {
        $point = new Point(2);
        $point2 = new Point(3);

        $this->assertEquals(new Point(5), $point->add($point2));
    }

    public function testSubtractReturnsCorrectDifferenceWhenSufficient(): void
    {
        $point = new Point(2);
        $point2 = new Point(3);
        
        $this->assertEquals(new Point(1), $point2->subtract($point));
    }

        public function testSubtractThrowsWhenInsufficient(): void
    {
        $point = new Point(2);
        $point2 = new Point(3);

        $this->expectException(InsufficientPointsException::class);
        $point->subtract($point2);
    }

    public function testSubtractReturnsZeroWhenAmountsAreEqual(): void
    {
        $point = new Point(3);
        $point2 = new Point(3);

        $this->assertEquals(new Point(0), $point->subtract($point2));
    }
}