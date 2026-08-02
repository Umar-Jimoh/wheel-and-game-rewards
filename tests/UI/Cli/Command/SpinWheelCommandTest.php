<?php

declare(strict_types=1);

namespace App\Tests\UI\Cli\Command;

use Symfony\Component\Console\Tester\CommandTester;
use PHPUnit\Framework\TestCase;
use App\UI\Cli\Command\SpinWheelCommand;
use App\Application\Port\In\SpinWheelUseCase;
use App\Application\Dto\SpinWheelResult;
use App\Domain\Model\Multiplier;

final class SpinWheelCommandTest extends TestCase
{
    public function testInvokeReturnsFailureWhenUserIdArgumentIsMissing(): void
    {
        $spinWheelUseCase = $this->createStub(SpinWheelUseCase::class);
        $command = new SpinWheelCommand($spinWheelUseCase);
        $commandTester = new CommandTester($command);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/missing: "user-id"/');

        $commandTester->execute(['cost' => 500]);
    }

    public function testInvokeReturnsFailureWhenCostArgumentIsMissing(): void
    {
        $spinWheelUseCase = $this->createStub(SpinWheelUseCase::class);
        $command = new SpinWheelCommand($spinWheelUseCase);
        $commandTester = new CommandTester($command);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/missing: "cost"/');

        $commandTester->execute(['user-id' => 1]);    
    }

    public function testInvokeReturnsFailureWhenUseCaseReturnsFailure(): void
    {
        $spinWheelUseCase = $this->createStub(SpinWheelUseCase::class);
        $spinWheelUseCase->method('execute')->willReturn(SpinWheelResult::failure('Insufficient points.'));

        $command = new SpinWheelCommand($spinWheelUseCase);
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'user-id' => 1,
            'cost' => 5,
        ]);

        $this->assertStringContainsString('Insufficient points.', $commandTester->getDisplay());
    }

    public function testInvokeReturnsSuccessWhenUseCaseReturnsSuccess(): void
    {
        $spinWheelUseCase = $this->createStub(SpinWheelUseCase::class);
        $spinWheelUseCase->method('execute')->willReturn(SpinWheelResult::success(Multiplier::X2->label(), 1000, 2000));

        $command = new SpinWheelCommand($spinWheelUseCase);
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'user-id' => 1,
            'cost' => 500,
        ]);

        $this->assertStringContainsString('🎯 Spin Result: You hit a 2X multiplier and won', $commandTester->getDisplay());
        $this->assertStringContainsString('💰 You earned 1000 — your new balance is 2000.', $commandTester->getDisplay());
    }
    
    public function testInvokeReturnsSuccessWhenUseCaseReturnsSuccessWithNoReward(): void
    {
        $spinWheelUseCase = $this->createStub(SpinWheelUseCase::class);
        $spinWheelUseCase->method('execute')->willReturn(SpinWheelResult::success(Multiplier::NONE->label(), 0, 2000));

        $command = new SpinWheelCommand($spinWheelUseCase);
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'user-id' => 1,
            'cost' => 500,
        ]);

        $this->assertStringContainsString("🎯 Spin Result: You hit a 0 multiplier and didn't win anything", $commandTester->getDisplay());
        $this->assertStringContainsString('💰 You earned 0 — your new balance is 2000.', $commandTester->getDisplay());
    }
}