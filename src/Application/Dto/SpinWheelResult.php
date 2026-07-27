<?php

declare(strict_types=1);

namespace App\Application\Dto;

final class SpinWheelResult
{
    private function __construct(
        public readonly bool $success,
        public readonly ?string $multiplier,
        public readonly ?int $earned,
        public readonly ?int $balance,
        public readonly ?string $errorMessage,
    ){}

    public static function success(string $multiplier, int $earned, int $balance): self
    {
        return new self(true, $multiplier, $earned, $balance, null);
    }

    public static function failure(string $errorMessage): self
    {
        return new self(false, null, null, null, $errorMessage);
    }
}