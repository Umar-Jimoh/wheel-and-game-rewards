<?php

declare(strict_types=1);

namespace App\Application\Port\In;

use App\Application\Dto\GetBalanceResult;

interface GetBalanceUseCase
{
    public function execute(int $userId): GetBalanceResult;
}