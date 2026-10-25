<?php

declare(strict_types=1);

namespace App\Tests\UI\Cli\Command;

use PHPUnit\Framework\TestCase;
use App\UI\Cli\Command\PlayGameCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use App\Application\Port\In\PlayGameUseCase;
use App\Application\Dto\RandomNumbersResult;
use App\Application\Dto\PlayGameResult;

class PlayGameCommandTest extends TestCase
{
    public function testInvokeReturnsFailureWhenUserIdArgumentIsMissing(): void
    {
        $playGameUseCase = $this->createStub(PlayGameUseCase::class);
        $command = new PlayGameCommand($playGameUseCase);
        $commandTester = new CommandTester($command);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/missing: "user-id"/');

        $commandTester->execute([]);
    }

    public function testPositionSelectedIsInvalid(): void
    {
        $playGameUseCase = $this->createStub(PlayGameUseCase::class);
        $playGameUseCase->method('generateRandomNumbers')
            ->willReturn(new RandomNumbersResult([41, 73, 94, 15, 26]));

        $command = new PlayGameCommand($playGameUseCase);
        $commandTester = new CommandTester($command);
        $commandTester->setInputs(['9']);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/Invalid position. Please enter a number between 0 and 4./');

        $commandTester->execute(['user-id' => 1]);
    }

    public function testPositionSelectedIsValid(): void
    {
        $playGameUseCase = $this->createStub(PlayGameUseCase::class);
        $playGameUseCase->method('generateRandomNumbers')
            ->willReturn(new RandomNumbersResult([41, 73, 64, 15, 26]));
        $playGameUseCase->method('play')
            ->willReturn(PlayGameResult::success(100, 130));

        $command = new PlayGameCommand($playGameUseCase);
        $commandTester = new CommandTester($command);
        $commandTester->setInputs(['2', 'Even', 'yes']);

        $commandTester->execute(['user-id' => 1]);

        $this->assertStringContainsString('You picked position 2 (64) and guessed Even.', $commandTester->getDisplay());
    }

    public function testGameIsCancelledWhenUserDeclinesConfirmation(): void
    {
        $playGameUseCase = $this->createMock(PlayGameUseCase::class);
        $playGameUseCase->method('generateRandomNumbers')
            ->willReturn(new RandomNumbersResult([41, 73, 64, 15, 26]));
        $playGameUseCase->expects($this->never())->method('play');

        $command = new PlayGameCommand($playGameUseCase);
        $commandTester = new CommandTester($command);
        $commandTester->setInputs(['2', 'Even', 'no']);

        $commandTester->execute(['user-id' => 1]);

        $this->assertStringContainsString('Game cancelled — no points won or lost.', $commandTester->getDisplay());
        $this->assertSame(Command::SUCCESS, $commandTester->getStatusCode());
    }

    public function testPlayGameDisplaysSuccessMessageWhenGuessWins(): void
    {
        $playGameUseCase = $this->createStub(PlayGameUseCase::class);
        $playGameUseCase->method('generateRandomNumbers')
            ->willReturn(new RandomNumbersResult([41, 73, 64, 15, 26]));
        $playGameUseCase->method('play')
            ->willReturn(PlayGameResult::success(100, 130));

        $command = new PlayGameCommand($playGameUseCase);
        $commandTester = new CommandTester($command);
        $commandTester->setInputs(['2', 'Even', 'yes']);

        $commandTester->execute(['user-id' => 1]);

        $this->assertStringContainsString('🎉 Correct! You won 100 points. Your new balance is 130 points.', $commandTester->getDisplay());
        $this->assertSame(Command::SUCCESS, $commandTester->getStatusCode());
    }

    public function testPlayGameDisplaysFailureMessageWhenGuessLoses(): void
    {
        $playGameUseCase = $this->createStub(PlayGameUseCase::class);
        $playGameUseCase->method('generateRandomNumbers')
            ->willReturn(new RandomNumbersResult([41, 73, 64, 15, 26]));
        $playGameUseCase->method('play')
            ->willReturn(PlayGameResult::failure('You lost the game. Try again!'));

        $command = new PlayGameCommand($playGameUseCase);
        $commandTester = new CommandTester($command);
        $commandTester->setInputs(['2', 'Odd', 'yes']);

        $commandTester->execute(['user-id' => 1]);

        $this->assertStringContainsString('😔 You lost the game. Try again!', $commandTester->getDisplay());
        $this->assertSame(Command::FAILURE, $commandTester->getStatusCode());
    }
}