<?php

declare(strict_types=1);

namespace App\Tests\Application\UseCase;

use App\Application\Dto\PlayGameResult;
use App\Application\UseCase\PlayGame;
use App\Domain\Model\Game;
use App\Domain\Model\Parity;
use App\Domain\Model\PendingGame;
use App\Domain\Model\Point;
use App\Domain\Model\User;
use App\Infrastructure\Persistence\InMemory\InMemoryPendingGameRepository;
use App\Infrastructure\Persistence\InMemory\InMemoryUserRepository;
use PHPUnit\Framework\TestCase;

final class PlayGameTest extends TestCase
{
    public function testPlayGameReturnsFailureWhenUserIdIsInvalid(): void
    {
        $users = new InMemoryUserRepository();

        $user = new User(1, 'test user', new Point(30));

        $users->save($user);

        $pendingGames = new InMemoryPendingGameRepository();

        $game = new PlayGame($users, $pendingGames);

        $result = $game->play(2, 1, Parity::EVEN);

        $this->assertEquals(PlayGameResult::failure('Invalid user id provided.'), $result);
    }

    public function testPlayGameReturnsFailureWhenNoPendingGameExistsForUser(): void
    {
        $users = new InMemoryUserRepository();

        $user = new User(1, 'test user', new Point(30));

        $users->save($user);

        $pendingGames = new InMemoryPendingGameRepository();

        $game = new PlayGame($users, $pendingGames);

        $result = $game->play(1, 0, Parity::EVEN);

        $this->assertEquals(PlayGameResult::failure('No pending game found for the user.'), $result);
    }

    public function testPlayGameReturnsSuccessWhenGuessMatchesParity(): void
    {
        $users = new InMemoryUserRepository();

        $user = new User(1, 'test user', new Point(30));

        $users->save($user);

        $pendingGames = new InMemoryPendingGameRepository();

        $pendingGames->save(new PendingGame(1, new Game([41, 73, 94, 15, 26])));

        $game = new PlayGame($users, $pendingGames);

        $result = $game->play(1, 0, Parity::ODD);

        $this->assertEquals(PlayGameResult::success(true, 100, 130), $result);
    }

    public function testPlayGameReturnsLossWhenGuessDoesNotMatchParity(): void
    {
        $users = new InMemoryUserRepository();

        $user = new User(1, 'test user', new Point(30));

        $users->save($user);

        $pendingGames = new InMemoryPendingGameRepository();

        $pendingGames->save(new PendingGame(1, new Game([41, 73, 94, 15, 26])));

        $game = new PlayGame($users, $pendingGames);

        $result = $game->play(1, 0, Parity::EVEN);

        $this->assertEquals(PlayGameResult::success(false, 0, 30), $result);
        $this->assertSame(30, $user->getBalance()->getAmount());
    }
}
