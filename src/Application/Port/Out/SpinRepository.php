<?php

declare(strict_types=1);

namespace App\Application\Port\Out;

use App\Domain\Model\Spin;

interface SpinRepository
{   
    public function save(Spin $spin): void;

    public function getAll(): array;
}