<?php

declare(strict_types=1);

namespace App\Domain\Model;

final class PendingGame
{
    private ?int $id = null;

    public function __construct(
        private readonly int $userId,
        private readonly Game $game,
    ){}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getGame(): Game
    {
        return $this->game;
    }
}