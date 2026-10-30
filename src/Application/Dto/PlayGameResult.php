<?php

declare(strict_types=1);

namespace App\Application\Dto;

final class PlayGameResult
{
    private function __construct(
        public readonly bool $success,
        public readonly bool $won,
        public readonly ?int $earned,
        public readonly ?int $balance,
        public readonly ?string $errorMessage,
    ) {}

    public static function success(bool $won, int $earned, int $balance): self
    {
        return new self(true, $won, $earned, $balance, null);
    }

    public static function failure(string $errorMessage): self
    {
        return new self(false, false, null, null, $errorMessage);
    }
}