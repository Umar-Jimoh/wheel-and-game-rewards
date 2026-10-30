<?php

declare(strict_types=1);

namespace App\UI\Http;

use App\Application\Port\In\StartGameUseCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class StartGameController
{
    public function __construct(
        private readonly StartGameUseCase $startGameUseCase,
    ) {
    }

    #[Route('/users/{userId}/games/start', methods: ['POST'])]
    public function __invoke(int $userId): JsonResponse
    {
        $result = $this->startGameUseCase->execute($userId);

        if (! $result->success) {
            return new JsonResponse(['error' => $result->errorMessage], JsonResponse::HTTP_NOT_FOUND);
        }

        return new JsonResponse(['numbers' => $result->numbers->getNumbers()], JsonResponse::HTTP_CREATED);
    }
}
