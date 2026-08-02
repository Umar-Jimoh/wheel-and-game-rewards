<?php

declare(strict_types=1);

namespace App\Tests\Application\UseCase;

use App\Domain\Model\User;
use App\Domain\Model\Point;
use App\Domain\Model\Multiplier;
use App\Application\UseCase\SpinWheel;
use App\Application\Dto\SpinWheelResult;
use PHPUnit\Framework\TestCase;
use App\Domain\Exception\InsufficientPointsException;
use App\Infrastructure\Persistence\InMemory\InMemoryUserRepository;

final class SpinWheelTest extends TestCase
{
    public function testExecuteReturnsFailureWhenNoUserFound(): void
    {
        $users = new InMemoryUserRepository();
        
        $user = new User(1, 'test user', new Point(30));
        
        $users->save($user);
        
        $multiplier = $this->createStub(\App\Application\Port\Out\RandomMultiplierGenerator::class);
        $multiplier->method('generate')->willReturn(Multiplier::X2);

        $spinWheel = new SpinWheel($users, $multiplier);

        $result = $spinWheel->execute(5, 500);

        $this->assertEquals(SpinWheelResult::failure('Invalid user id provided.'), $result);
    }

    public function testExecuteReturnsFailureWhenHasNoEnoughPoints(): void
    {
        $users = new InMemoryUserRepository();

        $user = new User(1, 'test user', new Point(30));
        
        $users->save($user); 

        $multiplier = $this->createStub(\App\Application\Port\Out\RandomMultiplierGenerator::class);
        $multiplier->method('generate')->willReturn(Multiplier::X2);

        $spinWheel = new SpinWheel($users, $multiplier);

        $result = $spinWheel->execute(1, 500);

        $this->assertEquals(SpinWheelResult::failure('Insufficient points.'), $result);
    }

    public function testExecuteMultiplyWithNone(): void
    {
        $users = new InMemoryUserRepository();

        $user = new User(1, 'test user', new Point(1000));

        $users->save($user);

        $multiplier = $this->createStub(\App\Application\Port\Out\RandomMultiplierGenerator::class);
        $multiplier->method('generate')->willReturn(Multiplier::NONE);

        $spinWheel = new SpinWheel($users, $multiplier);

        $result = $spinWheel->execute(1, 500);

        $this->assertSame(Multiplier::NONE->label(), $result->multiplier);
    }

    public function testExecuteMultiplyWithX2(): void
    {
        $users = new InMemoryUserRepository();

        $user = new User(1, 'test user', new Point(1000));

        $users->save($user);

        $multiplier = $this->createStub(\App\Application\Port\Out\RandomMultiplierGenerator::class);
        $multiplier->method('generate')->willReturn(Multiplier::X2);

        $spinWheel = new SpinWheel($users, $multiplier);

        $result = $spinWheel->execute(1, 500);

        $this->assertSame(Multiplier::X2->label(), $result->multiplier);
    }

    public function testExecuteMultiplyWithX3(): void
    {
        $users = new InMemoryUserRepository();

        $user = new User(1, 'test user', new Point(1000));

        $users->save($user);

        $multiplier = $this->createStub(\App\Application\Port\Out\RandomMultiplierGenerator::class);
        $multiplier->method('generate')->willReturn(Multiplier::X3);

        $spinWheel = new SpinWheel($users, $multiplier);

        $result = $spinWheel->execute(1, 500);

        $this->assertSame(Multiplier::X3->label(), $result->multiplier);
    }

    public function testExecuteMultiplyWithX4(): void
    {
        $users = new InMemoryUserRepository();

        $user = new User(1, 'test user', new Point(1000));

        $users->save($user);

        $multiplier = $this->createStub(\App\Application\Port\Out\RandomMultiplierGenerator::class);
        $multiplier->method('generate')->willReturn(Multiplier::X4);

        $spinWheel = new SpinWheel($users, $multiplier);

        $result = $spinWheel->execute(1, 500);

        $this->assertSame(Multiplier::X4->label(), $result->multiplier);
    }

    public function testExecuteReturnsExpectedEarned(): void
    {
        $users = new InMemoryUserRepository();

        $user = new User(1, 'test user', new Point(1000));
        
        $users->save($user); 

        $multiplier = $this->createStub(\App\Application\Port\Out\RandomMultiplierGenerator::class);
        $multiplier->method('generate')->willReturn(Multiplier::X2);

        $spinWheel = new SpinWheel($users, $multiplier);

        $spinCost = 500;

        $result = $spinWheel->execute(1, $spinCost);
        
        $expectedEarned = 1000;

        $this->assertSame($expectedEarned, $result->earned);
    }

    public function testExecuteReturnsExpectedBalance(): void
    {
        $users = new InMemoryUserRepository();

        $user = new User(1, 'test user', new Point(1000));
        
        $users->save($user); 

        $multiplier = $this->createStub(\App\Application\Port\Out\RandomMultiplierGenerator::class);
        $multiplier->method('generate')->willReturn(Multiplier::X2);

        $spinWheel = new SpinWheel($users, $multiplier);

        $spinCost = 500;

        $balanceAfterSpin = $user->getBalance()->getAmount() - $spinCost;
        
        $result = $spinWheel->execute(1, $spinCost);

        $spinReward = 1000;

        $expectedBalance = $balanceAfterSpin + $spinReward;

        $this->assertSame($expectedBalance, $result->balance);
    }

    public function testExecuteUpdateUserBalance(): void
    {
        $users = new InMemoryUserRepository();

        $user = new User(1, 'test user', new Point(1000));

        $users->save($user);

        $multiplier = $this->createStub(\App\Application\Port\Out\RandomMultiplierGenerator::class);
        $multiplier->method('generate')->willReturn(Multiplier::X2);

        $spinWheel = new SpinWheel($users, $multiplier);

        $spinWheel->execute(1, 500);

        $updatedUser = $users->findById(1);

        $this->assertSame(1500, $updatedUser->getBalance()->getAmount());
    }
}