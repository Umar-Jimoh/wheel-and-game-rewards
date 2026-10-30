<?php

declare(strict_types=1);

namespace App\Tests\UI\Http;

use PHPUnit\Framework\TestCase;
use App\Application\Port\In\PlayGameUseCase;
use App\Application\Dto\PlayGameResult;
use App\Application\Dto\Request\PlayGameInput;
use App\Domain\Model\Parity;
use App\UI\Http\PlayGameController;
use Symfony\Component\HttpFoundation\JsonResponse;

class PlayGameControllerTest extends TestCase
{
    public function testInvokeReturnsFailureWhenUseCaseReturnsFailure(): void
    {
        $playGameUseCase = $this->createStub(PlayGameUseCase::class);
        $playGameUseCase->method('play')->willReturn(PlayGameResult::failure('No pending game found for the user.'));

        $controller = new PlayGameController($playGameUseCase);

        $response = $controller(1, new PlayGameInput(0, Parity::EVEN));

        $this->assertSame(JsonResponse::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertSame(
            ['error' => 'No pending game found for the user.'],
            json_decode($response->getContent(), true)
        );
    }

    public function testInvokeReturnsWinResultWhenGuessMatchesParity(): void
    {
        $playGameUseCase = $this->createStub(PlayGameUseCase::class);
        $playGameUseCase->method('play')->willReturn(PlayGameResult::success(true, 100, 130));

        $controller = new PlayGameController($playGameUseCase);

        $response = $controller(1, new PlayGameInput(0, Parity::ODD));

        $this->assertSame(JsonResponse::HTTP_OK, $response->getStatusCode());
        $this->assertSame(
            ['won' => true, 'earned' => 100, 'balance' => 130],
            json_decode($response->getContent(), true)
        );
    }

    public function testInvokeReturnsLossResultWhenGuessDoesNotMatchParity(): void
    {
        $playGameUseCase = $this->createStub(PlayGameUseCase::class);
        $playGameUseCase->method('play')->willReturn(PlayGameResult::success(false, 0, 30));

        $controller = new PlayGameController($playGameUseCase);

        $response = $controller(1, new PlayGameInput(0, Parity::EVEN));

        $this->assertSame(JsonResponse::HTTP_OK, $response->getStatusCode());
        $this->assertSame(
            ['won' => false, 'earned' => 0, 'balance' => 30],
            json_decode($response->getContent(), true)
        );
    }
}
