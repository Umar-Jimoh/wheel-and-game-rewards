<?php

declare(strict_types=1);

namespace App\Application\UseCase;

use App\Domain\Model\Parity;
use App\Application\Port\In\PlayGameUseCase;
use App\Application\Port\Out\UserRepository;
use App\Application\Port\Out\PendingGameRepository;
use App\Application\Dto\PlayGameResult;
use App\Domain\Model\Point;

final class PlayGame implements PlayGameUseCase
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly PendingGameRepository $pendingGameRepository,
    ) {}

    public function play(int $userId, int $selectedPosition, Parity $parity): PlayGameResult
    {
        $user = $this->userRepository->findById($userId);

        if (! $user) {
            return PlayGameResult::failure('Invalid user id provided.');
        }

        $pendingGame = $this->pendingGameRepository->takeFor($userId);

        if (!$pendingGame) {
            return PlayGameResult::failure('No pending game found for the user.');
        }

        $game = $pendingGame->getGame();

        if ($selectedPosition < 0 || $selectedPosition >= count($game->getNumbers())) {
            $this->pendingGameRepository->save($pendingGame);

            return PlayGameResult::failure('Invalid selected position.');
        }

        $isWin = $game->evaluate($selectedPosition, $parity);

        if ($isWin) {
            $point = new Point(100);

            $user->addPoints($point);
            $this->userRepository->save($user);

            return PlayGameResult::success(true, $point->getAmount(), $user->getBalance()->getAmount());
        } else {
            return PlayGameResult::success(false, 0, $user->getBalance()->getAmount());
        }
    }
}