<?php

declare(strict_types=1);

namespace App\Application\Port\In;

use App\Domain\Model\Parity;
use App\Application\Dto\PlayGameResult;

interface PlayGameUseCase
{
    public function play(int $userId, int $position, Parity $parity): PlayGameResult;
}