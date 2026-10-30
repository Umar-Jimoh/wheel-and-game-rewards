<?php

declare(strict_types=1);

namespace App\Tests\UI\Http;

use PHPUnit\Framework\TestCase;
use App\Application\Port\In\StartGameUseCase;
use App\Application\Dto\StartGameResult;
use App\Application\Dto\RandomNumbersResult;
use App\UI\Http\StartGameController;
use Symfony\Component\HttpFoundation\JsonResponse;

class StartGameControllerTest extends TestCase
{
    public function testInvokeReturnsFailureWhenUseCaseReturnsFailure(): void
    {
        $startGameUseCase = $this->createStub(StartGameUseCase::class);
        $startGameUseCase->method('execute')->willReturn(StartGameResult::failure('Invalid user id provided.'));

        $controller = new StartGameController($startGameUseCase);

        $response = $controller(99);

        $this->assertSame(JsonResponse::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertSame(
            ['error' => 'Invalid user id provided.'],
            json_decode($response->getContent(), true)
        );
    }

    public function testInvokeReturnsNumbersWhenUseCaseSucceeds(): void
    {
        $startGameUseCase = $this->createStub(StartGameUseCase::class);
        $startGameUseCase->method('execute')->willReturn(StartGameResult::success(new RandomNumbersResult([41, 73, 94, 15, 26])));

        $controller = new StartGameController($startGameUseCase);

        $response = $controller(1);

        $this->assertSame(JsonResponse::HTTP_CREATED, $response->getStatusCode());
        $this->assertSame(
            ['numbers' => [41, 73, 94, 15, 26]],
            json_decode($response->getContent(), true)
        );
    }
}
