<?php

declare(strict_types=1);

namespace App\Infrastructure\Random;

use App\Domain\Model\Multiplier;
use App\Application\Port\Out\RandomMultiplierGenerator as RandomMultiplierGeneratorInterface;

final class RandomMultiplierGenerator implements RandomMultiplierGeneratorInterface
{
    public function generate(): Multiplier
    {
        $cases = Multiplier::cases();
         return $cases[random_int(0, count($cases) - 1)];
    }
}