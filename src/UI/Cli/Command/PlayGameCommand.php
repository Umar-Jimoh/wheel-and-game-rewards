<?php

declare(strict_types=1);

namespace App\UI\Cli\Command;

use App\Application\Port\In\PlayGameUseCase;
use App\Application\Port\In\StartGameUseCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Domain\Model\Parity;

#[AsCommand(name: 'game:play')]
class PlayGameCommand extends Command
{
    public function __construct(
        private readonly PlayGameUseCase $playGameUseCase,
        private readonly StartGameUseCase $startGameUseCase,
    ){
        parent::__construct();
    }

    public function __invoke(
        OutputInterface $output, 
        InputInterface $input,
        #[Argument(description: 'The unique ID of the user.')] int $userId, 
    ): int
    {
        $io = new SymfonyStyle($input, $output);

        $generatedNumbers = $this->startGameUseCase->execute($userId);
        $numbers = $generatedNumbers->getNumbers();

        $io->title("🎲 Game Started for User ID: <info>{$userId}</info>");
        $io->text('Pick a position and guess whether its number is odd or even. Playing is free — win 100 points, or lose nothing.');
        $io->table(
            ['Position 0', 'Position 1', 'Position 2', 'Position 3', 'Position 4'],
            [$numbers]
        );

        $selectedPosition = $io->ask('Enter the position of the number you want to select (0-4)', null, function ($value) {
            if (!is_numeric($value) || $value < 0 || $value > 4) {
                throw new \RuntimeException('Invalid position. Please enter a number between 0 and 4.');
            }
            return (int) $value;
        });

        $selectedParity = $io->choice('Select the parity of the selected number', ['Even', 'Odd']);
        $parityEnum = $selectedParity === 'Even' ? Parity::EVEN : Parity::ODD;

        $io->text("You picked position <comment>{$selectedPosition}</comment> ({$numbers[$selectedPosition]}) and guessed <comment>{$selectedParity}</comment>.");

        if (! $io->confirm('Lock in this guess?', true)) {
            $io->warning('Game cancelled — no points won or lost.');
            return Command::SUCCESS;
        }

        $result = $this->playGameUseCase->play($userId, $selectedPosition, $parityEnum);

        if (! $result->success) {
            $io->error("😔 {$result->errorMessage}");
            return Command::FAILURE;
        }

        if ($result->won) {
            $io->success("🎉 Correct! You won {$result->earned} points. Your new balance is {$result->balance} points.");
        } else {
            $io->warning("😔 Not quite — you lost this round. Your balance is still {$result->balance} points.");
        }

        return Command::SUCCESS;
    }
}