<?php

declare(strict_types=1);

namespace App\UI\Http;

use App\Application\Port\In\GetBalanceUseCase;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('/users/{userId}/balance', name: 'get_balance', methods:['GET'])]
final class GetBalanceController
{
    public function __construct(
        private readonly GetBalanceUseCase $getBalanceUseCase
    ){}

    public function __invoke(int $userId): JsonResponse
    {
        $result = $this->getBalanceUseCase->execute($userId);

        if (! $result->success) {
            return new JsonResponse(['error' => $result->errorMessage], JsonResponse::HTTP_NOT_FOUND);
        }

        return new JsonResponse(['balance' => $result->balance], JsonResponse::HTTP_OK);
    }
}