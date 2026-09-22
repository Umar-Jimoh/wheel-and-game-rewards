<?php

declare(strict_types=1);

namespace App\UI\Cli\Command;

use App\Application\Port\In\GetBalanceUseCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Output\OutputInterface;


#[AsCommand(name: 'user:balance')]
class GetBalanceCommand extends Command
{
    public function __construct(
        private readonly GetBalanceUseCase $getBalanceUseCase,
    ){
        parent::__construct();
    }

    public function __invoke(
        OutputInterface $output, 
        #[Argument(description: 'The unique ID of the user.')] int $userId
    ): int
    {
        $result = $this->getBalanceUseCase->execute($userId);

        if (! $result->success) {
            $output->writeln($result->errorMessage);
            return Command::FAILURE;
        }
        
        $output->writeln("💰 User <info>{$userId}</info> has a balance of <comment>{$result->balance}</comment> points.");
        return Command::SUCCESS;
    }

}