<?php

declare(strict_types=1);

namespace App\Application\Port\In;

use App\Application\Dto\SpinWheelResult;

interface SpinWheelUseCase
{
    public function execute(int $userId, int $spinCost): SpinWheelResult;
}