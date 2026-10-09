<?php

declare(strict_types=1);

namespace App\Infrastructure\Random;

use App\Application\Dto\RandomNumbersResult;
use App\Application\Port\Out\RandomNumberGenerator as RandomNumberGeneratorInterface;

final class RandomNumberGenerator implements RandomNumberGeneratorInterface
{
    public function generate(): RandomNumbersResult
    {
        $numbers = [];
        for ($i = 0; $i < 5; $i++) {
            $numbers[] = random_int(0, 9);
        }

        return new RandomNumbersResult($numbers);
    }
}