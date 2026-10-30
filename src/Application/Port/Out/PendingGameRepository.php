<?php

declare(strict_types=1);

namespace App\Application\Port\Out;

use App\Domain\Model\PendingGame;

interface PendingGameRepository
{
    public function save(PendingGame $pendingGame): void;

    public function takeFor(int $userId): ?PendingGame;
}