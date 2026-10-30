<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\InMemory;

use App\Application\Port\Out\PendingGameRepository;
use App\Domain\Model\PendingGame;

class InMemoryPendingGameRepository implements PendingGameRepository
{
    private array $pendingGames = [];

    public function save(PendingGame $pendingGame): void
    {
        $this->pendingGames[] = $pendingGame;
    }

    public function takeFor(int $userId): ?PendingGame
    {
        foreach ($this->pendingGames as $id => $pendingGame) {
            if ($pendingGame->getUserId() === $userId) {
                unset($this->pendingGames[$id]);
                return $pendingGame;
            }
        }

        return null;
    }
}