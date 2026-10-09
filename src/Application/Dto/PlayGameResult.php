<?php

declare(strict_types=1);

namespace App\Application\Dto;

final class PlayGameResult
{
    public function __construct(
        private readonly bool $win,
        private readonly ?int $earned,
        private readonly ?int $balance,
        private readonly ?string $errorMessage,
    ) {}

    public function isWin(): bool
    {
        return $this->win;
    }

    public static function success(int $earned, int $balance): self
    {
        return new self(true, $earned, $balance, null);
    }

    public static function failure(string $errorMessage): self
    {
        return new self(false, null, null, $errorMessage);
    }
}