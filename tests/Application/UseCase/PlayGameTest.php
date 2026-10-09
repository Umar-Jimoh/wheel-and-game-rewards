<?php

declare(strict_types=1);

namespace App\Tests\Application\UseCase;

use App\Application\Dto\PlayGameResult;
use App\Application\Dto\RandomNumbersResult;
use App\Application\Port\Out\RandomNumberGenerator;
use App\Application\UseCase\PlayGame;
use App\Domain\Model\Parity;
use App\Domain\Model\Point;
use App\Domain\Model\User;
use App\Infrastructure\Persistence\InMemory\InMemoryUserRepository;
use PHPUnit\Framework\TestCase;

final class PlayGameTest extends TestCase
{
    public function testPlayGameReturnsFailureWhenUserIdIsInvalid(): void
    {
        $users = new InMemoryUserRepository();

        $user = new User(1, 'test user', new Point(30));

        $users->save($user);

        $randomNumberGenerator = $this->createStub(RandomNumberGenerator::class);
        $randomNumberGenerator->method('generate')->willReturn(new RandomNumbersResult([41, 73, 94, 15, 26]));

        $game = new PlayGame($randomNumberGenerator, $users);

        $numbers = new RandomNumbersResult([41, 73, 94, 15, 26]);

        $result = $game->play(2, $numbers, 1, Parity::EVEN);

        $this->assertEquals(PlayGameResult::failure('Invalid user id provided.'), $result);
    }

    public function testPlayGameReturnsSuccessWhenGuessMatchesParity(): void
    {
        $users = new InMemoryUserRepository();

        $user = new User(1, 'test user', new Point(30));

        $users->save($user);

        $randomNumberGenerator = $this->createStub(RandomNumberGenerator::class);

        $game = new PlayGame($randomNumberGenerator, $users);

        $numbers = new RandomNumbersResult([41, 73, 94, 15, 26]);

        $result = $game->play(1, $numbers, 0, Parity::ODD);

        $this->assertEquals(PlayGameResult::success(100, 130), $result);
    }

    public function testPlayGameReturnsFailureWhenGuessDoesNotMatchParity(): void
    {
        $users = new InMemoryUserRepository();

        $user = new User(1, 'test user', new Point(30));

        $users->save($user);

        $randomNumberGenerator = $this->createStub(RandomNumberGenerator::class);

        $game = new PlayGame($randomNumberGenerator, $users);

        $numbers = new RandomNumbersResult([41, 73, 94, 15, 26]);

        $result = $game->play(1, $numbers, 0, Parity::EVEN);

        $this->assertEquals(PlayGameResult::failure('You lost the game. Try again!'), $result);
        $this->assertSame(30, $user->getBalance()->getAmount());
    }
}