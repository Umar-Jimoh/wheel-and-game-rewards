<?php

declare(strict_types=1);

namespace App\Tests\Application\UseCase;

use PHPUnit\Framework\TestCase;
use App\Infrastructure\Persistence\InMemory\InMemoryUserRepository;
use App\Application\UseCase\GetBalance;
use App\Application\Dto\GetbalanceResult;
use App\Domain\Model\User;
use App\Domain\Model\Point;

class GetBalanceTest extends TestCase
{
    // ExecuteInvalidUserIDReturnFailureResult
    public function testExecuteReturnsFailureWhenNoUserFound(): void
    {
        $users = new InMemoryUserRepository();

        $user = new User(1, 'test user', new Point(1000));

        $users->save($user);

        $getBalance = new GetBalance($users);

        $result = $getBalance->execute(2);

        $this->assertEquals(GetBalanceResult::failure('Invalid user id provided.'), $result);

    }

    public function testExecuteReturnsCorrectBalance(): void
    {
        $users = new InMemoryUserRepository();

        $balance = 1000;

        $user = new User(1, 'test user', new Point($balance));

        $users->save($user);

        $getBalance = new GetBalance($users);

        $result = $getBalance->execute(1);

        $this->assertSame($balance, $result->balance);
    }
}