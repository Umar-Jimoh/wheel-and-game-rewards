<?php

declare(strict_types=1);

namespace App\Tests\UI\Cli\Command;

use PHPUnit\Framework\TestCase;
use App\Application\Dto\GetBalanceResult;
use App\UI\Cli\Command\GetBalanceCommand;
use Symfony\Component\Console\Tester\CommandTester;
use App\Application\Port\In\GetBalanceUseCase;

final class GetBalanceCommandTest extends TestCase
{
    public function testInvokeReturnsFailureWhenUserIdArgumentIsMissing(): void
    {
        $getBalanceUseCase = $this->createStub(GetBalanceUseCase::class);
        $command = new GetBalanceCommand($getBalanceUseCase);
        $commandTester = new CommandTester($command);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/missing: "user-id"/');

        $commandTester->execute([]);
    }

    public function testInvokeReturnsFailureWhenUseCaseReturnsFailure(): void
    {
        $getBalanceUseCase = $this->createStub(GetBalanceUseCase::class);
        $getBalanceUseCase->method('execute')->willReturn(GetBalanceResult::failure("User not found."));

        $command = new GetBalanceCommand($getBalanceUseCase);
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'user-id' => 1,
        ]);

        $this->assertStringContainsString("User not found.", $commandTester->getDisplay());
    }

    public function testInvokeReturnsSuccessWhenUseCaseReturnsSuccess(): void
    {
        $getBalanceUseCase = $this->createStub(GetBalanceUseCase::class);
        $getBalanceUseCase->method('execute')->willReturn(GetBalanceResult::success(1000));

        $command = new GetBalanceCommand($getBalanceUseCase);
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'user-id' => 1,
        ]);

        $this->assertStringContainsString("💰 User 1 has a balance of 1000 points.", $commandTester->getDisplay());
    }
}