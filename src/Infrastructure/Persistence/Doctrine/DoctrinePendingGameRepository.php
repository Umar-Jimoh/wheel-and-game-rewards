<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine;

use App\Domain\Model\PendingGame;
use App\Application\Port\Out\PendingGameRepository;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrinePendingGameRepository implements PendingGameRepository
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {}

    public function save(PendingGame $pendingGame): void
    {
        $this->entityManager->persist($pendingGame);
        $this->entityManager->flush();
    }

    public function takeFor(int $userId): ?PendingGame
    {
        $pendingGame = $this->entityManager->getRepository(PendingGame::class)->findOneBy(['userId' => $userId]);

        if ($pendingGame === null) {
            return null;
        }

        $this->entityManager->remove($pendingGame);
        $this->entityManager->flush();

        return $pendingGame;
    }
}
