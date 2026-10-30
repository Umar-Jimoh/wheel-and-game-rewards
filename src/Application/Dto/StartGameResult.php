<?php

declare(strict_types=1);

namespace App\Application\Dto;

use App\Application\Dto\RandomNumbersResult;

final class StartGameResult
{
    public function __construct(
        public readonly bool $success,
        public readonly ?RandomNumbersResult $numbers,
        public readonly ?string $errorMessage,
    ){}

    public static function success(RandomNumbersResult $numbers): self 
    {
        return new self(true, $numbers, null);
    }
    public static function failure(string $errorMessage): self
    {
        return new self(false, null, $errorMessage);
    }
}