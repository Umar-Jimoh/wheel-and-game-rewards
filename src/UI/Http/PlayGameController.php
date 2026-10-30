<?php

declare(strict_types=1);

namespace App\UI\Http;

use Symfony\Component\Routing\Attribute\Route;
use App\Application\Port\In\PlayGameUseCase;
use App\Application\Dto\Request\PlayGameInput;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;

final class PlayGameController
{
    public function __construct(
        private readonly PlayGameUseCase $playGameUseCase
        ){}
        
    #[Route('/users/{userId}/games/play', name: 'play_game', methods: ['POST'])]
    public function __invoke(int $userId, #[MapRequestPayload] PlayGameInput $input): JsonResponse
    {
        $result = $this->playGameUseCase->play($userId, $input->position, $input->parity);

        if (! $result->success) {
            return new JsonResponse(['error' => $result->errorMessage], JsonResponse::HTTP_NOT_FOUND);
        }

        $successResponse = ['won' => $result->won, 'earned' => $result->earned, 'balance' => $result->balance];

        return new JsonResponse($successResponse, JsonResponse::HTTP_OK);
    }
}