<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine;

use App\Domain\Model\Spin;
use App\Application\Port\Out\SpinRepository;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineSpinRepository implements SpinRepository
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {}

    public function save(Spin $spin): void
    {
        $this->entityManager->persist($spin);
        $this->entityManager->flush();
    }

    public function getAll(): array
    {
        return $this->entityManager->getRepository(Spin::class)->findAll();
    }
}
