<?php

declare(strict_types=1);

namespace App\Application\Port\Out;

use App\Application\Dto\RandomNumbersResult;

interface RandomNumberGenerator
{
    public function generate(): RandomNumbersResult;
}