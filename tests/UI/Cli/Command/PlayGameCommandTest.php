<?php

declare(strict_types=1);

namespace App\Tests\UI\Cli\Command;

use PHPUnit\Framework\TestCase;
use App\UI\Cli\Command\PlayGameCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use App\Application\Port\In\PlayGameUseCase;
use App\Application\Port\In\StartGameUseCase;
use App\Application\Dto\RandomNumbersResult;
use App\Application\Dto\PlayGameResult;

class PlayGameCommandTest extends TestCase
{
    public function testInvokeReturnsFailureWhenUserIdArgumentIsMissing(): void
    {
        $playGameUseCase = $this->createStub(PlayGameUseCase::class);
        $startGameUseCase = $this->createStub(StartGameUseCase::class);
        $command = new PlayGameCommand($playGameUseCase, $startGameUseCase);
        $commandTester = new CommandTester($command);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/missing: "user-id"/');

        $commandTester->execute([]);
    }

    public function testPositionSelectedIsInvalid(): void
    {
        $playGameUseCase = $this->createStub(PlayGameUseCase::class);
        $startGameUseCase = $this->createStub(StartGameUseCase::class);
        $startGameUseCase->method('execute')
            ->willReturn(new RandomNumbersResult([41, 73, 94, 15, 26]));

        $command = new PlayGameCommand($playGameUseCase, $startGameUseCase);
        $commandTester = new CommandTester($command);
        $commandTester->setInputs(['9']);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/Invalid position. Please enter a number between 0 and 4./');

        $commandTester->execute(['user-id' => 1]);
    }

    public function testPositionSelectedIsValid(): void
    {
        $playGameUseCase = $this->createStub(PlayGameUseCase::class);
        $playGameUseCase->method('play')
            ->willReturn(PlayGameResult::success(true, 100, 130));
        $startGameUseCase = $this->createStub(StartGameUseCase::class);
        $startGameUseCase->method('execute')
            ->willReturn(new RandomNumbersResult([41, 73, 64, 15, 26]));

        $command = new PlayGameCommand($playGameUseCase, $startGameUseCase);
        $commandTester = new CommandTester($command);
        $commandTester->setInputs(['2', 'Even', 'yes']);

        $commandTester->execute(['user-id' => 1]);

        $this->assertStringContainsString('You picked position 2 (64) and guessed Even.', $commandTester->getDisplay());
    }

    public function testGameIsCancelledWhenUserDeclinesConfirmation(): void
    {
        $playGameUseCase = $this->createMock(PlayGameUseCase::class);
        $playGameUseCase->expects($this->never())->method('play');
        $startGameUseCase = $this->createStub(StartGameUseCase::class);
        $startGameUseCase->method('execute')
            ->willReturn(new RandomNumbersResult([41, 73, 64, 15, 26]));

        $command = new PlayGameCommand($playGameUseCase, $startGameUseCase);
        $commandTester = new CommandTester($command);
        $commandTester->setInputs(['2', 'Even', 'no']);

        $commandTester->execute(['user-id' => 1]);

        $this->assertStringContainsString('Game cancelled — no points won or lost.', $commandTester->getDisplay());
        $this->assertSame(Command::SUCCESS, $commandTester->getStatusCode());
    }

    public function testPlayGameDisplaysSuccessMessageWhenGuessWins(): void
    {
        $playGameUseCase = $this->createStub(PlayGameUseCase::class);
        $playGameUseCase->method('play')
            ->willReturn(PlayGameResult::success(true, 100, 130));
        $startGameUseCase = $this->createStub(StartGameUseCase::class);
        $startGameUseCase->method('execute')
            ->willReturn(new RandomNumbersResult([41, 73, 64, 15, 26]));

        $command = new PlayGameCommand($playGameUseCase, $startGameUseCase);
        $commandTester = new CommandTester($command);
        $commandTester->setInputs(['2', 'Even', 'yes']);

        $commandTester->execute(['user-id' => 1]);

        $this->assertStringContainsString('🎉 Correct! You won 100 points. Your new balance is 130 points.', $commandTester->getDisplay());
        $this->assertSame(Command::SUCCESS, $commandTester->getStatusCode());
    }

    public function testPlayGameDisplaysLossMessageWhenGuessLoses(): void
    {
        $playGameUseCase = $this->createStub(PlayGameUseCase::class);
        $playGameUseCase->method('play')
            ->willReturn(PlayGameResult::success(false, 0, 30));
        $startGameUseCase = $this->createStub(StartGameUseCase::class);
        $startGameUseCase->method('execute')
            ->willReturn(new RandomNumbersResult([41, 73, 64, 15, 26]));

        $command = new PlayGameCommand($playGameUseCase, $startGameUseCase);
        $commandTester = new CommandTester($command);
        $commandTester->setInputs(['2', 'Odd', 'yes']);

        $commandTester->execute(['user-id' => 1]);

        $this->assertStringContainsString('😔 Not quite — you lost this round. Your balance is still 30 points.', $commandTester->getDisplay());
        $this->assertSame(Command::SUCCESS, $commandTester->getStatusCode());
    }
}
