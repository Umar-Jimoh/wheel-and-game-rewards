<?php

declare(strict_types=1);

namespace App\Domain\Model;

final class PendingGame
{
    public function __construct(
        private readonly int $userId,
        private readonly Game $game,
    ){}

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getGame(): Game
    {
        return $this->game;
    }
}