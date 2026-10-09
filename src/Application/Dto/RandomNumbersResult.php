<?php

declare(strict_types=1);

namespace App\Application\Dto;

final class RandomNumbersResult
{
    public function __construct(
        private readonly array $numbers,
    ){}

    public function getNumbers(): array
    {
        return $this->numbers;
    }
}