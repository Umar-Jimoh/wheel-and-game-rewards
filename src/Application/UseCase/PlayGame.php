<?php

declare(strict_types=1);

namespace App\Application\UseCase;

use App\Domain\Model\Game;
use App\Domain\Model\Parity;
use App\Application\Dto\RandomNumbersResult;
use App\Application\Port\Out\RandomNumberGenerator;
use App\Application\Port\In\PlayGameUseCase;
use App\Application\Port\Out\UserRepository;
use App\Application\Dto\PlayGameResult;
use App\Domain\Model\Point;

final class PlayGame implements PlayGameUseCase
{
    public function __construct(
        private readonly RandomNumberGenerator $randomNumberGenerator,
        private readonly UserRepository $userRepository
    ) {}

    public function play(int $userId, RandomNumbersResult $numbers, int $selectedPosition, Parity $parity): PlayGameResult
    {
        $user = $this->userRepository->findById($userId);

        if (! $user) {
            return PlayGameResult::failure('Invalid user id provided.');
        }

        $isWin = new Game($numbers->getNumbers())->evaluate($selectedPosition, $parity);

        if ($isWin) {
            $point = new Point(100);

            $user->addPoints($point);
            $this->userRepository->save($user);

            return PlayGameResult::success($point->getAmount(), $user->getBalance()->getAmount());
        } else {
            return PlayGameResult::failure('You lost the game. Try again!');
        }
    }

    public function generateRandomNumbers(): RandomNumbersResult
    {
        return $this->randomNumberGenerator->generate();
    }
}