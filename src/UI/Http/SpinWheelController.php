<?php

declare(strict_types=1);

namespace App\UI\Http;

use App\Application\Port\In\SpinWheelUseCase;
use Symfony\Component\Routing\Attribute\Route;
use App\Application\Dto\Request\SpinWheelInput;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;


final class SpinWheelController
{
    public function __construct(
        private readonly SpinWheelUseCase $spinWheelUseCase,
    ){}

    #[Route('/users/{userId}/wheel/spin', name: 'spin_wheel', methods: ['POST'])]
    public function __invoke(int $userId, #[MapRequestPayload] SpinWheelInput $input): JsonResponse
    {
        $result = $this->spinWheelUseCase->execute($userId, $input->spinCost);

        if (! $result->success) {
            return new JsonResponse(['error' => $result->errorMessage], JsonResponse::HTTP_NOT_FOUND);
        }

        return new JsonResponse(
            ['multiplier' => $result->multiplier, 'earned' => $result->earned, 'balance' => $result->balance],
            JsonResponse::HTTP_CREATED
        );
    }
}