<?php

declare(strict_types=1);

namespace App\Application\Port\Out;

use App\Domain\Model\User;

interface UserRepository
{
    public function findById(int $id): ?User;

    public function save(User $user): void;
}