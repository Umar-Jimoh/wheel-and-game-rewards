<?php

declare(strict_types=1);

namespace App\Application\UseCase;

use App\Application\Port\In\GetBalanceUseCase;
use App\Application\Port\Out\UserRepository;   
use App\Application\Dto\GetbalanceResult; 

final class GetBalance implements GetBalanceUseCase
{
    public function __construct(
        private readonly UserRepository $userRepository
    ) {}

    public function execute(int $userId): GetbalanceResult
    {
        $user = $this->userRepository->findById($userId);

        if (! $user) {
            return GetbalanceResult::failure('Invalid user id provided.');
        }

        return GetbalanceResult::success($user->getBalance()->getAmount());
    }
}