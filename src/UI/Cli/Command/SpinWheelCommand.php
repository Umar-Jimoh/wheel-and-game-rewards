<?php

declare(strict_types=1);

namespace App\UI\Cli\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Output\OutputInterface;
use App\Application\Port\In\SpinWheelUseCase;

#[AsCommand(name: 'wheel:spin')]
class SpinWheelCommand extends Command
{
    public function __construct(
        private readonly SpinWheelUseCase $spinWheelUseCase,
    ){
        parent::__construct();
    }

public function __invoke(
    OutputInterface $output, 
    #[Argument(description: 'The unique ID of the user.')] int $userId, 
    #[Argument(description: 'The cost in points for the spin.')] int $cost,
    ): int
    {
        $result = $this->spinWheelUseCase->execute($userId, $cost);

        if (! $result->success) {
            $output->writeln($result->errorMessage);
            return Command::FAILURE;
        }

        $verb = $result->earned > 0 ? "won" : "didn't win anything";
        $output->writeln("🎯 <info>Spin Result:</info> You hit a <comment>{$result->multiplier}</comment> multiplier and {$verb}");
        $output->writeln("💰 You earned <comment>{$result->earned}</comment> — your new balance is <info>{$result->balance}</info>.");

        return Command::SUCCESS;
    }
}