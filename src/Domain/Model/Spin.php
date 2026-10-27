<?php

declare(strict_types=1);

namespace App\Domain\Model;

final class Spin
{
    public function __construct(
        private readonly int $userId,
        private readonly Point $spinCost,
        private readonly Multiplier $multiplierRolled,
        private readonly Point $rewardedPoint,
    ){    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getSpinCost(): Point
    {
        return $this->spinCost;
    }

    public function getRewardedPoint(): Point
    {
        return $this->rewardedPoint;
    }

    public function getMultiplierRolled(): Multiplier
    {
        return $this->multiplierRolled;
    }
}