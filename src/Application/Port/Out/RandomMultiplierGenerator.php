<?php

declare(strict_types=1);

namespace App\Application\Port\Out;

use App\Domain\Model\Multiplier;

interface RandomMultiplierGenerator
{
    public function generate(): Multiplier;
}
