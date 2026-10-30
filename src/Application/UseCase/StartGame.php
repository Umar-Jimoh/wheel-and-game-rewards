<?php

declare(strict_types=1);

namespace App\Application\UseCase;

use App\Domain\Model\Game;
use App\Domain\Model\PendingGame;
use App\Application\Dto\StartGameResult;
use App\Application\Port\In\StartGameUseCase;
use App\Application\Port\Out\PendingGameRepository;
use App\Application\Port\Out\UserRepository;
use App\Application\Port\Out\RandomNumberGenerator;

final class StartGame implements StartGameUseCase
{
    public function __construct(
        private readonly PendingGameRepository $pendingGameRepository,
        private readonly UserRepository $userRepository,
        private readonly RandomNumberGenerator $randomNumberGenerator,
    ){}

    public function execute(int $userId): StartGameResult
    {
        $user = $this->userRepository->findById($userId);

        if (! $user) {
            return StartGameResult::failure('Invalid user id provided.');
        }

        $generatedNumbers = $this->randomNumberGenerator->generate();

        $game = new Game($generatedNumbers->getNumbers());

        $pendingGame = new PendingGame($userId, $game);

        $this->pendingGameRepository->save($pendingGame);

        return StartGameResult::success($generatedNumbers);
    }
}