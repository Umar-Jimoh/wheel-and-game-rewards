<?php

declare(strict_types=1);

namespace App\Application\Port\In;

use App\Domain\Model\Parity;
use App\Application\Dto\PlayGameResult;
use App\Application\Dto\RandomNumbersResult;

interface PlayGameUseCase
{
    public function play(int $userId, RandomNumbersResult $numbers, int $position, Parity $parity): PlayGameResult;

    public function generateRandomNumbers(): RandomNumbersResult;
}