<?php

declare(strict_types=1);

namespace App\Application\Port\In;

use App\Application\Dto\StartGameResult;

interface StartGameUseCase
{
    public function execute(int $userId): StartGameResult;
}