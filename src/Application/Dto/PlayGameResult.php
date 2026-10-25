<?php

declare(strict_types=1);

namespace App\Application\Dto;

final class PlayGameResult
{
    private function __construct(
        public readonly bool $success,
        public readonly ?int $earned,
        public readonly ?int $balance,
        public readonly ?string $errorMessage,
    ) {}

    public static function success(int $earned, int $balance): self
    {
        return new self(true, $earned, $balance, null);
    }

    public static function failure(string $errorMessage): self
    {
        return new self(false, null, null, $errorMessage);
    }
}