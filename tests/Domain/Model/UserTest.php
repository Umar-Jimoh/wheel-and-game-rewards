<?php

declare(strict_types=1);

namespace App\Tests\Domain\Model;

use PHPunit\Framework\TestCase;
use App\Domain\Model\User;
use App\Domain\Model\Point;

final class UserTest extends TestCase
{
    public function testGetIdReturnsId(): void
    {
        $user = new User(1, 'test user', new Point(0));

        $this->assertSame(1, $user->getId());
    }

    public function testGetNameReturnsName(): void
    {
        $user = new User(1, 'test user', new Point(0));

        $this->assertSame('test user', $user->getName());
    }

    public function testGetBalanceReturnsBalance(): void
    {
        $point = new Point(0);

        $user = new User(1, 'test user', $point);

        $this->assertSame($point, $user->getBalance());
    }

    public function testAddPointsReturnsSumPoints(): void
    {
        $user = new User(1, 'test user', new Point(2));
        $point = new Point(2);

        $user->addPoints($point);

        $this->assertEquals(new Point(4), $user->getBalance());
    }

    public function testHasEnoughPointsReturnsTrueWhenPointIsEnough(): void
    {
        $user = new User(1, 'test user', new Point(10));

        $pointNeeded = new Point(5);

        $this->assertTrue($user->hasEnoughPoints($pointNeeded));
    }

    public function testHasEnoughPointsReturnsFalseWhenPointIsNotEnough(): void
    {
        $user = new User(1, 'test user', new Point(2));

        $pointNeeded = new Point(5);

        $this->assertFalse($user->hasEnoughPoints($pointNeeded));
    }

    public function testHasEnoughPointsReturnsTrueWhenPointIsExact(): void
    {
        $user = new User(1, 'test user', new Point(5));

        $pointNeeded = new Point(5);

        $this->assertTrue($user->hasEnoughPoints($pointNeeded));
    }

    public function testDeductPointsReturesCorrectBalance(): void
    {
        $user = new User(1, 'test user', new Point(5));

        $deductedPoint = new Point(2);

        $user->deductPoints($deductedPoint);

        $this->assertEquals(new Point(3), $user->getBalance());

    }
}