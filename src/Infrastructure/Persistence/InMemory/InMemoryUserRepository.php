<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\InMemory;

use App\Application\Port\Out\UserRepository;
use App\Domain\Model\User;

class InMemoryUserRepository implements UserRepository
{
    /** @var array<int, User> */ 
    private array $users = [];

    public function findById(int $id): ?User
    {
        return $this->users[$id] ?? null;
    }

    public function save(User $user): void
    {
        $this->users[$user->getId()] = $user;
    }
}