<?php

declare(strict_types=1);

namespace App\Application\UseCase;

use App\Application\Port\In\GetBalanceUseCase;
use App\Application\Port\Out\UserRepository;   
use App\Application\Dto\GetBalanceResult; 

final class GetBalance implements GetBalanceUseCase
{
    public function __construct(
        private readonly UserRepository $userRepository
    ) {}

    public function execute(int $userId): GetBalanceResult
    {
        $user = $this->userRepository->findById($userId);

        if (! $user) {
            return GetBalanceResult::failure('Invalid user id provided.');
        }

        return GetBalanceResult::success($user->getBalance()->getAmount());
    }
}