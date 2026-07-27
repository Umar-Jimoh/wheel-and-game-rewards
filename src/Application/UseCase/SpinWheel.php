<?php

declare(strict_types=1);

namespace App\Application\UseCase;

use App\Domain\Model\SpinCost;
use App\Domain\Exception\InsufficientPointsException;
use App\Domain\Exception\SpinCostException;
use App\Application\Port\In\SpinWheelUseCase;
use App\Application\Port\Out\UserRepository;
use App\Application\Port\Out\RandomMultiplierGenerator;
use App\Application\Dto\SpinWheelResult;

final class SpinWheel implements SpinWheelUseCase
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly RandomMultiplierGenerator $randomMultiplierGenerator,
    ){}

    public function execute(int $userId, int $spinCost): SpinWheelResult
    {
        try {
            $user = $this->userRepository->findById($userId);

            if (! $user) {
                return SpinWheelResult::failure('Invalid user id provided.');
            }

            $spinCost = new SpinCost($spinCost);

            $point = $spinCost->toPoint();

            if (! $user->hasEnoughPoints($point)) {
                return SpinWheelResult::failure(new InsufficientPointsException()->getMessage());
            }

            $user->deductPoints($point);

            $multiplier = $this->randomMultiplierGenerator->generate();

            $reward = $multiplier->applyTo($point);

            $user->addPoints($reward);

            $this->userRepository->save($user);

            return SpinWheelResult::success($multiplier->name, $reward->getAmount(), $user->getBalance()->getAmount());
        } catch (SpinCostException $e) {
            return SpinWheelResult::failure($e->getMessage());
        }
    }
}