<?php

declare(strict_types=1);

namespace App\Tests\UI\Http;

use PHPUnit\Framework\TestCase;
use App\Application\Port\In\GetBalanceUseCase;
use App\Application\Dto\GetBalanceResult;
use App\UI\Http\GetBalanceController;
use Symfony\Component\HttpFoundation\JsonResponse;

class GetBalanceControllerTest extends TestCase
{
    public function testInvokeReturnsFailureWhenUseCaseReturnsFailure(): void
    {
        $getBalanceUseCase = $this->createStub(GetBalanceUseCase::class);
        $getBalanceUseCase->method('execute')->willReturn(GetBalanceResult::failure('Invalid user id provided.'));

        $controller = new GetBalanceController($getBalanceUseCase);

        $response = $controller(99);

        $this->assertSame(JsonResponse::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertSame(
            ['error' => 'Invalid user id provided.'],
            json_decode($response->getContent(), true)
        );
    }

    public function testInvokeReturnsBalanceWhenUseCaseSucceeds(): void
    {
        $getBalanceUseCase = $this->createStub(GetBalanceUseCase::class);
        $getBalanceUseCase->method('execute')->willReturn(GetBalanceResult::success(130));

        $controller = new GetBalanceController($getBalanceUseCase);

        $response = $controller(1);

        $this->assertSame(JsonResponse::HTTP_OK, $response->getStatusCode());
        $this->assertSame(
            ['balance' => 130],
            json_decode($response->getContent(), true)
        );
    }
}
