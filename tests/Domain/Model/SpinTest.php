<?php

declare(strict_types=1);

namespace App\Tests\Domain\Model;

use \PHPUnit\Framework\TestCase;
use App\Domain\Model\Spin;
use App\Domain\Model\Point;
use App\Domain\Model\Multiplier;

class SpinTest extends TestCase
{
    public function testGetUserIdReturnsCorrectId(): void
    {
        $userId = 1;
        $spinCost = new Point(100);
        $multiplier = Multiplier::X2;
        $rewardedPoint = new Point(200);

        $spin = new Spin($userId, $spinCost, $multiplier, $rewardedPoint);
        $this->assertSame($userId, $spin->getUserId());
    }

    public function testGetSpinCostReturnsCorrectPoint(): void
    {
        $userId = 1;
        $spinCost = new Point(100);
        $multiplier = Multiplier::X2;
        $rewardedPoint = new Point(200);

        $spin = new Spin($userId, $spinCost, $multiplier, $rewardedPoint);
        $this->assertEquals($spinCost, $spin->getSpinCost());
    }

    public function testGetMultiplierRolledReturnsCorrectMultiplier(): void
    {
        $userId = 1;
        $spinCost = new Point(100);
        $multiplier = Multiplier::X2;
        $rewardedPoint = new Point(200);

        $spin = new Spin($userId, $spinCost, $multiplier, $rewardedPoint);
        $this->assertSame($multiplier, $spin->getMultiplierRolled());
    }

    public function testGetRewardedPointReturnsCorrectPoint(): void
    {
        $userId = 1;
        $spinCost = new Point(100);
        $multiplier = Multiplier::X2;
        $rewardedPoint = new Point(200);

        $spin = new Spin($userId, $spinCost, $multiplier, $rewardedPoint);
        $this->assertEquals($rewardedPoint, $spin->getRewardedPoint());
    }
}