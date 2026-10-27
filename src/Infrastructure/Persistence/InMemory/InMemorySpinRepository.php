<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\InMemory;

use App\Application\Port\Out\SpinRepository;
use App\Domain\Model\Spin;

class InMemorySpinRepository implements SpinRepository
{
    private array $spins = [];

    public function save(Spin $spin): void
    {
        $this->spins[] = $spin;
    }

    public function getAll(): array
    {
        return $this->spins;
    }
}