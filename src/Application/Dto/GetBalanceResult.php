<?php

declare(strict_types=1);

namespace App\Application\Dto;

class GetBalanceResult
{
    private function __construct(
        public readonly bool $success,
        public readonly ?int $balance,
        public readonly ?string $errorMessage,
    ){}

    public static function success(int $balance): self
    {
        return new self(true, $balance, null);
    }

    public static function failure(string $errorMessage): self
    {
        return new self(false, null, $errorMessage);
    }
}